<?php
    $consent = request()->cookie('cookie_consent');
?>

<?php if(! $consent): ?>
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-zinc-200 bg-white p-4 shadow-lg">
        <div class="mx-auto flex max-w-5xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-zinc-700">
                <?php echo e(__('cookie.message')); ?>

                <a href="<?php echo e(route('privacy')); ?>" class="font-medium underline"><?php echo e(__('nav.privacy')); ?></a>
            </p>
            <form method="POST" action="<?php echo e(route('cookie.consent')); ?>" class="flex shrink-0 gap-2">
                <?php echo csrf_field(); ?>
                <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800">
                    <?php echo e(__('cookie.accept')); ?>

                </button>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\vitou\OneDrive\Documents\GitHub\laravel13.x\examples\my-sg-laravel\resources\views/components/cookie-banner.blade.php ENDPATH**/ ?>