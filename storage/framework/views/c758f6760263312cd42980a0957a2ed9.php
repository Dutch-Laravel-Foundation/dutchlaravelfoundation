<li class="<?php echo e($item->isActive() ? 'current' : ''); ?>">
    <a href="<?php echo e($item->url()); ?>">
        <i><?php echo $item->svg(); ?></i><span v-pre><?php echo e(__($item->name())); ?></span>
        <updates-badge class="rtl:mr-2 ltr:ml-2"></updates-badge>
    </a>
</li>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/nav/updates.blade.php ENDPATH**/ ?>