<?php $__env->startSection('title', __('services.title')); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('services.title')); ?></h1>
    <p class="mt-4 max-w-2xl text-zinc-600"><?php echo e(__('services.lead')); ?></p>

    <h2 class="mt-10 text-xl font-semibold text-gray-900"><?php echo e(__('services.benefits_title')); ?></h2>
    <ul class="mt-4 list-disc space-y-2 pl-6 text-zinc-700">
        <li><?php echo e(__('services.benefit.performance')); ?></li>
        <li><?php echo e(__('services.benefit.security')); ?></li>
        <li><?php echo e(__('services.benefit.scale')); ?></li>
        <li><?php echo e(__('services.benefit.velocity')); ?></li>
    </ul>

    <h2 class="mt-10 text-xl font-semibold text-gray-900"><?php echo e(__('services.industries_title')); ?></h2>
    <ul class="mt-4 list-disc space-y-2 pl-6 text-zinc-700">
        <li><?php echo e(__('services.industry.fintech')); ?></li>
        <li><?php echo e(__('services.industry.healthcare')); ?></li>
        <li><?php echo e(__('services.industry.saas')); ?></li>
    </ul>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.marketing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\vitou\OneDrive\Documents\GitHub\laravel13.x\examples\my-sg-laravel\resources\views/services/laravel.blade.php ENDPATH**/ ?>