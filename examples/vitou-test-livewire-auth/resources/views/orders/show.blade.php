<x-layouts::app :title="__('Order #' . $order->id)">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">Order #{{ $order->id }}</h1>
        <p class="text-zinc-600 dark:text-zinc-300">Status: <span class="font-medium">{{ $order->status }}</span></p>
        <p class="text-zinc-600 dark:text-zinc-300">Total: <span class="font-bold">{{ $order->currency }} {{ number_format($order->total, 2) }}</span></p>
    </div>
</x-layouts::app>
