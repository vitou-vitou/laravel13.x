<?php
    $cart = app(\App\Services\CartService::class);
    $stickyCartCount = $cart->itemCount();
    $stickyCartTotalCents = $cart->totalCents();
    $hideOnRoutes = ['cart.index', 'checkout.success', 'checkout.cancel'];
    $visible = $stickyCartCount > 0 && ! request()->routeIs(...$hideOnRoutes);
?>

<?php if($visible): ?>
    <div
        class="sticky-cart-bar fixed inset-x-0 bottom-0 z-40 border-t border-stone-200 bg-white/95 px-4 py-3 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] backdrop-blur sm:hidden"
        role="region"
        aria-label="Cart summary"
    >
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-stone-900"><?php echo e($stickyCartCount); ?> <?php echo e(Str::plural('item', $stickyCartCount)); ?></p>
                <p class="text-xs text-stone-500">$<?php echo e(number_format($stickyCartTotalCents / 100, 2)); ?></p>
            </div>
            <a href="<?php echo e(route('cart.index')); ?>" class="btn-brand min-h-11 shrink-0 px-6">View cart</a>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/components/sticky-cart-bar.blade.php ENDPATH**/ ?>