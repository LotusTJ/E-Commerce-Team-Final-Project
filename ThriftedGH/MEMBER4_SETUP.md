# Member 4 — Tasks 8, 9 and 10

Implemented on `member4/cart-payments-orders`, based on main commit `2095c1e`.

## Acceptance status

| Task | Implemented behaviour | Verification |
|---|---|---|
| 8 — Multi-seller cart | Buyer-linked database cart, add/remove, product images and seller names, quantity one, duplicate protection, database prices and checkout revalidation | Database and HTTP checks passed |
| 9 — Orders and Paystack | Server-calculated GHS totals, 15-minute atomic reservations, test checkout, server verification, authenticated webhooks, replay-safe paid orders, sold inventory and 10% commission | Real Paystack TEST success and decline verified locally; callback, earnings and expiry verified; actual public webhook delivery deferred by user choice |
| 10 — Order management | Buyer history/details, seller-only paid order items and sequential fulfilment updates, admin order/review/commission overview, separate paid and delivered sales | Access-control and HTTP checks passed |

This implementation has not been pushed or merged. Existing Task 5 files outside this clone and unrelated XAMPP databases were not modified. At the user’s request, the newly created thriftedgh database now contains demo accounts, products and sandbox test orders.

See `PAYSTACK_TEST_EVIDENCE.md` for the real sandbox results, exact local callback URL and deferred public delivery check.

## Set up the application

1. Back up your current database before applying changes. Keep the cloned repository separate from any older project copy.
2. Open the repository in VS Code and keep the `member4/cart-payments-orders` branch selected.
3. On an EXISTING database, select the correct database in phpMyAdmin and run **only** `database/migrations/008_member4.sql`, once. It adds reservation/payment fields and an order/product uniqueness constraint; it does not replace accounts or product data. Do not import the full schema over an existing database. If any statement fails, inspect the current schema before retrying: MySQL DDL is not transactionally rolled back.
4. On an EMPTY new database, import `database/thriftedgh.sql` instead. It already includes the Member 4 additions, so do not also apply the migration.
5. Keep your private `core/db_cred.php` if you already have one. New users can copy `core/db_cred.example.php` to `core/db_cred.php` and enter their local database values. This file is now ignored by Git. Environment variables are also supported: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`, `DB_SOCKET`. Private constants take precedence when a local credentials file exists. Default values target local XAMPP only.
6. Copy `core/payment.local.example.php` to `core/payment.local.php`. Enter your `sk_test_…` key locally and set `APP_URL` to the exact application root. Do not share or commit that file. Live keys are rejected. Environment variables can override the payment configuration. `.env` files are not automatically loaded.
7. For XAMPP, use the application's actual folder URL, such as `http://localhost/ThriftedGH`. The existing team's login uses `/thriftedgh/`; keep that URL working or coordinate a shared authentication-path update before hosting under a different path.
8. Enable PHP `mysqli` with mysqlnd and `curl`. Start Apache and MySQL.

The database configuration loader supports existing private credentials; removing `db_cred.php` from Git does not remove teammates' existing private files from their computers. Take a private backup before merging because Git can report a conflict with a locally modified tracked credentials file. Historical repository commits still contain the old development defaults; any real exposed credentials must be replaced privately.

## Paystack test setup

- Set the test dashboard callback to `<APP_URL>/payments/callback.php`.
- Set the test webhook to a publicly reachable HTTPS URL ending in `/payments/webhook.php`. Paystack cannot reach a plain localhost URL. A deployed test environment is suitable; keep real and test settings separate.
- If your dashboard has a webhook signing secret (`whsec_…`), set `PAYSTACK_WEBHOOK_SECRET`. The handler then requires a timestamped `Paystack-Signature` and checks the five-minute replay window. Without that configuration it verifies legacy `X-Paystack-Signature` using the test API key.
- Both callback and webhook verify the transaction with Paystack from the server. Settlement checks reference, successful status, integer amount in pesewas, GHS currency and test domain. Browser query parameters never mark an order paid.
- A successful result is displayed at `views/buyer/payment_result.php?reference=...` only to the purchasing buyer.

