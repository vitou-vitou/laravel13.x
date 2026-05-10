<?php $__env->startSection('title', __('privacy.title')); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('privacy.title')); ?></h1>
    <p class="mt-6 max-w-2xl text-zinc-700"><?php echo e(__('privacy.body')); ?></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.marketing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\vitou\OneDrive\Documents\GitHub\laravel13.x\examples\my-sg-laravel\resources\views/privacy.blade.php ENDPATH**/ ?>