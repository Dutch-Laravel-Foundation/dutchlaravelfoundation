@include('vendor/autoload.php')

@setup
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $deploymentTime = date('Y-m-d-His');
    $deploymentDir = "deployment-{$deploymentTime}";
    $basePath = env('DEPLOY_PATH');
    $currentPath = "{$basePath}/current";
    $newDeploymentPath = "{$basePath}/{$deploymentDir}";
@endsetup

@servers(['web' => [env('DEPLOY_SERVER')]])

@story('deploy')
    check_and_commit
    create_deployment_directory
    clone_repository
    install_composer_dependencies
    install_npm_dependencies
    build_assets
    symlink_env
    symlink_folders
    optimize_application
    update_search_index
    finalize_deployment
    reset_php_cache
    warm_static_cache
    cleanup_old_releases
@endstory

@task('check_and_commit', ['on' => 'web'])
    echo "=========================================="
    echo "📋 Step 1: Checking current deployment status"
    echo "=========================================="

    cd {{ $currentPath }}

    echo "🔍 Checking git status..."
    if [[ -n $(git status --porcelain) ]]; then
        echo "⚠️  Uncommitted changes detected"
        echo "📝 Committing changes..."
        git add .
        git commit -m "committing changes before deployment"
        echo "⬆️  Pushing changes to origin/main..."
        git push origin main
        echo "✅ Changes committed and pushed"
    else
        echo "✅ Working directory is clean"
    fi

    echo ""
@endtask

@task('create_deployment_directory', ['on' => 'web'])
    echo "=========================================="
    echo "📁 Step 2: Creating deployment directory"
    echo "=========================================="

    echo "📂 Creating directory: {{ $deploymentDir }}"
    cd {{ $basePath }}
    mkdir {{ $deploymentDir }}
    echo "✅ Deployment directory created at {{ $newDeploymentPath }}"
    echo ""
@endtask

@task('clone_repository', ['on' => 'web'])
    echo "=========================================="
    echo "🔄 Step 3: Cloning repository"
    echo "=========================================="

    cd {{ $newDeploymentPath }}
    echo "📥 Pulling latest code from repository..."
    git clone git@github.com:Dutch-Laravel-Foundation/dutchlaravelfoundation.git .
    git checkout main
    echo "✅ Repository cloned successfully"
    echo ""
@endtask

@task('install_composer_dependencies', ['on' => 'web'])
    echo "=========================================="
    echo "📦 Step 4: Installing Composer dependencies"
    echo "=========================================="

    cd {{ $newDeploymentPath }}
    echo "🎼 Running composer install..."
    composer install --no-dev \
        --optimize-autoloader \
        --prefer-dist \
        --no-interaction
    echo "✅ Composer dependencies installed"
    echo ""
@endtask

@task('install_npm_dependencies', ['on' => 'web'])
    echo "=========================================="
    echo "📦 Step 4: Installing NPM dependencies"
    echo "=========================================="

    cd {{ $newDeploymentPath }}
    echo "🎼 Running npm install..."
    npm ci
    echo "✅ NPM dependencies installed"
    echo ""
@endtask

@task('build_assets', ['on' => 'web'])
    echo "=========================================="
    echo "🎨 Step 5: Building frontend assets"
    echo "=========================================="

    cd {{ $newDeploymentPath }}
    echo "⚡ Running npm run build..."
    npm run build
    echo "✅ Assets built successfully"
    echo ""
@endtask

@task('symlink_env', ['on' => 'web'])
    echo "=========================================="
    echo "🔗 Step 6: Symlinking environment file"
    echo "=========================================="

    cd {{ $newDeploymentPath }}
    echo "🔗 Creating symlink for .env file..."
    ln -sf {{ $basePath }}/.env .env
    echo "✅ Environment file symlinked"
    echo ""
@endtask

@task('symlink_folders', ['on' => 'web'])
    echo "=========================================="
    echo "🔗 Step 7: Symlinking folder"
    echo "=========================================="

    cd {{ $newDeploymentPath }}
    echo "🔗 Creating symlink for .forms file..."
    rm -rf storage/forms && ln -sf {{ $basePath }}/forms storage/forms
    echo "✅ Forms folder symlinked"

    echo "🔗 Creating symlink for .users file..."
    rm -rf users && ln -sf {{ $basePath }}/users users
    echo "✅ Users folder symlinked"

    echo ""
@endtask

@task('optimize_application', ['on' => 'web'])
    echo "=========================================="
    echo "⚡ Step 8: Optimizing application"
    echo "=========================================="

    cd {{ $newDeploymentPath }}
    echo "🚀 Running php artisan optimize..."
    php artisan optimize:clear
    php artisan optimize
    echo "✅ Application optimized"
    echo ""
@endtask

@task('update_search_index', ['on' => 'web'])
    echo "=========================================="
    echo "🔍 Step 9: Updating Statamic caches and search"
    echo "=========================================="

    cd {{ $newDeploymentPath }}
    echo "📚 Warming up Stache cache..."
    php please stache:clear
    php please stache:warm
    echo "🔎 Updating search indexes..."
    php please search:update --all
    echo "✅ Caches and search indexes updated"
    echo ""
@endtask

@task('finalize_deployment', ['on' => 'web'])
    echo "=========================================="
    echo "🎉 Step 10: Finalizing deployment"
    echo "=========================================="

    cd {{ $basePath }}
    echo "🔗 Updating 'current' symlink to new deployment..."
    ln -sfn {{ $newDeploymentPath }} current
    echo "✅ Symlink updated"

    echo ""
    echo "=========================================="
    echo "🎊 DEPLOYMENT COMPLETED SUCCESSFULLY!"
    echo "=========================================="
    echo "📂 Deployment: {{ $deploymentDir }}"
    echo "📍 Location: {{ $newDeploymentPath }}"
    echo "🕐 Time: $(date '+%Y-%m-%d %H:%M:%S')"
    echo "=========================================="
@endtask

@task('warm_static_cache', ['on' => 'web'])
    cd {{ $newDeploymentPath }}
    php please static:clear
    php please static:warm
@endtask

@task('reset_php_cache', ['on' => 'web'])
    echo "=========================================="
    echo "🔄 Step 11: Resetting PHP OPcache"
    echo "=========================================="

    echo "🧹 Clearing OPcache via PHP-FPM socket..."
    cachetool opcache:reset --fcgi=/run/php/dutchlaravelfoundation.nl-php8.5-fpm.sock
    echo "✅ OPcache reset successfully"
    echo ""
@endtask

@task('cleanup_old_releases', ['on' => 'web'])
    echo "=========================================="
    echo "🧹 Step 12: Cleaning up old releases"
    echo "=========================================="

    cd {{ $basePath }}

    # Count deployment directories (excluding 'current' symlink)
    RELEASE_COUNT=$(ls -1d deployment-* 2>/dev/null | wc -l)
    echo "📊 Found $RELEASE_COUNT release(s)"

    # Keep only the 5 most recent releases
    if [ $RELEASE_COUNT -gt 5 ]; then
        echo "🗑️  Removing old releases (keeping 5 most recent)..."

        # List directories by modification time, skip the 5 newest, and remove the rest
        ls -1td deployment-* | tail -n +6 | while read dir; do
            echo "   🗑️  Removing: $dir"
            rm -rf "$dir"
        done

        REMOVED_COUNT=$((RELEASE_COUNT - 5))
        echo "✅ Removed $REMOVED_COUNT old release(s)"
    else
        echo "✅ No cleanup needed (5 or fewer releases present)"
    fi

    echo ""
@endtask
