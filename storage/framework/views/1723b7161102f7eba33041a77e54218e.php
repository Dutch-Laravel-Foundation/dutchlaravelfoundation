<?php use function Statamic\trans as __; ?>

<div class="card card-lg p-0 content">
    <header>
        <h1><?php echo e(__('statamic::messages.getting_started_widget_header')); ?></h1>
        <p><?php echo e(__('statamic::messages.getting_started_widget_intro')); ?></p>
    </header>
    <div class="grid lg:grid-cols-2 p-4">
        <a href="https://statamic.dev" class="super-btn">
            <?php echo Statamic::svg('icons/light/book-pages') ?>
            <div class="flex-1">
                <h3><?php echo e(__('Read the Documentation')); ?></h3>
                <p><?php echo e(__('statamic::messages.getting_started_widget_docs')); ?></p>
            </div>
        </a>
        <?php if(!Statamic::pro()): ?>
        <a href="https://statamic.dev/licensing" class="super-btn">
            <?php echo Statamic::svg('icons/light/pro-ribbon') ?>
            <div class="flex-1">
                <h3><?php echo e(__('Enable Pro Mode')); ?></h3>
                <p><?php echo e(__('statamic::messages.getting_started_widget_pro')); ?></p>
            </div>
        </a>
        <?php endif; ?>
        <a href="<?php echo e(cp_route('collections.create')); ?>" class="super-btn">
            <?php echo Statamic::svg('icons/light/content-writing') ?>
            <div class="flex-1">
                <h3><?php echo e(__('Create a Collection')); ?></h3>
                <p><?php echo e(__('statamic::messages.getting_started_widget_collections')); ?></p>
            </div>
        </a>
        <a href="<?php echo e(cp_route('blueprints.index')); ?>" class="super-btn">
            <?php echo Statamic::svg('icons/light/blueprints') ?>
            <div class="flex-1">
                <h3><?php echo e(__('Create a Blueprint')); ?></h3>
                <p><?php echo e(__('statamic::messages.getting_started_widget_blueprints')); ?></p>
            </div>
        </a>
        <a href="<?php echo e(cp_route('navigation.create')); ?>" class="super-btn">
            <?php echo Statamic::svg('icons/light/hierarchy-files') ?>
            <div class="flex-1">
                <h3><?php echo e(__('Create a Navigation')); ?></h3>
                <p><?php echo e(__('statamic::messages.getting_started_widget_navigation')); ?></p>
            </div>
        </a>
    </div>
</div>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/widgets/getting-started.blade.php ENDPATH**/ ?>