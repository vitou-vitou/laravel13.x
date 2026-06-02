<p>Thanks for your order, {{ $order->user->name }}.</p>
<p>Your payment for order #{{ $order->id }} has been confirmed.</p>
<p>Total: {{ $order->formattedTotal() }}</p>
