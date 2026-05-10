<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ffffff">
        <title><?php echo e($title ?? 'Successfully logged in'); ?></title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link
            href="https://fonts.bunny.net/css?family=inter:400,600,700&display=swap"
            rel="stylesheet"
        >
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <style>
            body {
                font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            }
        </style>
    </head>
    <body class="flex min-h-dvh flex-col bg-white text-neutral-950 antialiased">
        <header class="shrink-0 px-6 pt-8 sm:px-10">
            <div class="flex items-center gap-2">
                
                <svg
                    class="h-8 w-[2.35rem] shrink-0"
                    viewBox="0 0 236 200"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        fill="#0061FE"
                        d="M58.75 0 0 37.32 58.75 74.64 117.5 37.32 58.75 0zm117.5 0L117.5 37.32l58.75 37.32 58.75-37.32L176.25 0zM58.75 149.28 0 186.6l58.75 37.32 58.75-37.32-58.75-37.32zm117.5 0-58.75 37.32 58.75 37.32 58.75-37.32-58.75-37.32z"
                    />
                </svg>
                <span class="text-[1.375rem] font-semibold leading-none tracking-tight text-black">Dropbox</span>
            </div>
        </header>

        <main class="flex flex-1 flex-col items-center justify-center px-6 pb-16 pt-6 text-center sm:px-8">
            <div class="flex w-full max-w-[26rem] flex-col items-center">
                
                <div
                    class="mb-8 flex h-[5.5rem] w-[5.5rem] items-center justify-center rounded-full border-[3px] border-black"
                    aria-hidden="true"
                >
                    <svg
                        class="h-12 w-12 text-black"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.25"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h1 class="text-balance text-[1.75rem] font-bold leading-tight tracking-tight text-black sm:text-[2rem]">
                    Successfully logged in
                </h1>
                <p class="mt-3 text-[1rem] font-normal leading-snug text-black">
                    Now, explore Dropbox on desktop.
                </p>

                <a
                    href="<?php echo e($ctaUrl ?? 'https://www.dropbox.com/install'); ?>"
                    class="mt-10 inline-flex w-full max-w-none items-center justify-center rounded-md bg-[#0061FE] px-6 py-3.5 text-[1rem] font-bold leading-none text-white shadow-none outline-none ring-offset-2 transition-colors hover:bg-[#0058e6] focus-visible:ring-2 focus-visible:ring-[#0061FE]/40 sm:min-h-[3.25rem]"
                >
                    Open Dropbox
                </a>
            </div>
        </main>
    </body>
</html>
<?php /**PATH C:\Users\vitou\OneDrive\Documents\GitHub\laravel13.x\examples\oauth-desktop-success\resources\views/oauth/desktop-success.blade.php ENDPATH**/ ?>