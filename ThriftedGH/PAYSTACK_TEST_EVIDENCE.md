# Paystack local sandbox evidence — 17 November 2026

## Successful payment

- Genuine Paystack TEST checkout, completed through the hosted Mobile Money test flow.
- Reference: `TGH-1da93b0b95f7d0741383caab49c4c590`.
- Local order: `2`.
- Currency and amount: **GHS 205.00** (20,500 pesewas).
- One item from each of two approved sellers.
- Callback returned to the local app; server queried Paystack and verified success, exact reference, test domain, amount and currency.
- One paid order and two seller earnings records; repeated verification and locally signed webhook replay created no duplicates.
- Commission: **GHS 20.50** (GHS 8.50 + GHS 12.00).
- Total seller net: **GHS 184.50**.
- Both purchased products became sold with stock zero and disappeared from public catalogue results.
- Both sellers could see only their own item, and advanced it through processing → shipped → delivered. Buyer/admin views distinguish paid from completed fulfilment.

## Declined payment

- Genuine Paystack TEST card checkout using its built-in Declined option.
- Reference: `TGH-775c37d82fc52e65d06d404adace889c`.
- Local order: `3`.
- Amount: **GHS 180.00**.
- Paystack verification reported failed; order remained unpaid, the reserved demo item returned to available/quantity one, and no earnings were created.

## Expiry and access checks

- A dedicated uncharged demo reservation was expired, and the actual maintenance worker released its inventory without recording a sale.
- The local development payment worker is running once per minute for the working XAMPP database. Start it again when restarting the local environment; it is not installed as a system startup service.
- Invalid local webhook signatures returned 401. Valid signed local requests returned 200 and remained idempotent.
- All 74 isolated automated regression checks passed; 24 additional HTTP checks passed against the working XAMPP accounts, including cross-database session isolation. All PHP files passed syntax checks.
- Definite Paystack validation rejection was exercised with a deliberately invalid email. Rejected initialization is distinguished from ambiguous network failures so inventory can be released safely.

## Deferred by user choice

**Actual Paystack-to-server public webhook delivery was NOT tested.** The user chose to keep the site local and defer a temporary public HTTPS endpoint. No tunnel or public webhook registration was created.

The local signed-request tests are not evidence of external webhook delivery. Complete that acceptance step after hosting the receiver on public HTTPS. Configure a persistent maintenance schedule on the hosted server. Hosting/deployment acceptance criteria are also outside this local run.

## Current local settings

- Site: `http://127.0.0.1:8766/ThriftedGH/`.
- Callback supplied automatically during initialization: `http://127.0.0.1:8766/ThriftedGH/payments/callback.php`.
- Demo buyer: `buyer.demo@example.com`. Its password is unchanged from the private demo credential handover.
- Provided keys live only in ignored `core/payment.local.php`. They are excluded from Git commits and source archives. The hosted redirect integration uses the server-side secret; the public key is stored privately for reference and is not needed by this flow.
- No live-mode transaction, transfer or payout was performed.
