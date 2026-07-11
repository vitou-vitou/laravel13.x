# Buyer experience v1

## ADDED Requirements

### Requirement: Saved shipping addresses

Customers SHALL manage shipping addresses on their account and select one at checkout.

#### Scenario: Set default address

- **WHEN** customer marks an address as default
- **THEN** that address becomes the default for checkout and other addresses are not default

#### Scenario: Checkout snapshots address

- **WHEN** customer places an order with a selected shipping address
- **THEN** the order stores an immutable JSON snapshot of that address

### Requirement: Wishlist

Authenticated customers SHALL save products to a wishlist and add them to the cart from the wishlist page.

#### Scenario: Toggle wishlist on product

- **WHEN** customer clicks save on a product they have not wishlisted
- **THEN** the product appears on `/wishlist`

#### Scenario: Remove from wishlist

- **WHEN** customer removes a product from the wishlist
- **THEN** it no longer appears on `/wishlist`

### Requirement: Order timeline

Order detail SHALL show a per-vendor-group timeline of fulfillment steps.

#### Scenario: Shipped group shows tracking

- **WHEN** a vendor group is shipped with a tracking number
- **THEN** the customer sees a shipped step and the tracking number on the timeline
