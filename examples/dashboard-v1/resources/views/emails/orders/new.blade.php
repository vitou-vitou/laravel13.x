<p>A new order was placed.</p>

<p><strong>Order #{{ $order->id }}</strong></p>
<p>Customer: {{ $order->customer->name }} ({{ $order->customer->email }})</p>
<p>Total: {{ $order->formattedAmount() }}</p>
<p>Status: {{ $order->statusLabel() }}</p>

@if ($order->items->isNotEmpty())
    <p>Items:</p>
    <ul>
        @foreach ($order->items as $item)
            <li>{{ $item->product_name }} × {{ $item->quantity }} — ${{ number_format($item->line_total_cents / 100, 2) }}</li>
        @endforeach
    </ul>
@endif

<p><a href="{{ $adminUrl }}">View in admin</a></p>
