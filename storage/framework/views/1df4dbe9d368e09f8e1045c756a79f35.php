<?php use function Statamic\trans as __; ?>
<?php $licenses = app('Statamic\Licensing\LicenseManager'); ?>

<?php if($licenses->outpostIsOffline()): ?>
    
<?php elseif($licenses->requestFailed()): ?>
    <div class="p-2 w-full fixed bottom-0 z-20">
        <div class="py-3 px-4 text-sm w-full rounded-md bg-yellow border border-yellow-dark dark:bg-dark-blue-100 dark:border-none">
        <?php if($licenses->usingLicenseKeyFile()): ?>
            <?php echo e(__('statamic::messages.outpost_license_key_error')); ?>

        <?php elseif($licenses->requestErrorCode() === 422): ?>
            <?php echo e(__('statamic::messages.outpost_error_422')); ?>

            <?php echo e(join(' ', $licenses->requestValidationErrors()->unique())); ?>

        <?php elseif($licenses->requestErrorCode() === 429): ?>
            <?php echo e(__('statamic::messages.outpost_error_429')); ?>

            <?php echo e(trans_choice('statamic::messages.try_again_in_seconds', $licenses->failedRequestRetrySeconds())); ?>

        <?php else: ?>
            <?php echo e(__('statamic::messages.outpost_issue_try_later')); ?>

        <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <?php if($licenses->invalid()): ?>
        <div class="p-2 w-full fixed bottom-0 z-2" v-cloak v-show="showBanner">
            <div class="
                py-3 px-4 text-sm w-full rounded-md
                <?php if($licenses->isOnTestDomain()): ?> bg-gray-800 dark:bg-dark-500 text-gray-300 <?php endif; ?>
                <?php if($licenses->isOnPublicDomain()): ?> bg-red-500 text-white <?php endif; ?>
            ">
                <?php if($licenses->isOnTestDomain()): ?>
                    <div class="flex items-center justify-between">
                        <span>
                            <b class="rtl:ml-2 ltr:mr-2"><?php echo e(__('Trial Mode')); ?>:</b>
                            <?php if($licenses->onlyAddonsAreInvalid()): ?>
                                <?php echo e(__('statamic::messages.licensing_trial_mode_alert_addons')); ?>

                            <?php elseif($licenses->onlyStatamicIsInvalid()): ?>
                                <?php echo e(__('statamic::messages.licensing_trial_mode_alert_statamic')); ?>

                            <?php else: ?>
                                <?php echo e(__('statamic::messages.licensing_trial_mode_alert')); ?>

                            <?php endif; ?>
                        </span>
                        <div class="flex">
                            <button @click="hideBanner" class="rtl:ml-4 ltr:mr-4 text-2xs opacity-50 hover:opacity-75"><?php echo e(__('Dismiss')); ?></button>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access licensing utility')): ?>
                            <a href="<?php echo e(cp_route('utilities.licensing')); ?>" class="text-2xs text-white hover:text-yellow flex items-center" aria-label="<?php echo e(__('Manage Licenses')); ?>">
                                <?php echo e(__('Manage Licenses')); ?> <?php echo Statamic::cpDirection() === 'ltr' ? '&rarr;' : '&larr;' ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center justify-between">
                        <?php if($licenses->onlyAddonsAreInvalid()): ?>
                            <?php echo e(__('statamic::messages.licensing_production_alert_addons')); ?>

                        <?php elseif($licenses->onlyStatamicIsInvalid()): ?>
                            <?php if($licenses->statamicNeedsRenewal()): ?>
                                <?php echo e(__('statamic::messages.licensing_production_alert_renew_statamic')); ?>

                            <?php else: ?>
                                <?php echo e(__('statamic::messages.licensing_production_alert_statamic')); ?>

                            <?php endif; ?>
                        <?php else: ?>
                            <?php echo e(__('statamic::messages.licensing_production_alert')); ?>

                        <?php endif; ?>
                        <div class="flex">
                            <button @click="hideBanner" class="rtl:ml-4 ltr:mr-4 text-2xs opacity-50 hover:opacity-75"><?php echo e(__('Dismiss')); ?></button>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access licensing utility')): ?>
                                <a href="<?php echo e(cp_route('utilities.licensing')); ?>" class="text-2xs text-white hover:text-yellow flex items-center" aria-label="<?php echo e(__('Manage Licenses')); ?>">
                                    <?php echo e(__('Manage Licenses')); ?> <?php echo Statamic::cpDirection() === 'ltr' ? '&rarr;' : '&larr;' ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/partials/licensing-alerts.blade.php ENDPATH**/ ?>