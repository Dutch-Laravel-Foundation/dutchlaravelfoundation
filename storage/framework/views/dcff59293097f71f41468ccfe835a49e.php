<!doctype html>
<html lang="<?php echo e(config('app.locale')); ?>" dir="<?php echo e(Statamic\Facades\Site::current()->direction ?? 'rtl'); ?>">
    <head>
        <?php echo $__env->make('statamic::partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </head>
    <body class="outside <?php echo e(config('statamic.cp.theme')); ?>-theme <?php echo $__env->yieldContent('body_class'); ?>">
        <div id="statamic">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
        <?php echo $__env->make('statamic::partials.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->yieldContent('scripts'); ?>
    </body>
</html>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/outside.blade.php ENDPATH**/ ?>