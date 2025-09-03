<?php use function Statamic\trans as __; ?>


<?php $__env->startSection('title', __('Collections')); ?>

<?php $__env->startSection('content'); ?>

    <?php if (! ($collections->isEmpty())): ?>

        <div class="flex items-center justify-between mb-6">
            <h1><?php echo e(__('Collections')); ?></h1>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', 'Statamic\Contracts\Entries\Collection')): ?>
                <a href="<?php echo e(cp_route('collections.create')); ?>" class="btn-primary"><?php echo e(__('Create Collection')); ?></a>
            <?php endif; ?>
        </div>

        <collection-list
            :initial-rows="<?php echo e(json_encode($collections)); ?>"
            :initial-columns="<?php echo e(json_encode($columns)); ?>"
            :endpoints="{}">
        </collection-list>

    <?php else: ?>

        <?php echo $__env->make('statamic::partials.empty-state', [
            'title' => __('Collections'),
            'description' => __('statamic::messages.collection_configure_intro'),
            'svg' => 'empty/content',
            'button_text' => __('Create Collection'),
            'button_url' => cp_route('collections.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Entries\Collection')
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php endif; ?>

    <?php echo $__env->make('statamic::partials.docs-callout', [
        'topic' => __('Collections'),
        'url' => Statamic::docsUrl('collections')
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('statamic::layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/collections/index.blade.php ENDPATH**/ ?>