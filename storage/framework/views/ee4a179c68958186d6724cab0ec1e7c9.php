<?php use function Statamic\trans as __; ?>

<nav class="nav-main nav-mobile" v-cloak>
    <div class="nav-main-inner">
        <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($section['display'] !== 'Top Level'): ?>
                <h6 v-pre><?php echo e(__($section['display'])); ?></h6>
            <?php endif; ?>
            <ul>
                <?php $__currentLoopData = $section['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (! ($item->view())): ?>
                        <li class="<?php echo e($item->isActive() ? 'current' : ''); ?>" v-pre>
                            <a href="<?php echo e($item->url()); ?>">
                                <i><?php echo $item->svg(); ?></i><span><?php echo e(__($item->name())); ?></span>
                            </a>
                            <?php if($item->children() && $item->isActive()): ?>
                                <ul>
                                    <?php $__currentLoopData = $item->children(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="<?php echo e($child->isActive() ? 'current' : ''); ?>">
                                            <a href="<?php echo e($child->url()); ?>"><?php echo e(__($child->name())); ?></a>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php else: ?>
                        <?php echo $__env->make($item->view(), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</nav>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/partials/nav-mobile.blade.php ENDPATH**/ ?>