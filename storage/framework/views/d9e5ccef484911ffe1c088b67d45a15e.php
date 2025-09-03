<?php use function Statamic\trans as __; ?>

<?php if(config('statamic.cp.link_to_docs')): ?>
    <div class="flex justify-center text-center mt-16">
        <div class="bg-white dark:bg-dark-900 rounded-full px-6 py-2 shadow-sm text-sm text-gray-700 dark:text-dark-100"><?php echo $text ?? __('Learn more about :link', ['link' => '<a href="'.$url.'" target="_blank" rel="noopener noopener" class="text-blue hover:text-blue-700">'.$topic.'</a>']); ?></div>
    </div>
<?php endif; ?>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/partials/docs-callout.blade.php ENDPATH**/ ?>