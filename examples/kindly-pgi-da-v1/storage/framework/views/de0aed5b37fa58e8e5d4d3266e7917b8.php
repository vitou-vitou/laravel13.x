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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store-page','data' => ['title' => 'Your cart','max' => 'max-w-4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Your cart','max' => 'max-w-4xl']); ?>
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

            <?php if($lines->isEmpty()): ?>
                <div class="store-card mt-6 p-10 text-center">
                    <p class="text-lg font-medium text-stone-900">Your cart is empty</p>
                    <p class="mt-1 text-stone-500">Browse the catalog and add a variant to get started.</p>
                    <a href="<?php echo e(route('catalog.index')); ?>" class="btn-brand mt-6 inline-flex">Browse products</a>
                </div>
            <?php else: ?>
                <div class="store-card mt-6 divide-y divide-stone-100">
                    <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-stone-900"><?php echo e($line->variant->product->name); ?> — <?php echo e($line->variant->name); ?></p>
                                <p class="text-sm text-stone-500"><?php echo e($line->variant->product->vendor->store_name); ?></p>
                            </div>
                            <form method="POST" action="<?php echo e(route('cart.update', $line->variant)); ?>" class="flex items-center gap-3">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <input type="number" name="quantity" value="<?php echo e($line->quantity); ?>" min="0" class="store-input w-24">
                                <button type="submit" class="text-sm font-medium text-brand-600 hover:text-brand-700">Update</button>
                            </form>
                            <p class="font-semibold text-stone-900">$<?php echo e(number_format($line->lineTotalCents() / 100, 2)); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="store-card mt-6 p-6">
                    <h2 class="font-semibold text-stone-900">Promo code</h2>
                    <?php if($appliedPromo): ?>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-emerald-50 px-4 py-3 text-sm">
                            <span class="font-medium text-emerald-900"><?php echo e($appliedPromo->code); ?> applied</span>
                            <form method="POST" action="<?php echo e(route('cart.promo.remove')); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="font-medium text-emerald-800 hover:text-emerald-900">Remove</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?php echo e(route('cart.promo.apply')); ?>" class="mt-3 flex flex-col gap-2 sm:flex-row">
                            <?php echo csrf_field(); ?>
                            <input type="text" name="code" placeholder="Enter code" class="store-input flex-1 uppercase" maxlength="40" required>
                            <button type="submit" class="btn-brand sm:shrink-0">Apply</button>
                        </form>
                        <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php endif; ?>
                </div>

                <div class="store-card mt-6 p-6">
                    <h2 class="font-semibold text-stone-900">Order summary</h2>
                    <div class="mt-4 space-y-2 text-sm text-stone-600">
                        <?php $__currentLoopData = $vendorSubtotals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subtotal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex justify-between">
                                <span><?php echo e($subtotal['vendor_name']); ?></span>
                                <span>$<?php echo e(number_format($subtotal['subtotal_cents'] / 100, 2)); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="mt-4 space-y-2 border-t border-stone-100 pt-4 text-sm">
                        <div class="flex justify-between text-stone-600">
                            <span>Subtotal</span>
                            <span>$<?php echo e(number_format($subtotalCents / 100, 2)); ?></span>
                        </div>
                        <?php if($discountCents > 0): ?>
                            <div class="flex justify-between text-emerald-700">
                                <span>Promo discount</span>
                                <span>−$<?php echo e(number_format($discountCents / 100, 2)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="mt-4 border-t border-stone-100 pt-4 text-xl font-bold text-stone-900">
                        Total: $<?php echo e(number_format($totalCents / 100, 2)); ?>

                    </p>

                    <?php if(auth()->guard()->check()): ?>
                        <?php if($shippingAddresses->isNotEmpty()): ?>
                            <div class="mt-6 space-y-2 border-t border-stone-100 pt-4">
                                <p class="text-sm font-medium text-stone-900">Ship to</p>
                                <?php $__currentLoopData = $shippingAddresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-stone-200 p-3 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50">
                                        <input
                                            type="radio"
                                            name="shipping_address_id"
                                            value="<?php echo e($address->id); ?>"
                                            class="mt-1"
                                            <?php if($address->is_default): echo 'checked'; endif; ?>
                                            form="checkout-form"
                                            required
                                        >
                                        <span class="text-sm text-stone-700">
                                            <span class="font-medium text-stone-900"><?php echo e($address->label); ?></span> — <?php echo e($address->formattedSingleLine()); ?>

                                        </span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('account.addresses.index')); ?>" class="text-sm text-brand-600 hover:text-brand-700">Manage addresses</a>
                                <?php $__errorArgs = ['shipping_address_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        <?php else: ?>
                            <p class="mt-6 text-sm text-stone-600">
                                <a href="<?php echo e(route('account.addresses.index')); ?>" class="font-medium text-brand-600 hover:text-brand-700">Add a shipping address</a> (optional for checkout).
                            </p>
                        <?php endif; ?>

                        <div class="mt-6 rounded-lg border border-stone-200 bg-stone-50/80 p-4 text-sm text-stone-600">
                            <p class="font-medium text-stone-900">Buyer protection</p>
                            <p class="mt-1">Pay securely with Stripe. If something goes wrong after delivery, open a dispute from your order page — our team reviews cases and can issue refunds when appropriate.</p>
                        </div>

                        <form id="checkout-form" method="POST" action="<?php echo e(route('checkout.store')); ?>" class="mt-6">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn-brand">Checkout</button>
                        </form>
                    <?php else: ?>
                        <p class="mt-6 text-sm text-stone-600">
                            <a href="<?php echo e(route('login')); ?>" class="font-medium text-brand-600 hover:text-brand-700">Log in</a> to checkout.
                        </p>
                    <?php endif; ?>
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
<?php /**PATH D:\laravel13.x\examples\kindly-pgi-da-v1\resources\views/cart/index.blade.php ENDPATH**/ ?>