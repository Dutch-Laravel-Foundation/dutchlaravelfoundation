<?php $__currentLoopData = Statamic::availableExternalScripts(request()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <script src="<?php echo $url; ?>" defer></script>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__currentLoopData = Statamic::availableScripts(request()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package => $paths): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $__currentLoopData = $paths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <script src="<?php echo e(Statamic::vendorPackageAssetUrl($package, $path, 'js')); ?>" defer></script>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__currentLoopData = Statamic::availableVites(request()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package => $vite): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php echo e(\Illuminate\Support\Facades\Vite::useHotFile($vite['hotFile'])
           ->useBuildDirectory($vite['buildDirectory'])
           ->withEntryPoints($vite['input'])); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__currentLoopData = Statamic::availableInlineScripts(request()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $script): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <script><?php echo $script; ?></script>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<script>
var StatamicConfig = <?php echo json_encode(array_merge(Statamic::jsonVariables(request()), [
    'wrapperClass' => $__env->getSection('wrapper_class', 'max-w-xl')
])); ?>

</script>


<script src="data:text/javascript;base64,<?php echo e(base64_encode('Statamic.config(StatamicConfig); Statamic.start()')); ?>" defer></script>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/partials/scripts.blade.php ENDPATH**/ ?>