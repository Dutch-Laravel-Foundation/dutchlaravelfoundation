<div class="logo pt-20 relative z-10">
    <?php if($customLogo): ?>
        <img src="<?php echo e($customLogo); ?>" alt="<?php echo e(config('statamic.cp.custom_cms_name')); ?>" class="white-label-logo dark:hidden">
        <img src="<?php echo e($customDarkLogo); ?>" alt="<?php echo e(config('statamic.cp.custom_cms_name')); ?>" class="white-label-logo hidden dark:block">
    <?php elseif($customLogoText): ?>
        <div class="max-w-xs mx-auto mb-8 text-lg font-medium text-center opacity-50"><?php echo e($customLogoText); ?></div>
    <?php else: ?>
        <?php echo Statamic::svg('statamic-wordmark') ?>
    <?php endif; ?>
</div>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/partials/outside-logo.blade.php ENDPATH**/ ?>