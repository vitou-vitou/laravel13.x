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
    <?php if (isset($component)) { $__componentOriginal8942416bf3d0109b1f5eeee2be83f28c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8942416bf3d0109b1f5eeee2be83f28c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store-page','data' => ['title' => 'Wishlist','max' => 'max-w-7xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Wishlist','max' => 'max-w-7xl']); ?>
        <?php if (isset($component)) { $__componentOriginal498a8b9574ec42a6d3736fbb53639d85 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal498a8b9574ec42a6d3736fbb53639d85 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash-status','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal498a8b9574ec42a6d3736fbb53639d85)): ?>
<?php $attributes = $__attributesOriginal498a8b9574ec42a6d3736fbb53639d85; ?>
<?php unset($__attributesOriginal498a8b9574ec42a6d3736fbb53639d85); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal498a8b9574ec42a6d3736fbb53639d85)): ?>
<?php $component = $__componentOriginal498a8b9574ec42a6d3736fbb53639d85; ?>
<?php unset($__componentOriginal498a8b9574ec42a6d3736fbb53639d85); ?>
<?php endif; ?>

        <?php if($products->isEmpty()): ?>
            <div class="store-card mt-6 p-10 text-center">
                <p class="text-lg font-medium text-stone-900">Your wishlist is empty</p>
                <a href="<?php echo e(route('catalog.index')); ?>" class="btn-brand mt-6 inline-flex">Browse products</a>
            </div>
        <?php else: ?>
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="store-card overflow-hidden">
                        <a href="<?php echo e(route('catalog.show', $product)); ?>">
                            <img src="<?php echo e($product->displayImageUrl()); ?>" alt="<?php echo e($product->name); ?>" class="aspect-square w-full object-cover">
                        </a>
                        <div class="p-4 space-y-3">
                            <a href="<?php echo e(route('catalog.show', $product)); ?>" class="font-semibold text-stone-900 hover:text-brand-700"><?php echo e($product->name); ?></a>
                            <p class="text-sm text-stone-500"><?php echo e($product->vendor->store_name); ?></p>
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="<?php echo e(route('wishlist.cart', $product)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn-brand text-sm">Add to cart</button>
                                </form>
                                <form method="POST" action="<?php echo e(route('wishlist.destroy', $product)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn-brand-outline text-sm">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8942416bf3d0109b1f5eeee2be83f28c)): ?>
<?php $attributes = $__attributesOriginal8942416bf3d0109b1f5eeee2be83f28c; ?>
<?php unset($__attributesOriginal8942416bf3d0109b1f5eeee2be83f28c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8942416bf3d0109b1f5eeee2be83f28c)): ?>
<?php $component = $__componentOriginal8942416bf3d0109b1f5eeee2be83f28c; ?>
<?php unset($__componentOriginal8942416bf3d0109b1f5eeee2be83f28c); ?>
<?php endif; ?>
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
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/wishlist/index.blade.php ENDPATH**/ ?>