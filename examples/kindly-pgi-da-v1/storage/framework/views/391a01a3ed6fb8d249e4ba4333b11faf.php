<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="font-sans antialiased text-stone-900">
        <div class="flex min-h-screen flex-col items-center justify-center bg-stone-50 px-4 py-10 sm:px-6">
            <a href="<?php echo e(route('home')); ?>" class="mb-8 flex items-center gap-2">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white">M</span>
                <span class="text-lg font-semibold text-stone-900"><?php echo e(config('app.name')); ?></span>
            </a>

            <div class="w-full max-w-md store-panel">
                <?php echo e($slot); ?>

            </div>
        </div>
    </body>
</html>
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/layouts/guest.blade.php ENDPATH**/ ?>