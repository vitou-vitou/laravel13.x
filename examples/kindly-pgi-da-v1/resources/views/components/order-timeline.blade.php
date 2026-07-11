@props(['group', 'orderPaid' => false])

@php
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
@endphp

<ol class="flex flex-wrap gap-2 text-xs sm:text-sm">
    @foreach ($steps as $index => $step)
        <li class="flex items-center gap-2">
            <span @class([
                'flex h-6 w-6 items-center justify-center rounded-full font-semibold',
                'bg-brand-600 text-white' => $step['done'],
                'bg-stone-200 text-stone-500' => ! $step['done'],
            ])>{{ $index + 1 }}</span>
            <span @class([
                'font-medium',
                'text-stone-900' => $step['done'],
                'text-stone-400' => ! $step['done'],
            ])>{{ $step['label'] }}</span>
            @if (! $loop->last)
                <span class="hidden text-stone-300 sm:inline">→</span>
            @endif
        </li>
    @endforeach
</ol>

@if ($shipped && $group->tracking_number)
    <p class="mt-2 text-sm text-stone-600">
        Tracking: <span class="font-mono font-medium text-stone-900">{{ $group->tracking_number }}</span>
    </p>
@endif
