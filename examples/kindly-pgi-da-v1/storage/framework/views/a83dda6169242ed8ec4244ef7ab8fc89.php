<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['active']));

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

foreach (array_filter((['active']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg border-l-4 border-brand-600 bg-brand-50 py-2 ps-3 pe-4 text-start text-base font-semibold text-brand-700'
            : 'block w-full rounded-lg border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-stone-600 hover:border-stone-300 hover:bg-stone-50 hover:text-stone-900';
?>

<a <?php echo e($attributes->merge(['class' => $classes])); ?>>
    <?php echo e($slot); ?>

</a>
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/components/responsive-nav-link.blade.php ENDPATH**/ ?>