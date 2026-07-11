<?php

use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Admin\VendorSuspendController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DevStripeSimulateController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Privacy\GdprEraseController;
use App\Http\Controllers\Privacy\GdprExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Vendor\ConnectController as VendorConnectController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\VendorRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{product}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{variant}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/promo', [CartController::class, 'applyPromo'])->name('cart.promo.apply');
Route::delete('/cart/promo', [CartController::class, 'removePromo'])->name('cart.promo.remove');

Route::post('/stripe/webhook', StripeWebhookController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('stripe.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/{order}/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/{order}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/orders/{order}/products/{product}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/orders/{order}/products/{product}/review', [ReviewController::class, 'store'])->name('reviews.store');

    Route::post('/order-groups/{orderGroup}/disputes', [DisputeController::class, 'store'])->name('disputes.store');
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');
    Route::post('/disputes/{dispute}/messages', [DisputeController::class, 'message'])->name('disputes.message');

    Route::get('/vendor/apply', [VendorRegistrationController::class, 'create'])->name('vendor.apply');
    Route::post('/vendor/apply', [VendorRegistrationController::class, 'store'])->name('vendor.apply.store');

    Route::get('/privacy/export', GdprExportController::class)->name('privacy.export');
    Route::post('/privacy/erase', GdprEraseController::class)->name('privacy.erase');

    Route::get('/account/addresses', [ShippingAddressController::class, 'index'])->name('account.addresses.index');
    Route::post('/account/addresses', [ShippingAddressController::class, 'store'])->name('account.addresses.store');
    Route::patch('/account/addresses/{shippingAddress}', [ShippingAddressController::class, 'update'])->name('account.addresses.update');
    Route::delete('/account/addresses/{shippingAddress}', [ShippingAddressController::class, 'destroy'])->name('account.addresses.destroy');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::post('/wishlist/{product}/cart', [WishlistController::class, 'addToCart'])->name('wishlist.cart');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders/{orderGroup}', [VendorDashboardController::class, 'show'])->name('orders.show');
    Route::post('/orders/{orderGroup}/confirm', [VendorDashboardController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{orderGroup}/ship', [VendorDashboardController::class, 'ship'])->name('orders.ship');
    Route::post('/orders/{orderGroup}/deliver', [VendorDashboardController::class, 'deliver'])->name('orders.deliver');

    Route::get('/products', [VendorProductController::class, 'index'])->name('products.index');
    Route::get('/connect', [VendorConnectController::class, 'status'])->name('connect.status');
    Route::get('/connect/start', [VendorConnectController::class, 'start'])->name('connect.start');
    Route::get('/connect/callback', [VendorConnectController::class, 'callback'])->name('connect.callback');
    Route::get('/products/create', [VendorProductController::class, 'create'])->name('products.create');
    Route::post('/products', [VendorProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [VendorProductController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product}', [VendorProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [VendorProductController::class, 'destroy'])->name('products.destroy');

    // Refresh/bookmark on POST-only URLs → dashboard (avoids 405 after errors)
    Route::get('/orders/{orderGroup}/confirm', fn () => redirect()->route('vendor.dashboard'));
    Route::get('/orders/{orderGroup}/ship', fn () => redirect()->route('vendor.dashboard'));
    Route::get('/orders/{orderGroup}/deliver', fn () => redirect()->route('vendor.dashboard'));
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/audit', [AdminDashboardController::class, 'audit'])->name('audit');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');
    Route::get('/vendors', [VendorApprovalController::class, 'index'])->name('vendors.index');
    Route::post('/vendors/{vendor}/approve', [VendorApprovalController::class, 'approve'])->name('vendors.approve');
    Route::post('/vendors/{vendor}/suspend', [VendorSuspendController::class, 'suspend'])->name('vendors.suspend');
    Route::post('/vendors/{vendor}/activate', [VendorSuspendController::class, 'activate'])->name('vendors.activate');
    Route::get('/commission', [CommissionController::class, 'edit'])->name('commission.edit');
    Route::post('/commission', [CommissionController::class, 'update'])->name('commission.update');
    Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
    Route::post('/disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');
    Route::get('/promo-codes', [PromoCodeController::class, 'index'])->name('promo-codes.index');
    Route::post('/promo-codes', [PromoCodeController::class, 'store'])->name('promo-codes.store');
    Route::delete('/promo-codes/{promoCode}', [PromoCodeController::class, 'destroy'])->name('promo-codes.destroy');
});

if (app()->environment('local')) {
    Route::middleware('auth')->group(function () {
        Route::post('/dev/orders/{order}/simulate-stripe-paid', [DevStripeSimulateController::class, 'store'])
            ->name('dev.stripe.simulate-paid');
    });
}

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
