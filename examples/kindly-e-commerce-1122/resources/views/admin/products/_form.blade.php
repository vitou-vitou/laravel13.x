<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium mb-1" for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $product->name ?? '') }}" required class="w-full rounded border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1" for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="w-full rounded border-gray-300">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1" for="price_cents">Price (cents)</label>
        <input id="price_cents" name="price_cents" type="number" min="1" value="{{ old('price_cents', $product->price_cents ?? '') }}" required class="w-full rounded border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1" for="stock_quantity">Stock</label>
        <input id="stock_quantity" name="stock_quantity" type="number" min="0" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required class="w-full rounded border-gray-300">
    </div>
    <div class="flex items-center gap-2">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $product->is_active ?? true)) class="rounded border-gray-300">
        <label for="is_active" class="text-sm">Active in shop</label>
    </div>
</div>
