<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['product']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $minPrice = $product->variants->min('price_cents');
    $isNew = $product->created_at?->isAfter(now()->subDays(7));
    $vendorRating = $product->vendor?->rating_count > 0 ? (float) $product->vendor->rating_avg : null;
?>

<a href="<?php echo e(route('catalog.show', $product)); ?>" class="store-card catalog-feed-card group flex flex-col">
    <div class="relative aspect-square overflow-hidden bg-stone-100">
        <img
            src="<?php echo e($product->displayImageUrl()); ?>"
            alt="<?php echo e($product->name); ?>"
            class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
            loading="lazy"
        />
        <?php if($isNew): ?>
            <div class="absolute left-1.5 top-1.5 sm:left-2 sm:top-2">
                <span class="catalog-badge-new">New</span>
            </div>
        <?php endif; ?>
    </div>
    <div class="flex flex-1 flex-col gap-1 p-2 sm:gap-1.5 sm:p-3">
        <h3 class="line-clamp-2 text-xs font-medium leading-snug text-stone-900 group-hover:text-brand-700 sm:text-sm sm:font-semibold">
            <?php echo e($product->name); ?>

        </h3>
        <div class="mt-auto flex items-end justify-between gap-2 pt-0.5">
            <?php if($minPrice !== null): ?>
                <p class="catalog-price">
                    <span class="text-[10px] font-semibold text-brand-600/90 sm:text-xs">$</span><?php echo e(number_format($minPrice / 100, 2)); ?>

                </p>
            <?php endif; ?>
            <?php if($vendorRating !== null): ?>
                <p class="shrink-0 text-[10px] text-stone-500 sm:text-xs" aria-label="Vendor rating <?php echo e(number_format($vendorRating, 1)); ?>">
                    <span class="text-amber-500" aria-hidden="true">★</span> <?php echo e(number_format($vendorRating, 1)); ?>

                </p>
            <?php endif; ?>
        </div>
        <p class="truncate text-[10px] text-stone-500 sm:text-xs">
            <?php echo e($product->vendor?->store_name ?? 'Marketplace'); ?>

        </p>
    </div>
</a>
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/components/product-card.blade.php ENDPATH**/ ?>