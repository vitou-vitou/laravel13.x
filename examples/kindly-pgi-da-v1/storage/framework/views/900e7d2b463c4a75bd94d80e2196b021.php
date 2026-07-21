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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store-page','data' => ['title' => 'Order #'.$order->id,'max' => 'max-w-4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Order #'.$order->id),'max' => 'max-w-4xl']); ?>
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

        <div class="store-panel mt-6 space-y-6">
            <div class="flex flex-wrap gap-4 text-sm">
                <p>Status: <span class="font-semibold text-stone-900"><?php echo e($order->status->value); ?></span></p>
                <p>Total: <span class="font-semibold text-stone-900"><?php echo e($order->formattedTotal()); ?></span></p>
            </div>

            <?php if($order->isPaid()): ?>
                <p class="rounded-lg border border-stone-200 bg-stone-50/80 px-4 py-3 text-sm text-stone-600">
                    <span class="font-medium text-stone-900">Buyer protection:</span>
                    Problems with a shipment? File a dispute on the vendor section below. Refunds are reviewed by admins when eligible.
                </p>
            <?php endif; ?>

            <?php if($order->shipping_address_snapshot): ?>
                <div class="rounded-xl border border-stone-100 bg-white p-4 text-sm text-stone-600">
                    <p class="font-medium text-stone-900">Ship to</p>
                    <p class="mt-1"><?php echo e($order->shipping_address_snapshot['name'] ?? ''); ?></p>
                    <p><?php echo e($order->shipping_address_snapshot['line1'] ?? ''); ?></p>
                    <?php if(! empty($order->shipping_address_snapshot['line2'])): ?>
                        <p><?php echo e($order->shipping_address_snapshot['line2']); ?></p>
                    <?php endif; ?>
                    <p>
                        <?php echo e($order->shipping_address_snapshot['city'] ?? ''); ?>,
                        <?php echo e($order->shipping_address_snapshot['region'] ?? ''); ?>

                        <?php echo e($order->shipping_address_snapshot['postal_code'] ?? ''); ?>

                    </p>
                </div>
            <?php endif; ?>

            <?php $__currentLoopData = $order->groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-xl border border-stone-100 bg-stone-50/50 p-4 space-y-3">
                    <div>
                        <p class="font-semibold text-stone-900"><?php echo e($group->vendor->store_name); ?></p>
                        <?php if (isset($component)) { $__componentOriginal1559e45aa3b06c08378a24b14c08207e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1559e45aa3b06c08378a24b14c08207e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.order-timeline','data' => ['group' => $group,'orderPaid' => $order->isPaid(),'class' => 'mt-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('order-timeline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['group' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($group),'order-paid' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->isPaid()),'class' => 'mt-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1559e45aa3b06c08378a24b14c08207e)): ?>
<?php $attributes = $__attributesOriginal1559e45aa3b06c08378a24b14c08207e; ?>
<?php unset($__attributesOriginal1559e45aa3b06c08378a24b14c08207e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1559e45aa3b06c08378a24b14c08207e)): ?>
<?php $component = $__componentOriginal1559e45aa3b06c08378a24b14c08207e; ?>
<?php unset($__componentOriginal1559e45aa3b06c08378a24b14c08207e); ?>
<?php endif; ?>
                    </div>
                    <ul class="space-y-2 text-sm">
                        <?php $__currentLoopData = $group->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-stone-700"><?php echo e($line->product_name_snapshot); ?> (<?php echo e($line->variant_name_snapshot); ?>) × <?php echo e($line->quantity); ?></span>
                                <?php if($order->isPaid() && $line->variant): ?>
                                    <a href="<?php echo e(route('reviews.create', [$order, $line->variant->product_id])); ?>" class="link-brand text-xs">Review</a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <?php if($order->isPaid() && ! $group->dispute && in_array($group->status->value, ['shipped', 'delivered', 'completed'], true)): ?>
                        <form method="POST" action="<?php echo e(route('disputes.store', $group)); ?>" class="space-y-2 border-t border-stone-200 pt-3">
                            <?php echo csrf_field(); ?>
                            <textarea name="reason" rows="2" class="store-input text-sm" placeholder="Describe the issue" required></textarea>
                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">File dispute</button>
                        </form>
                    <?php elseif($group->dispute): ?>
                        <a href="<?php echo e(route('disputes.show', $group->dispute)); ?>" class="link-brand text-sm">View dispute →</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($order->isPending()): ?>
                <p class="text-sm text-stone-600">Complete payment via Stripe checkout. If you already paid, refresh in a moment.</p>
            <?php endif; ?>
        </div>
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
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/orders/show.blade.php ENDPATH**/ ?>