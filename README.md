# Kenya.me

Kenya.me helps Kenyan SMEs prepare quotations, create invoices, and reconcile M-Pesa payments in one business-scoped workspace.

## Stack

- Laravel 12 and PHP 8.3+
- PostgreSQL for local development and production
- PHPUnit for feature tests
- SQLite is configured in the ignored local `.env` for quick development without a PostgreSQL service

## MVP workflows

- Business registration, login, logout, and business-scoped records
- Quotations with quantity, unit rates, markup, and discount; convert a quote to an invoice once
- Unique M-Pesa invoice references and printable invoice/quotation PDFs
- Daraja C2B confirmation and validation callbacks, duplicate receipt protection, and audited cascading matching
- Manual payment entry, ambiguous/unmatched exception queue, and manual invoice resolution
- Daily reconciliation dashboard and invoice/payment CSV exports

## Local setup

1. Install PHP 8.3+ with the `pdo_pgsql`, `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, and `tokenizer` extensions, plus Composer. PostgreSQL is required when switching from the local SQLite database to the proposal's production database.
2. Run `composer install`.
3. Copy `.env.example` to `.env`, create a PostgreSQL database named `kenya_me`, and set the database credentials.
4. Run `php artisan key:generate` and `php artisan migrate`.
5. Run `php artisan serve` and open `http://127.0.0.1:8000`.
6. Run `composer test` to execute the feature tests.

For Daraja sandbox use, configure `DARAJA_ENV=sandbox`. URL registration requires a publicly reachable HTTPS address in `DARAJA_CALLBACK_BASE_URL`; localhost callbacks cannot receive Safaricom requests. Add credentials in the owner-only Daraja settings page and register the URLs after the callback service is reachable. Test in Safaricom's sandbox before using production credentials.

The transaction-status polling fallback described in the proposal is not yet wired; the current reconciliation path relies on Daraja callbacks plus the manual exception queue. Do not represent delayed callbacks as confirmed payments.

## Public deployment on Render

The repository includes a Render Blueprint, FrankenPHP container, and managed PostgreSQL configuration. To publish it, push this project to a Git repository, create a new Blueprint in Render from that repository, and review the paid web/database plans and current costs before confirming. Render will provide an HTTPS `onrender.com` address; no custom domain is required.

Set `APP_KEY` as a secret in Render using a fresh value from `php artisan key:generate --show`. Do not reuse the local development key. The Blueprint runs database migrations before deployment and uses database-backed sessions/cache so logins survive container restarts. After deployment, open `/register` to create the owner account.

Daraja starts in sandbox mode. Configure and verify callbacks using the generated HTTPS Render URL before changing `DARAJA_ENV` to `production`. Live payments require Safaricom production credentials and merchant approval.
