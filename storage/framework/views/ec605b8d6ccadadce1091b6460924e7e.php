<?php use function Statamic\trans as __; ?>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width">
<meta name="robots" content="noindex,nofollow">

<title><?php echo $__env->yieldContent('title', $title ?? __('Here')); ?> <?php echo e(Statamic::cpDirection() === 'ltr' ? '‹' : '›'); ?> <?php echo e(__(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic')); ?></title>

<?php if(Statamic::pro() && config('statamic.cp.custom_favicon_url')): ?>
    <?php echo $__env->make('statamic::partials.favicon', ['favicon_url' => config('statamic.cp.custom_favicon_url')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <link rel="icon" type="image/png" href="<?php echo e(Statamic::cpViteAsset('img/favicon-32x32.png')); ?>" sizes="32x32" />
    <link rel="icon" type="image/png" href="<?php echo e(Statamic::cpViteAsset('img/favicon-16x16.png')); ?>" sizes="16x16" />
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo e(Statamic::cpViteAsset('img/favicon.ico')); ?>" sizes="16x16 32x32"/>
<?php endif; ?>

<script>
    (function () {
        let theme = <?php echo ($userTheme = $user?->preferredTheme()) ? "'".$userTheme."'" : "null"; ?>;
        if (! theme) theme = localStorage.getItem('statamic.theme') ?? 'auto';
        if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) theme = 'dark';
        if (theme === 'dark') document.documentElement.classList.add('dark');
    })();
</script>

<?php echo e(Statamic::cpViteScripts()); ?>


<?php if(Statamic::pro() && config('statamic.cp.custom_css_url')): ?>
<link href="<?php echo e(config('statamic.cp.custom_css_url')); ?>?v=<?php echo e(Statamic::version()); ?>" rel="stylesheet" />
<?php endif; ?>

<?php $__currentLoopData = Statamic::availableExternalStyles(request()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <link href="<?php echo e($url); ?>" rel="stylesheet" />
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__currentLoopData = Statamic::availableStyles(request()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package => $paths): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $__currentLoopData = $paths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <link href="<?php echo e(Statamic::vendorPackageAssetUrl($package, $path, 'css')); ?>" rel="stylesheet" />
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php echo $__env->yieldPushContent('head'); ?>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/partials/head.blade.php ENDPATH**/ ?>