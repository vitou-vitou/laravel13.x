<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => null, 'max' => 'max-w-5xl']));

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

foreach (array_filter((['title' => null, 'max' => 'max-w-5xl']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-stone-50 py-10">
    <div <?php echo e($attributes->merge(['class' => "mx-auto px-4 sm:px-6 lg:px-8 {$max}"])); ?>>
        <?php if($title): ?>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900"><?php echo e($title); ?></h1>
        <?php endif; ?>
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/components/store-page.blade.php ENDPATH**/ ?>