# Member 4 validation — 17 November 2026

- 49 database, permission, provider-contract, signature and concurrency checks passed.
- 25 HTTP/form/page checks passed.
- All 66 PHP files passed syntax checks after the Paystack follow-up.
- Git whitespace checks passed.
- Both a fresh schema install and the additive upgrade from original main were verified in separate disposable databases; resulting columns matched.
- Desktop and 390px mobile cart layouts were visually checked.

The initial isolated checks did not change XAMPP data or the older Task 5 project. Subsequent user-authorized demo setup and actual sandbox testing used only the newly created thriftedgh database. Payment-provider contract tests used a fake transport; settlement tests used synthetic verified responses. These are not evidence of an actual Paystack sandbox payment.

Before final acceptance: apply the migration to the backed-up working database, configure private Paystack test credentials and a reachable webhook, enable reservation maintenance, complete the real two-seller sandbox checkout, and record provider/webhook evidence. Deployment and cross-module seller-upload regression testing on the working XAMPP installation remain necessary.

See MEMBER4_SETUP.md for the full acceptance exercise and known limitations.

## Paystack follow-up

Real sandbox success (GHS 205, two sellers), decline (GHS 180), callback verification, local signed webhook replay, expiry cleanup, full seller fulfilment and administrator reconciliation passed. Working XAMPP account HTTP checks passed (24 checks). Public Paystack-to-receiver webhook delivery was deferred at the user’s request. See `../PAYSTACK_TEST_EVIDENCE.md`.
