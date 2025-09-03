<?php echo $xml_header; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

<?php $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e($entry->loc); ?></loc>
        <lastmod><?php echo e($entry->lastmod->format("c")); ?></lastmod>
        <?php if($entry->changefreq): ?>
            <changefreq><?php echo e($entry->changefreq); ?></changefreq>
        <?php endif; ?>
        <priority><?php if($entry->priority): ?><?php echo e($entry->priority); ?>

            <?php elseif($entry->path === '/'): ?> 1
            <?php else: ?> 0.8 <?php endif; ?>
        </priority>
    </url>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</urlset>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/pecotamic/sitemap/resources/views/sitemap.blade.php ENDPATH**/ ?>