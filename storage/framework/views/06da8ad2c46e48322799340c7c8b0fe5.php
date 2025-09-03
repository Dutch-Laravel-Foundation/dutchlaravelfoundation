<?php $__env->startSection('title', Statamic::crumb($collection->title(), 'Collections')); ?>
<?php $__env->startSection('wrapper_class', 'max-w-full'); ?>

<?php $__env->startSection('content'); ?>

    <collection-view
        title="<?php echo e($collection->title()); ?>"
        handle="<?php echo e($collection->handle()); ?>"
        breadcrumb-url="<?php echo e(cp_route('collections.index')); ?>"
        :can-create="<?php echo e(Statamic\Support\Str::bool($canCreate)); ?>"
        :create-urls='<?php echo json_encode($createUrls, 15, 512) ?>'
        create-label="<?php echo e($collection->createLabel()); ?>"
        :blueprints='<?php echo json_encode($blueprints, 15, 512) ?>'
        sort-column="<?php echo e($collection->sortField()); ?>"
        sort-direction="<?php echo e($collection->sortDirection()); ?>"
        :columns="<?php echo e($columns->toJson()); ?>"
        :filters="<?php echo e($filters->toJson()); ?>"
        action-url="<?php echo e(cp_route('collections.entries.actions.run', $collection->handle())); ?>"
        reorder-url="<?php echo e(cp_route('collections.entries.reorder', $collection->handle())); ?>"
        initial-site="<?php echo e($site); ?>"
        :sites="<?php echo e(json_encode($sites)); ?>"
        :can-change-localization-delete-behavior="<?php echo e(Statamic\Support\Str::bool($canChangeLocalizationDeleteBehavior)); ?>"

        <?php if($collection->hasStructure()): ?>
        :structured="<?php echo e(Statamic\Support\Str::bool($user->can('reorder', $collection))); ?>"
        structure-pages-url="<?php echo e(cp_route('collections.tree.index', $structure->handle())); ?>"
        structure-submit-url="<?php echo e(cp_route('collections.tree.update', $collection->handle())); ?>"
        :structure-max-depth="<?php echo e($structure->maxDepth() ?? 'Infinity'); ?>"
        :structure-expects-root="<?php echo e(Statamic\Support\Str::bool($structure->expectsRoot())); ?>"
        :structure-show-slugs="<?php echo e(Statamic\Support\Str::bool($structure->showSlugs())); ?>"
        <?php endif; ?>
    >
        <?php if(
            auth()->user()->can('edit', $collection)
            || auth()->user()->can('delete', $collection)
            || auth()->user()->can('configure fields')
            || $actions->isNotEmpty()
        ): ?>
        <template #twirldown="{ actionCompleted }">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit', $collection)): ?>
                <dropdown-item :text="__('Edit Collection')" redirect="<?php echo e($collection->editUrl()); ?>"></dropdown-item>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configure fields')): ?>
                <dropdown-item :text="__('Edit Blueprints')" redirect="<?php echo e(cp_route('collections.blueprints.index', $collection)); ?>"></dropdown-item>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit', $collection)): ?>
                <dropdown-item :text="__('Scaffold Views')" redirect="<?php echo e(cp_route('collections.scaffold', $collection->handle())); ?>"></dropdown-item>
            <?php endif; ?>
            <data-list-inline-actions
                item="<?php echo e($collection->handle()); ?>"
                url="<?php echo e(cp_route('collections.actions.run', ['collection' => $collection->handle()])); ?>"
                :actions="<?php echo e($actions); ?>"
                @completed="actionCompleted"
            ></data-list-inline-actions>
        </template>
        <?php endif; ?>
    </collection-view>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('statamic::layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/collections/show.blade.php ENDPATH**/ ?>