Provider documentation: [Accept payments](https://paystack.com/docs/payments/accept-payments/), [Verify payments](https://paystack.com/docs/payments/verify-payments/), [Webhooks](https://paystack.com/docs/payments/webhooks/).

## Local development worker

Run `php scripts/payment_worker.php` while developing to reconcile test payments and release expired reservations every minute. Stop it with Ctrl-C. `php scripts/payment_worker.php --once` performs one cycle. This is a foreground local worker, not a system startup service. On hosting, use the server scheduler described below instead.

## Reservation maintenance

Configure a server scheduler to run `scripts/reconcile_payments.php` every minute, using the same PHP configuration, database and private payment settings as the website. This verifies pending payments and releases expired reservations. The script is CLI-only.

On a Mac, the PHP executable is commonly `/Applications/XAMPP/xamppfiles/bin/php`. Pass the script's absolute path to it. For inventory cleanup without contacting Paystack, the CLI-only `scripts/expire_reservations.php` is also available. Checkout performs expired-reservation cleanup before creating a new order, but that is not a substitute for a regular worker on a deployed application.

- Known failed/abandoned payments release their own reservations.
- Definite provider validation/authentication rejections release reservations immediately. Network/initialization errors keep reservations until verification or expiry because Paystack may have received the request despite a lost response. The buyer can check payment status from the order page.
- Only the order holding a product reservation can sell it. If a successful charge arrives AFTER its reservation was released, the payment is recorded as received, the order is flagged for admin review, and no stock or seller earnings are taken from another order. The administrator must investigate and refund via Paystack if appropriate. Automatic refunds are not implemented.
- A buyer can have one active pending checkout. If checkout was interrupted before redirect, check payment status or wait for expiry before starting again; checkout-link resumption is not implemented.

## Pages and permissions

- Cart: `views/buyer/cart.php`
- Checkout: `views/buyer/checkout.php`
- Buyer purchases: `views/buyer/orders.php`
- Seller paid items: `views/seller/orders.php`
- Admin overview: `views/admin/orders.php`
- Details: append `?id=<order_id>` to any of the order pages above.

Changing a URL or form ID does not grant access. Sellers receive only their own items and never another seller's totals. Their fulfilment progression is pending → processing → shipped → delivered and only applies to paid orders. An administrator sees aggregate verified sales, commissions, seller earnings and delivered gross sales. Payout execution and automatic refund operations are outside these tasks.

Order lists currently show the newest 200 records. Commission is rounded to the nearest pesewa per item; seller net is gross minus that rounded commission. Delivery fees are not invented or charged by this checkout.

## Tests and evidence

Automated tests use a disposable database named exactly `thriftedgh_member4_test`, verify the connected database name before modifying fixtures, and must never be pointed at a working team database.

1. Import the full schema into that disposable database (replace its CREATE DATABASE/USE name with the test name).
2. Configure the test process's DB environment to connect to it. Do not let a private credentials file override that connection.
3. Run `php tests/commerce_test.php`. This resets only the named test database and runs the model, provider-contract and concurrent-process checks.
4. Run `php tests/prepare_http.php` against that database to set passwords for the disposable accounts. Their shared test password is `Member4-test-fixture`.
5. Serve the repository root with PHP for local HTTP testing. With the default local test URL, use port 8765 so the application is under `/ThriftedGH/`.
6. Run `COMMERCE_HTTP_TESTS=disposable python3 tests/http_test.py`. `COMMERCE_TEST_URL` can override the default local URL. This suite logs in as `buyer@test.invalid`, `other@test.invalid`, `a@test.invalid` and `admin@test.invalid`.

The database tests exercise mixed-seller totals, duplicate adds/checkouts, unavailable inventory, incorrect payment fields, repeated settlement, commission/net reconciliation, reservation failure/expiry, late charges, buyer/seller/admin access, fulfilment transitions, signed/tampered/replayed webhooks and two simultaneous buyers competing for one unique item. Provider-contract tests use a fake transport; they do not represent a completed Paystack transaction.

Fresh installation and an additive upgrade of the original main schema were both tested in isolated databases. Desktop and 390px mobile cart layouts were visually checked. PHP syntax checks and Git whitespace checks are included in final validation.

## Required final acceptance exercise

1. Use two separate approved sellers and one buyer. Upload one legitimate test product per seller through Task 5.
2. Add both to one cart; repeat an add to confirm quantity stays one. Confirm seller names, images and total.
3. Complete one genuine Paystack TEST payment using Paystack's current documented test instruments.
4. Check the callback AND public webhook delivery. Replay a notification. There must be exactly one paid order, one earnings row per item, correct 3% buyer fee and 7% seller commission and no duplicate deductions.
5. Verify both products disappear from Home, Shop, search and storefronts. Verify buyer history and each seller's isolated order items.
6. Progress one seller's item to delivered and confirm the admin view distinguishes verified sales from delivered sales.
7. Test a failed payment and an abandoned/expired reservation with the worker enabled. Verify stock returns without earnings. Test a late notification and confirm it enters review without double-selling.
8. Re-test seller upload/edit/remove, authentication and mobile views on the actual XAMPP/deployment environment. Save screenshots and provider references as submission evidence without secret keys or customer information.

The real sandbox success/decline exercise and local maintenance have now passed. Public webhook delivery remains deferred by the user; do not mark the hosted webhook acceptance criterion complete until it is tested on public HTTPS.
