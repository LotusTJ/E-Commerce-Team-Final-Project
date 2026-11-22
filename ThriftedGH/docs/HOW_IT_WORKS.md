# How ThriftedGH fits together

ThriftedGH follows the MVC pattern. Every page is its own file, every form posts to its own action file, and each folder has one job.

## Folders

- `views/` is the View. It only shows things. Each page is one file, grouped by who may open it.
  - `views/public/` home, shop, product, sellers, storefront (anyone)
  - `views/auth/` login, register
  - `views/buyer/` account, cart, checkout, orders, payment result, find my fit (logged in)
  - `views/seller/` apply, documents, dashboard, product form, sales (approved sellers; apply and documents for applicants)
  - `views/admin/` dashboard, applications, orders (admins only)
  - `views/layout/` header and footer shared by every page
  - `views/components/` small reusable pieces (product card, order status helpers)
- `actions/` is where forms post to. One file does one thing, then redirects. Grouped by area: `auth`, `cart`, `checkout`, `orders`, `products`, `fit`, `seller`, `admin`, `documents`.
- `controllers/` is the Controller logic that actions and views call: validation, rules and "what should happen". `Product.php`, `Seller.php`, `Fit.php`, `Cart.php`, `Order.php`, `Document.php`.
- `classes/` is the Model. These are the only files that talk to the database: `UserClass`, `SellerClass`, `ProductClass`, `CatalogueRepository`, `CartClass`, `OrderClass`, `CommerceModel`, `FitClass`, `DocumentClass`, `AdminClass`.
- `core/` is shared plumbing: database connection (`db_class.php`, `db_config.php`), session (`session.php`), login helpers (`auth.php`), the role guard (`guard.php`), small helpers (`helpers.php`), URL builder (`paths.php`) and the SVG icon helper (`icons.php`).
- `payments/` talks to Paystack: `PaystackService.php`, the `callback.php` the buyer returns to, and the `webhook.php` Paystack calls.
- `assets/icons/` the SVG icons. `css/` and `js/` are the styles and scripts. `images/` holds product photos.
- `database/` SQL files. `scripts/` command line jobs. `tests/` automated checks. `docs/` these notes.

## One request, start to finish

Adding a piece to the cart:

1. The buyer clicks "Add to cart" on `views/public/shop.php`. The product card is drawn by `views/components/product_card.php`.
2. The button submits a form to `actions/cart/add.php`.
3. The action loads `controllers/Cart.php`, which loads `core/guard.php`. The guard checks the form is a POST, the CSRF token is valid, and the user is logged in.
4. The action calls `CartClass->add()` in `classes/CartClass.php`, which writes to the `cart_items` table.
5. The action sets a flash message and redirects to `views/buyer/cart.php`, which asks `CartClass` for the items and draws the page inside `views/layout/header.php` and `footer.php`.

Every action follows the same shape: guard, validate, call the model, flash message, redirect.

## Who calls who

- Pages in `views/` include `views/layout/header.php`, which asks `core/auth.php` who is logged in and shows the right menu.
- Pages and actions call `core/guard.php` first. Nothing else decides who may enter.
- `controllers/*` call `classes/*`. Only `classes/*` and `core/db_class.php` touch MySQL.
- `core/helpers.php` provides `tgh_url()`, `tgh_e()`, flash messages and CSRF to everyone.
- `actions/checkout/start.php` calls `payments/PaystackService.php` to start a payment. Paystack sends the buyer to `payments/callback.php`, which verifies the payment with Paystack and redirects to `views/buyer/payment_result.php`.

## Access rules

- Every protected page and action starts with a guard from `core/guard.php`: `guardRequireLogin()`, `guardRequireSeller()` or `guardRequireAdmin()`.
- The role is read from the database on every request. It is never trusted from the session or from a form field, so changing a cookie or URL cannot make someone an admin.
- Logged-out visitors are sent to the login page. Logged-in users without the right role get a 403 page. Actions answer 403 as plain text.
- Admin pages: only `users.role = 'admin'`. Seller pages: only approved sellers. Admins cannot use seller tools and sellers cannot open admin pages.

## Document uploads

Applicants upload verification documents (student ID, national ID, business registration) at `views/seller/documents.php`.

- `actions/documents/upload.php` checks the file type from its real contents (PDF, JPG, PNG only), a 5 MB limit, five files per application, and the CSRF token.
- Files are saved under random names in `thriftedgh_storage/documents`, a folder next to the project folder and outside the web root. Set `DOC_STORAGE_DIR` to choose another private folder.
- `actions/documents/download.php` is the only way to read a file. It allows the owner and admins and nobody else.
- Admins see each applicant's documents on `views/admin/applications.php` before approving.
- The database table is `seller_documents` (`database/migrations/009_documents.sql`).

## Handy rules for the team

- Adding a page: create the file in the right `views/` folder, start it with a guard if it is not public.
- Adding a form: create a new file in `actions/<area>/`, post to it, redirect back.
- Never write SQL in `views/` or `actions/`. Put it in `classes/`.
- Never print user data without `tgh_e()`.
- Never hard code `/thriftedgh/`. Use `tgh_url('views/public/shop.php')`.
