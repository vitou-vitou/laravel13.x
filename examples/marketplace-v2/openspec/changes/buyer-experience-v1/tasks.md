## 1. Addresses

- [x] 1.1 Migration `shipping_addresses` + model + factory
- [x] 1.2 `ShippingAddressService` + policy (user owns record)
- [x] 1.3 `AddressController` + Blade CRUD under `/account/addresses`
- [x] 1.4 Checkout: validate `shipping_address_id`, snapshot JSON on `orders`
- [x] 1.5 Feature tests: CRUD, default, checkout snapshot

## 2. Wishlist

- [x] 2.1 Migration `wishlist_items` + model
- [x] 2.2 `WishlistService` + routes + controller
- [x] 2.3 PDP heart + `/wishlist` page + nav link
- [x] 2.4 Feature tests: toggle, list, add to cart

## 3. Order timeline

- [x] 3.1 `x-order-timeline` Blade component (steps from group status + dates)
- [x] 3.2 Wire into `orders/show` and `orders/index` snippet
- [x] 3.3 Feature test: shipped group shows tracking step

## 4. Verify

- [x] 4.1 Full `php artisan test` + `./bin/verify-example marketplace-v2`
- [x] 4.2 Update `docs/NEXT_SESSION.md` · archive change when done
