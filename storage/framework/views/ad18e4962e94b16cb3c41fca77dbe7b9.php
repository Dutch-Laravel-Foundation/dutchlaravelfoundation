<?php use function Statamic\trans as __; ?>


<?php $__env->startSection('title', __('Addons')); ?>
<?php $__env->startSection('wrapper_class', 'max-w-3xl'); ?>

<?php $__env->startSection('content'); ?>

    <addon-list :install-count="<?php echo e($addonCount); ?>"></addon-list>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('statamic::layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/addons/index.blade.php ENDPATH**/ ?>