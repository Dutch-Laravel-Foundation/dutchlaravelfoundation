#!/usr/bin/env bash
set -euo pipefail

if [[ -z "${DEPLOY_SERVER:-}" || "${DEPLOY_PATH:-}" != /* ]]; then
    echo '::error::DEPLOY_SERVER and an absolute DEPLOY_PATH are required.' >&2
    exit 1
fi

required_revision=${REQUIRED_REVISION:-}
if [[ -n "$required_revision" && ! "$required_revision" =~ ^[0-9a-f]{40}$ ]]; then
    echo '::error::Invalid merged pull request revision.' >&2
    exit 1
fi

retry_delay=${DEPLOY_RETRY_DELAY_SECONDS:-10}
if [[ ! "$retry_delay" =~ ^[0-9]+$ ]]; then
    echo '::error::Invalid deployment retry delay.' >&2
    exit 1
fi

source_dir=$(git rev-parse --show-toplevel)
temporary_dir=$(mktemp -d)
trap 'rm -rf -- "$temporary_dir"' EXIT

for attempt in 1 2 3; do
    git -C "$source_dir" fetch --no-tags origin main
    revision=$(git -C "$source_dir" rev-parse FETCH_HEAD)

    # A force-push must never silently remove the PR that triggered this job.
    if [[ -n "$required_revision" ]] &&
       ! git -C "$source_dir" merge-base --is-ancestor "$required_revision" "$revision"; then
        echo '::error::Current main does not contain the merged pull request.' >&2
        exit 1
    fi

    echo "Deployment attempt $attempt/3 at revision $revision"
    attempt_dir="$temporary_dir/attempt-$attempt"
    # Keep this driver fixed, but use a fresh recipe and lockfile on every attempt.
    git clone --quiet --shared --no-checkout "$source_dir" "$attempt_dir"
    git -C "$attempt_dir" -c advice.detachedHead=false checkout --quiet --detach "$revision"
    (
        cd "$attempt_dir"
        composer install --no-interaction --prefer-dist --no-scripts --no-progress
    )

    log_file="$temporary_dir/deploy-$attempt.log"
    if (cd "$attempt_dir" && composer deploy -- --revision="$revision" --no-ansi) 2>&1 | tee "$log_file"; then
        exit 0
    else
        # Capture the deploy status, not tee's status.
        statuses=("${PIPESTATUS[@]}")
        status=${statuses[0]}
        if [[ "${statuses[1]}" -ne 0 ]]; then
            exit "${statuses[1]}"
        fi
    fi

    # Envoy emits this marker only after safely releasing the lock, before activation.
    if [[ "$status" -ne 75 ]] || ! grep -Fq 'DLF_DEPLOY_RETRY_MAIN_ADVANCED' "$log_file"; then
        echo '::error::Deployment failed; this error is not safe to retry automatically.' >&2
        exit "$status"
    fi

    if [[ "$attempt" -eq 3 ]]; then
        echo '::error::Main kept advancing; deployment stopped after three attempts.' >&2
        exit "$status"
    fi

    echo '::notice::Main advanced before activation. Retrying with its latest revision.'
    rm -rf -- "$attempt_dir"
    sleep "$retry_delay"
done
