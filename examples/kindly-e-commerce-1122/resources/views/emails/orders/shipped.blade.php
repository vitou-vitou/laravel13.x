<p>Good news, {{ $order->user->name }}.</p>
<p>Your order #{{ $order->id }} has been shipped.</p>
<p>Total: {{ $order->formattedTotal() }}</p>
