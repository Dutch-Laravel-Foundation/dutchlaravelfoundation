@include('vendor/autoload.php')

@setup
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    $revision = $revision ?? '';
    $basePath = env('DEPLOY_PATH');
    $server = env('DEPLOY_SERVER');

    if (! is_string($basePath) || ! str_starts_with($basePath, '/')) {
        throw new InvalidArgumentException('DEPLOY_PATH must be an absolute path.');
    }

    if (! is_string($server) || $server === '') {
        throw new InvalidArgumentException('DEPLOY_SERVER is required.');
    }

    if ($revision !== '' && ! preg_match('/\A[0-9a-f]{40}\z/', $revision)) {
        throw new InvalidArgumentException('The --revision argument must be a full lowercase commit SHA.');
    }

    $deploymentTime = date('Y-m-d-His');
    $shortRevision = $revision === '' ? 'revision-required' : substr($revision, 0, 12);
    $releaseName = "deployment-{$deploymentTime}-{$shortRevision}";
    $releasePath = "{$basePath}/{$releaseName}";
@endsetup

@servers(['web' => [$server]])

@story('deploy')
    deploy_release
@endstory

@task('deploy_release', ['on' => 'web'])
    set -Eeuo pipefail

    BASE_PATH={{ escapeshellarg($basePath) }}
    RELEASE_NAME={{ escapeshellarg($releaseName) }}
    RELEASE_PATH={{ escapeshellarg($releasePath) }}
    REVISION={{ escapeshellarg($revision) }}
    REPOSITORY='git@github.com:Dutch-Laravel-Foundation/dutchlaravelfoundation.git'
    HEALTH_URL='https://dutchlaravelfoundation.nl/up'
    KEEP_RELEASES=6
    CURRENT_PATH="$BASE_PATH/current"
    LOCK_PATH="$BASE_PATH/.deployment-lock"
    SWITCH_LINK="$BASE_PATH/.current-$RELEASE_NAME"
    PREVIOUS_RELEASE=''
    FPM_SOCKET=''
    ACTIVATED=0
    HEALTHY=0

    activate_release() {
        local target="$1"

        rm -f "$SWITCH_LINK"
        ln -s "$target" "$SWITCH_LINK"
        mv -Tf "$SWITCH_LINK" "$CURRENT_PATH"
    }

    discover_fpm_socket() {
        FPM_SOCKET=$(
            find /run/php \
                -maxdepth 1 \
                -type s \
                -name 'dutchlaravelfoundation.nl-php*-fpm.sock' \
                -print \
                | sort -V \
                | tail -n 1
        )

        if [ -z "$FPM_SOCKET" ]; then
            echo 'No Dutch Laravel Foundation PHP-FPM socket was found.' >&2
            return 1
        fi
    }

    reset_opcache() {
        discover_fpm_socket
        cachetool opcache:reset --fcgi="$FPM_SOCKET"
    }

    rollback_release() {
        if [ -z "$PREVIOUS_RELEASE" ] || [ ! -d "$PREVIOUS_RELEASE" ]; then
            echo 'The previous release is unavailable; automatic rollback is impossible.' >&2
            return 1
        fi

        echo "Rolling back to $PREVIOUS_RELEASE"
        activate_release "$PREVIOUS_RELEASE"
        reset_opcache
        echo "Rollback completed: $PREVIOUS_RELEASE"
    }

    check_health() {
        local attempt=1
        local maximum_attempts=12

        while [ "$attempt" -le "$maximum_attempts" ]; do
            if curl \
                --fail \
                --silent \
                --show-error \
                --location \
                --max-time 10 \
                "$HEALTH_URL" \
                >/dev/null; then
                return 0
            fi

            if [ "$attempt" -lt "$maximum_attempts" ]; then
                sleep 5
            fi

            attempt=$((attempt + 1))
        done

        echo "Health check failed after $maximum_attempts attempts: $HEALTH_URL" >&2
        return 1
    }

    cleanup_releases() {
        local current_release
        local release
        local -a releases

        current_release=$(readlink -f "$CURRENT_PATH")
        mapfile -t releases < <(
            find "$BASE_PATH" \
                -mindepth 1 \
                -maxdepth 1 \
                -type d \
                -name 'deployment-*' \
                -printf '%T@ %p\n' \
                | sort -rn \
                | cut -d' ' -f2-
        )

        for release in "${releases[@]:$KEEP_RELEASES}"; do
            if [ "$release" = "$current_release" ] || [ "$release" = "$PREVIOUS_RELEASE" ]; then
                continue
            fi

            case "$release" in
                "$BASE_PATH"/deployment-*)
                    echo "Removing old release: $release"
                    rm -rf -- "$release"
                    ;;
                *)
                    echo "Refusing to remove unexpected release path: $release" >&2
                    return 1
                    ;;
            esac
        done
    }

    finish_deployment() {
        local status=$?

        trap - EXIT

        if [ "$status" -ne 0 ] && [ "$ACTIVATED" -eq 1 ] && [ "$HEALTHY" -eq 0 ]; then
            rollback_release || true
        fi

        rm -f "$SWITCH_LINK"
        rm -f "$LOCK_PATH/revision" "$LOCK_PATH/pid"

        if ! rmdir "$LOCK_PATH"; then
            echo "Unable to remove deployment lock: $LOCK_PATH" >&2
            status=1
        fi

        exit "$status"
    }

    case "$REVISION" in
        ''|*[!0-9a-f]*)
            echo 'Deployment requires --revision=<full-lowercase-commit-sha>.' >&2
            exit 1
            ;;
    esac

    if [ "${#REVISION}" -ne 40 ]; then
        echo 'Deployment requires --revision=<full-lowercase-commit-sha>.' >&2
        exit 1
    fi

    if [ ! -d "$BASE_PATH" ]; then
        echo "Deployment path does not exist: $BASE_PATH" >&2
        exit 1
    fi

    if ! mkdir "$LOCK_PATH"; then
        echo "Another deployment holds the lock: $LOCK_PATH" >&2
        exit 1
    fi

    trap finish_deployment EXIT
    printf '%s\n' "$REVISION" > "$LOCK_PATH/revision"
    printf '%s\n' "$$" > "$LOCK_PATH/pid"

    if [ ! -f "$BASE_PATH/.env" ]; then
        echo "Shared environment file is missing: $BASE_PATH/.env" >&2
        exit 1
    fi

    if [ ! -d "$BASE_PATH/forms" ] || [ ! -d "$BASE_PATH/users" ]; then
        echo 'Shared forms or users directory is missing.' >&2
        exit 1
    fi

    PREVIOUS_RELEASE=$(readlink -f "$CURRENT_PATH" || true)

    if [ -z "$PREVIOUS_RELEASE" ] || [ ! -d "$PREVIOUS_RELEASE" ]; then
        echo 'A valid current release is required for automatic rollback.' >&2
        exit 1
    fi

    if [ -d "$PREVIOUS_RELEASE/.git" ]; then
        if [ -n "$(git -C "$PREVIOUS_RELEASE" status --porcelain --untracked-files=all)" ]; then
            echo "Current release is dirty: $PREVIOUS_RELEASE" >&2
            git -C "$PREVIOUS_RELEASE" status --short >&2
            exit 1
        fi

        git -C "$PREVIOUS_RELEASE" fetch --quiet --prune origin

        if ! git -C "$PREVIOUS_RELEASE" branch --remotes --contains HEAD \
            | grep -q '[^[:space:]]'; then
            echo 'Current release contains commits that are not present on any origin branch.' >&2
            exit 1
        fi
    fi

    if [ -e "$RELEASE_PATH" ]; then
        echo "Release path already exists: $RELEASE_PATH" >&2
        exit 1
    fi

    echo "Preparing revision $REVISION in $RELEASE_PATH"
    git clone --no-checkout "$REPOSITORY" "$RELEASE_PATH"
    cd "$RELEASE_PATH"
    git fetch --quiet origin main

    if [ "$(git rev-parse origin/main)" != "$REVISION" ]; then
        echo 'Requested revision is not the current origin/main tip.' >&2
        exit 1
    fi

    git checkout --detach "$REVISION"

    if [ "$(git rev-parse HEAD)" != "$REVISION" ]; then
        echo 'Checked-out release does not match the requested revision.' >&2
        exit 1
    fi

    git config remote.pushDefault origin
    git config remote.origin.push 'HEAD:refs/heads/main'

    rm -f .env
    rm -rf storage/forms users
    mkdir -p storage
    ln -s "$BASE_PATH/.env" .env
    ln -s "$BASE_PATH/forms" storage/forms
    ln -s "$BASE_PATH/users" users

    composer install \
        --no-dev \
        --optimize-autoloader \
        --prefer-dist \
        --no-interaction

    npm ci
    npm run build

    php artisan optimize:clear
    php artisan optimize
    php please stache:clear
    php please stache:warm
    php please search:update --all
    php please static:clear
    php please static:warm

    printf '%s\n' "$REVISION" > "$RELEASE_PATH/.git/deployed-revision"
    printf '%s\n' "$PREVIOUS_RELEASE" > "$RELEASE_PATH/.git/previous-release"

    activate_release "$RELEASE_PATH"
    ACTIVATED=1

    reset_opcache
    check_health
    HEALTHY=1

    if ! cleanup_releases; then
        echo 'Release cleanup failed after a healthy activation; manual cleanup is required.' >&2
    fi

    echo "Deployed revision: $REVISION"
    echo "Release: $RELEASE_PATH"
    echo "Previous release: $PREVIOUS_RELEASE"
@endtask
