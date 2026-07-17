## 1. Mailable

- [x] 1.1 Create `NewOrderMail` (queued) + blade view

## 2. Notifier service

- [x] 2.1 `OrderMailNotifier::notifyAdmins(Order $order)`
- [x] 2.2 Wire into `CheckoutService` after transaction

## 3. Tests

- [x] 3.1 Checkout queues mail to admin users
- [x] 3.2 Mail not sent when no admins
- [x] 3.3 Mailable subject and view data
- [x] 3.4 Cart checkout feature test
