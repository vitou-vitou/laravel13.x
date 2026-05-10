<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title><?php echo $__env->yieldContent('title', config('app.name')); ?></title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-4 py-4">
                <a href="<?php echo e(route('home')); ?>" class="font-semibold text-gray-900"><?php echo e(config('app.name')); ?></a>
                <nav class="flex flex-wrap items-center gap-4 text-sm text-zinc-700">
                    <a href="<?php echo e(route('home')); ?>" class="hover:underline"><?php echo e(__('nav.home')); ?></a>
                    <a href="<?php echo e(route('services.laravel')); ?>" class="hover:underline"><?php echo e(__('nav.services_laravel')); ?></a>
                    <a href="<?php echo e(route('privacy')); ?>" class="hover:underline"><?php echo e(__('nav.privacy')); ?></a>
                    <span class="text-zinc-400">|</span>
                    <a href="<?php echo e(route('locale.switch', ['locale' => 'en'])); ?>" class="<?php echo e(app()->isLocale('en') ? 'font-semibold text-gray-900' : 'hover:underline'); ?>"><?php echo e(__('nav.locale.en')); ?></a>
                    <a href="<?php echo e(route('locale.switch', ['locale' => 'zh_CN'])); ?>" class="<?php echo e(app()->isLocale('zh_CN') ? 'font-semibold text-gray-900' : 'hover:underline'); ?>"><?php echo e(__('nav.locale.zh')); ?></a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-10">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <?php if (isset($component)) { $__componentOriginalceaf3fe1766c78c4907eaa2dfb569a19 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalceaf3fe1766c78c4907eaa2dfb569a19 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.cookie-banner','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('cookie-banner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalceaf3fe1766c78c4907eaa2dfb569a19)): ?>
<?php $attributes = $__attributesOriginalceaf3fe1766c78c4907eaa2dfb569a19; ?>
<?php unset($__attributesOriginalceaf3fe1766c78c4907eaa2dfb569a19); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalceaf3fe1766c78c4907eaa2dfb569a19)): ?>
<?php $component = $__componentOriginalceaf3fe1766c78c4907eaa2dfb569a19; ?>
<?php unset($__componentOriginalceaf3fe1766c78c4907eaa2dfb569a19); ?>
<?php endif; ?>

        <?php if(Route::has('login')): ?>
            <footer class="border-t border-zinc-200 bg-white py-6 text-center text-xs text-zinc-500">
                <a href="<?php echo e(route('login')); ?>" class="underline"><?php echo e(__('nav.login')); ?></a>
                <?php if(Route::has('register')): ?>
                    <span class="mx-2">·</span>
                    <a href="<?php echo e(route('register')); ?>" class="underline"><?php echo e(__('nav.register')); ?></a>
                <?php endif; ?>
            </footer>
        <?php endif; ?>
    </body>
</html>
<?php /**PATH C:\Users\vitou\OneDrive\Documents\GitHub\laravel13.x\examples\my-sg-laravel\resources\views/layouts/marketing.blade.php ENDPATH**/ ?>