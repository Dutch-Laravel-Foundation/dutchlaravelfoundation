<?php use function Statamic\trans as __; ?>

<?php $str = app('Statamic\Support\Str'); ?>

<?php $__env->startSection('title', __('Log in')); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('statamic::partials.outside-logo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="max-w-xs rounded shadow-lg flex items-center justify-center relative mx-auto">
    <div class="outside-shadow absolute inset-0"></div>
    <div class="card auth-card">
        <login inline-template :show-email-login="!<?php echo e($str::bool($oauth)); ?>" :has-error="<?php echo e($str::bool(count($errors) > 0)); ?>">
        <div>
            <?php if($oauth): ?>
                <div class="login-oauth-providers">
                    <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="provider mb-2">
                            <a href="<?php echo e($provider->loginUrl()); ?>?redirect=<?php echo e(parse_url(cp_route('index'))['path']); ?>" class="w-full btn-primary">
                                <?php echo e(__('Log in with :provider', ['provider' => $provider->label()])); ?>

                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if($emailLoginEnabled): ?>
                    <div class="text-center text-sm text-gray-700 py-6">&mdash; <?php echo e(__('or')); ?> &mdash;</div>

                    <div class="login-with-email" v-if="! showEmailLogin">
                        <a class="btn w-full" @click.prevent="showEmailLogin = true">
                            <?php echo e(__('Log in with email')); ?>

                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" v-show="showEmailLogin" class="email-login select-none" <?php if($oauth): ?> v-cloak <?php endif; ?> @submit="busy = true">
                <?php echo csrf_field(); ?>


                <input type="hidden" name="referer" value="<?php echo e($referer); ?>" />

                <div class="mb-8">
                    <label class="mb-2" for="input-email"><?php echo e(__('Email')); ?></label>
                    <input type="text" class="input-text input-text" name="email" value="<?php echo e(old('email')); ?>" autofocus id="input-email">
                    <?php if($hasError('email')): ?><div class="text-red-500 text-xs mt-2"><?php echo e($errors->first('email')); ?></div><?php endif; ?>
                </div>

                <div class="mb-8">
                    <label class="mb-2" for="input-password"><?php echo e(__('Password')); ?></label>
                    <input type="password" class="input-text input-text" name="password" id="input-password">
                    <?php if($hasError('password')): ?><div class="text-red-500 text-xs mt-2"><?php echo e($errors->first('password')); ?></div><?php endif; ?>
                </div>
                <div class="flex justify-between items-center">
                    <label for="remember-me" class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" id="remember-me">
                        <span class="rtl:mr-2 ltr:ml-2"><?php echo e(__('Remember me')); ?></span>
                    </label>
                    <button type="submit" class="btn-primary" :disabled="busy"><?php echo e(__('Log in')); ?></button>
                </div>
            </form>
        </div>
        </login>
    </div>
</div>
<?php if($emailLoginEnabled): ?>
    <div class="w-full text-center mt-4 dark:mt-6">
        <a href="<?php echo e(cp_route('password.request')); ?>" class="forgot-password-link text-sm opacity-75 hover:opacity-100">
            <?php echo e(__('Forgot password?')); ?>

        </a>
    </div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('statamic::outside', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/dlf_prod/domains/dutchlaravelfoundation.nl/current/vendor/statamic/cms/src/Providers/../../resources/views/auth/login.blade.php ENDPATH**/ ?>