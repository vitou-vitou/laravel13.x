<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="bg-stone-50 pb-4 sm:pb-8">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <nav class="mb-4 text-sm text-stone-500 sm:mb-6">
                <a href="<?php echo e(route('catalog.index')); ?>" class="hover:text-brand-600">Shop</a>
                <span class="mx-2">/</span>
                <span class="text-stone-800"><?php echo e($product->name); ?></span>
            </nav>

            <div class="grid gap-6 lg:grid-cols-2 lg:gap-10">
                <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-sm">
                    <img
                        src="<?php echo e($product->displayImageUrl()); ?>"
                        alt="<?php echo e($product->name); ?>"
                        class="aspect-square w-full object-cover"
                    />
                </div>

                <div class="lg:sticky lg:top-8 lg:self-start">
                    <div class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-sm sm:p-8">
                        <p class="text-sm font-semibold uppercase tracking-wide text-brand-600">
                            <?php echo e($product->vendor->store_name); ?>

                        </p>
                        <h1 class="mt-2 text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl"><?php echo e($product->name); ?></h1>
                        <?php if(auth()->guard()->check()): ?>
                            <form method="POST" action="<?php echo e(route('wishlist.store', $product)); ?>" class="mt-3">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="min-h-11 text-sm font-medium <?php echo e(($isWishlisted ?? false) ? 'text-red-600' : 'text-stone-500'); ?> hover:text-red-600">
                                    <?php echo e(($isWishlisted ?? false) ? '♥ Saved' : '♡ Save to wishlist'); ?>

                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if($product->category): ?>
                            <p class="mt-2 text-sm text-stone-500"><?php echo e($product->category->name); ?></p>
                        <?php endif; ?>
                        <p class="mt-4 text-stone-600 leading-relaxed"><?php echo e($product->description); ?></p>

                        <form method="POST" action="<?php echo e(route('cart.store')); ?>" class="mt-8 space-y-4 border-t border-stone-100 pt-6">
                            <?php echo csrf_field(); ?>
                            <div>
                                <label for="product_variant_id" class="block text-sm font-medium text-stone-700">Variant</label>
                                <select id="product_variant_id" name="product_variant_id" class="store-input mt-1 min-h-11" required>
                                    <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($variant->id); ?>">
                                            <?php echo e($variant->name); ?> — <?php echo e($variant->formattedPrice()); ?> (<?php echo e($variant->stock_qty); ?> in stock)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-stone-700">Quantity</label>
                                <input id="quantity" type="number" name="quantity" value="1" min="1" class="store-input mt-1 min-h-11 w-28">
                            </div>
                            <button type="submit" class="btn-brand min-h-11 w-full sm:w-auto">Add to cart</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/catalog/show.blade.php ENDPATH**/ ?>