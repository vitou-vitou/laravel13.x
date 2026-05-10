<?php $__env->startSection('title', __('home.title')); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?php echo e(__('home.title')); ?></h1>
    <p class="mt-4 max-w-2xl text-lg text-zinc-600"><?php echo e(__('home.lead')); ?></p>

    <p class="mt-6 text-sm text-zinc-500"><?php echo e(__('home.demo_price_label')); ?>:
        <span class="font-mono text-base text-zinc-900"><?php echo e(\Illuminate\Support\Number::currency(1080, 'SGD', 'en_SG')); ?></span>
    </p>

    <p class="mt-8">
        <a href="<?php echo e(route('services.laravel')); ?>" class="inline-flex rounded-md bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">
            <?php echo e(__('home.cta_services')); ?>

        </a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.marketing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\vitou\OneDrive\Documents\GitHub\laravel13.x\examples\my-sg-laravel\resources\views/home.blade.php ENDPATH**/ ?>