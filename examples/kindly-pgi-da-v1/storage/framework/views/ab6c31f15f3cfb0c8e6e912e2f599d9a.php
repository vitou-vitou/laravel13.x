<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['group', 'orderPaid' => false]));

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

foreach (array_filter((['group', 'orderPaid' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Enums\OrderGroupStatus;

    $status = $group->status;
    $confirmed = in_array($status, [
        OrderGroupStatus::Confirmed,
        OrderGroupStatus::Processing,
        OrderGroupStatus::Shipped,
        OrderGroupStatus::Delivered,
        OrderGroupStatus::Completed,
    ], true);
    $shipped = in_array($status, [
        OrderGroupStatus::Shipped,
        OrderGroupStatus::Delivered,
        OrderGroupStatus::Completed,
    ], true);
    $delivered = in_array($status, [
        OrderGroupStatus::Delivered,
        OrderGroupStatus::Completed,
    ], true);

    $steps = [
        ['label' => 'Paid', 'done' => $orderPaid],
        ['label' => 'Confirmed', 'done' => $confirmed],
        ['label' => 'Shipped', 'done' => $shipped],
        ['label' => 'Delivered', 'done' => $delivered],
    ];
?>

<ol class="flex flex-wrap gap-2 text-xs sm:text-sm">
    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="flex items-center gap-2">
            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'flex h-6 w-6 items-center justify-center rounded-full font-semibold',
                'bg-brand-600 text-white' => $step['done'],
                'bg-stone-200 text-stone-500' => ! $step['done'],
            ]); ?>"><?php echo e($index + 1); ?></span>
            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'font-medium',
                'text-stone-900' => $step['done'],
                'text-stone-400' => ! $step['done'],
            ]); ?>"><?php echo e($step['label']); ?></span>
            <?php if(! $loop->last): ?>
                <span class="hidden text-stone-300 sm:inline">→</span>
            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ol>

<?php if($shipped && $group->tracking_number): ?>
    <p class="mt-2 text-sm text-stone-600">
        Tracking: <span class="font-mono font-medium text-stone-900"><?php echo e($group->tracking_number); ?></span>
    </p>
<?php endif; ?>
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/components/order-timeline.blade.php ENDPATH**/ ?>