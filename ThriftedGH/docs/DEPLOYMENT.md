# Putting ThriftedGH on a live server

You need any host that gives you PHP 8.0 or newer with the `mysqli`, `curl` and `fileinfo` extensions, and a MySQL or MariaDB database. A shared hosting account, a teammate's server or a university server all work.

## Steps

1. Create an empty database and a database user on the host, then import `database/thriftedgh.sql` (it already includes every table, including `seller_documents`).
2. Optional demo data: import `database/storefronts_seed.sql`. Create the admin account with `database/admin_seed.sql` and change its password straight away.
3. Upload the whole `ThriftedGH` folder. It can sit at the site root or inside a subfolder. The app works out its own base path, so no URL needs editing.
4. Create `core/db_cred.php` on the server (copy `core/db_cred.example.php`) with the host's database name, user and password. Do not commit this file.
5. Edit `core/payment.local.php` on the server:
   - `PAYSTACK_SECRET_KEY` stays an `sk_test_...` key. The code refuses live keys.
   - `APP_URL` must be the public address of the site including any subfolder, for example `https://yourdomain.com/thriftedgh`. Paystack sends buyers back to `APP_URL/payments/callback.php`.
6. Make a private folder for uploaded documents that the web cannot reach, for example one level above the web root, and either create `thriftedgh_storage/documents` next to the project folder or set the environment variable `DOC_STORAGE_DIR` to its full path. It must be writable by PHP.
7. In the Paystack dashboard set the test webhook URL to `APP_URL/payments/webhook.php`.
8. Product photos: make sure `images/products/` is writable so sellers can upload photos.
9. Use HTTPS. The session cookie is marked secure automatically when the site is served over HTTPS.

## Check it after upload

- Open `views/public/home.php` and the shop.
- Log in as the admin and open the dashboard.
- Log out and try `views/admin/dashboard.php`. You should land on the login page.
- Register a buyer, add a piece to the cart and run a test payment with a Paystack test card.
- Apply as a seller, upload a PDF or image, and approve it from the admin account.

## If something fails

- Blank page: turn on the host's PHP error log.
- "Uploads are unavailable": the private documents folder is missing or not writable.
- Payment returns to the wrong page: `APP_URL` does not match the real address.
- Images or styles missing: the folder was uploaded without `css/`, `js/`, `images/` or `assets/`.
