<?php use function Statamic\trans as __; ?>

<div class="global-header">
    <div class="lg:min-w-xl rtl:pr-2 ltr:pl-2 rtl:md:pr-6 ltr:md:pl-6 h-full flex items-center">
        <button class="nav-toggle hidden md:flex rtl:mr-1 ltr:ml-1 shrink-0" @click="toggleNav" aria-label="<?php echo e(__('Toggle Nav')); ?>"><?php echo Statamic::svg('icons/light/burger', 'h-4 w-4') ?></button>
        <button class="nav-toggle md:hidden rtl:mr-1 ltr:ml-1 shrink-0" @click="toggleMobileNav" v-if="! mobileNavOpen" aria-label="<?php echo e(__('Toggle Mobile Nav')); ?>"><?php echo Statamic::svg('icons/light/burger', 'h-4 w-4') ?></button>
        <button class="nav-toggle md:hidden rtl:mr-1 ltr:ml-1 shrink-0" @click="toggleMobileNav" v-else v-cloak aria-label="<?php echo e(__('Toggle Mobile Nav')); ?>"><?php echo Statamic::svg('icons/light/close', 'h-3 w-3') ?></button>
        <a href="<?php echo e(route('statamic.cp.index')); ?>" class="flex items-end">
            <div v-tooltip="version" class="hidden md:block shrink-0">
                <?php if($customLogo): ?>
                    <img src="<?php echo e($customLogo); ?>" alt="<?php echo e(config('statamic.cp.custom_cms_name')); ?>" class="white-label-logo dark:hidden">
                    <img src="<?php echo e($customDarkLogo); ?>" alt="<?php echo e(config('statamic.cp.custom_cms_name')); ?>" class="white-label-logo hidden dark:block">
                <?php elseif($customLogoText): ?>
                    <span class="font-medium"><?php echo e($customLogoText); ?></span>
                <?php else: ?>
                    <?php echo Statamic::svg('statamic-wordmark', 'w-24 logo') ?>
                    <?php if(Statamic::pro()): ?><span class="font-bold text-4xs align-top uppercase"><?php echo e(__('Pro')); ?></span><?php endif; ?>
                <?php endif; ?>
            </div>
        </a>
    </div>

    <div class="sm:px-8 w-full flex-1 lg:flex items-center lg:justify-center mx-auto max-w-full">
        <global-search ref="globalSearch" endpoint="<?php echo e(cp_route('search')); ?>" placeholder="<?php echo e(__('Search...')); ?>">
        </global-search>
    </div>

    <div class="head-link h-full px-6 space-x-3 rtl:space-x-reverse flex items-center justify-end">

        <?php if(Statamic\Facades\Site::authorized()->count() > 1): ?>
            <global-site-selector>
                <template slot="icon"><?php echo Statamic::svg('icons/light/sites') ?></template>
            </global-site-selector>
        <?php endif; ?>

        <dark-mode-toggle initial="<?php echo e($user->preferredTheme()); ?>"></dark-mode-toggle>

        <favorite-creator class="hidden md:block"></favorite-creator>

        <?php if(Route::has('horizon.index') && \Laravel\Horizon\Horizon::check(request())): ?>
            <a class="global-header-icon-button hidden md:block" href="<?php echo e(route('horizon.index')); ?>" target="_blank" v-tooltip="'Laravel Horizon'">
                <?php echo Statamic::svg('icons/regular/horizon') ?>
            </a>
        <?php endif; ?>

        <?php if(Route::has('pulse') && (app()->environment('local') || $user->can('viewPulse'))): ?>
            <a class="global-header-icon-button hidden md:block" href="<?php echo e(route('pulse')); ?>" target="_blank" v-tooltip="'Laravel Pulse'">
                <?php echo Statamic::svg('icons/regular/pulse') ?>
            </a>
        <?php endif; ?>

        <?php if(config('nova.path') && (app()->environment('local') || $user->can('viewNova'))): ?>
            <a class="global-header-icon-button hidden md:block" href="/<?php echo e(trim(config('nova.path'), '/')); ?>/dashboards/main" target="_blank" v-tooltip="'Laravel Nova'">
                <?php echo Statamic::svg('icons/regular/nova') ?>
            </a>
        <?php endif; ?>

        <?php if(Route::has('telescope') && \Laravel\Telescope\Telescope::check(request())): ?>
            <a class="global-header-icon-button hidden md:block" href="<?php echo e(route('telescope')); ?>" target="_blank" v-tooltip="'Laravel Telescope'">
                <?php echo Statamic::svg('icons/regular/telescope') ?>
            </a>
        <?php endif; ?>

        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <button class="global-header-icon-button hidden md:block" v-tooltip="__('Useful Links')" aria-label="<?php echo e(__('View Useful Links')); ?>">
                    <?php echo Statamic::svg('icons/light/book-open') ?>
                </button>
            </template>

            <?php if(config('statamic.cp.link_to_docs')): ?>
            <dropdown-item external-link="https://statamic.dev" class="flex items-center">
                <span><?php echo e(__('Documentation')); ?></span>
                <i class="w-3 block rtl:mr-2 ltr:ml-2"><?php echo Statamic::svg('icons/light/external-link') ?></i>
            </dropdown-item>
            <?php endif; ?>

            <?php if(config('statamic.cp.support_url')): ?>
            <dropdown-item external-link="<?php echo e(config('statamic.cp.support_url')); ?>" class="flex items-center">
                <span><?php echo e(__('Support')); ?></span>
                <i class="w-3 block rtl:mr-2 ltr:ml-2"><?php echo Statamic::svg('icons/light/external-link') ?></i>
            </dropdown-item>
            <?php endif; ?>

            <dropdown-item @click="$events.$emit('keyboard-shortcuts.open')" class="flex items-center">
                <span><?php echo e(__('Keyboard Shortcuts')); ?></span>
            </dropdown-item>
        </dropdown-list>

        <a class="global-header-icon-button hidden md:block" href="<?php echo e(Statamic\Facades\Site::selected()->url()); ?>" target="_blank" v-tooltip="'<?php echo e(__('View Site')); ?>'" aria-label="<?php echo e(__('View Site')); ?>">
            <?php echo Statamic::svg('icons/light/browser-com') ?>
        </a>
        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <a class="dropdown-toggle items-center h-full hide flex relative group">
                    <?php if($user->avatar()): ?>
                        <div class="icon-header-avatar <?php echo e(session()->get('statamic_impersonated_by') ? 'animate-radar' : ''); ?>"><img src="<?php echo e($user->avatar()); ?>" /></div>
                    <?php else: ?>
                        <div class="icon-header-avatar <?php echo e(session()->get('statamic_impersonated_by') ? 'animate-radar' : ''); ?> icon-user-initials"><?php echo e($user->initials()); ?></div>
                    <?php endif; ?>
                </a>
            </template>

            <div class="px-2">
                <div class="text-base mb-px"><?php echo e($user->email()); ?></div>
                <?php if($user->isSuper()): ?>
                    <div class="text-2xs mt-px text-gray-600"><?php echo e(__('Super Admin')); ?> <?php if(session()->get('statamic_impersonated_by')): ?>(Impersonating)<?php endif; ?></div>
                <?php elseif(session()->get('statamic_impersonated_by')): ?>
                    <div class="text-2xs mt-px text-gray-600"><?php echo e(__('Impersonating')); ?></div>
                <?php endif; ?>
            </div>
            <div class="divider"></div>

            <dropdown-item :text="__('Profile')" redirect="<?php echo e(route('statamic.cp.account')); ?>"></dropdown-item>
            <dropdown-item :text="__('Preferences')" redirect="<?php echo e(cp_route('preferences.user.edit')); ?>"></dropdown-item>
            <?php if(session()->get('statamic_impersonated_by')): ?>
                <dropdown-item :text="__('Stop Impersonating')" redirect="<?php echo e(cp_route('impersonation.stop')); ?>"></dropdown-item>
            <?php endif; ?>
            <dropdown-item :text="__('Log out')" redirect="<?php echo e(route('statamic.cp.logout', ['redirect' => cp_route('index')])); ?>"></dropdown-item>
        </dropdown-list>
    </div>
</div>

<div v-if="$refs.globalSearch?.focused" v-cloak class="fixed inset-0 h-full w-full bg-black/10 z-2"></div>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/partials/global-header.blade.php ENDPATH**/ ?>