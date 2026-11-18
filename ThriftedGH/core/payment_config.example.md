# Payment configuration

Copy `payment.local.example.php` to `payment.local.php` and enter test settings locally, or set the PHP/Apache environment variables `PAYSTACK_SECRET_KEY`, `PAYSTACK_WEBHOOK_SECRET` and `APP_URL`. Environment values take precedence. Private `*.local.php` files are ignored by Git; no automatic .env loading is provided.

See `../MEMBER4_SETUP.md` for migration, Paystack dashboard, reservation worker and final acceptance steps. Never commit a secret or use a live key for this lab.
