<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-stone-50">
            <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <!-- Page Heading -->
            <?php if(isset($header)): ?>
                <header class="border-b border-stone-200/80 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <div class="text-2xl font-bold tracking-tight text-stone-900">
                            <?php echo e($header); ?>

                        </div>
                    </div>
                </header>
            <?php endif; ?>

            <!-- Page Content -->
            <main class="<?php echo e(($stickyCartCount ?? 0) > 0 && ! request()->routeIs('cart.index', 'checkout.success', 'checkout.cancel') ? 'pb-24 sm:pb-0' : ''); ?>">
                <?php echo e($slot); ?>

            </main>

            <?php if (isset($component)) { $__componentOriginal46003327f1d5ea626059083a5f7ec4ab = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal46003327f1d5ea626059083a5f7ec4ab = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sticky-cart-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sticky-cart-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal46003327f1d5ea626059083a5f7ec4ab)): ?>
<?php $attributes = $__attributesOriginal46003327f1d5ea626059083a5f7ec4ab; ?>
<?php unset($__attributesOriginal46003327f1d5ea626059083a5f7ec4ab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal46003327f1d5ea626059083a5f7ec4ab)): ?>
<?php $component = $__componentOriginal46003327f1d5ea626059083a5f7ec4ab; ?>
<?php unset($__componentOriginal46003327f1d5ea626059083a5f7ec4ab); ?>
<?php endif; ?>
        </div>
    </body>
</html>
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/layouts/app.blade.php ENDPATH**/ ?>