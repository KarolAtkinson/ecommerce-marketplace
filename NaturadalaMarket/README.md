# NaturaDala Marketplace

The first milestone is a responsive Polish/English wholesale storefront, using public NaturaDala brand imagery and an editorial B2B layout inspired by the wholesale reference page. The application lives in `backend/` and uses Laravel 13.

## Run locally with Laragon

1. Start Laragon's web server.
2. In a terminal, enter `backend/`.
3. Run `php artisan serve`.
4. Open the local URL printed by Artisan.

The storefront inquiry list can add products, change quantities, remove them, and save its contents in the current browser. Sending an inquiry submits the selected products and contact details to the site's inquiry inbox for admin follow-up; this is not an order or payment checkout.

## Current scope

- Responsive landing page and mobile navigation. All layouts use CSS breakpoints (980, 760 and 680px) so pages adapt to phones, laptops and desktops; wide tables scroll inside their own container and two-column forms stack on phones.
- Homepage features the poultry broth nutrition table, a "For Who" audience section and a Q&A section; preparation, storage and warning details appear below the poultry broth product image.
- Announcement bar slideshow (20-second delay, pausable, paused on hover/focus). Its messages live in `backend/config/announcements.php` and are sorted A-Z automatically; add or edit entries there. English text for new messages goes in `backend/public/en-translations.js`.
- Basket button in the header, right next to "Oferta" on the storefront and product pages. It opens a centred popup (`shared/basket-panel.blade.php`) with the visitor's enquiry list (stored in the browser with consent) and, for verified signed-in users, the items saved in their account basket, with a link to the full basket page.
- Cookie consent popup (`shared/cookie-consent.blade.php`, `public/cookie-consent.js`): Accept all / Reject optional / Choose categories (necessary, preferences, analytics, marketing). The choice is stored in the first-party `naturadala_consent` cookie for 180 days and can be reopened from the "Ustawienia cookies" footer button. Today only "preferences" has an effect (it gates saving the enquiry list in localStorage); gate any future analytics or marketing scripts on `window.naturadalaConsent.has('analytics' | 'marketing')`. The popup text is a functional draft and needs review together with the legal pages before launch.
- Animated headings and imagery: shimmering accent words, hero entrance and floating product image, scroll-reveal for below-the-fold headings and images. All motion is disabled for visitors with "reduce motion" enabled.
- Session-persistent Polish/English switch beside the brand logo, with English translations across storefront, account, and admin interfaces.
- NaturaDala product/story content and public brand images.
- Browser-persisted inquiry list with quantity controls.
- Laravel-served storefront with a feature test.
- Marketplace account foundation: customer/retailer/admin roles, pending retailer profiles, addresses, versioned consent records, security-event logging, and account status fields.
- Laravel account routes for login/logout, verified-email-gated account pages, verification email, and password reset.
- Password reset requests use the same generic response for active and inactive accounts; reset emails are normalized consistently, and successful resets rotate remembered credentials and are recorded in security history.
- Optional authenticator-app MFA with encrypted TOTP secrets, one-use hashed recovery codes, replay protection, and security-event logging.
- Verified account holders can rotate MFA recovery codes after confirming their password and a fresh authenticator code; replaced codes stop working immediately.
- Verified account holders can manage saved billing/shipping addresses with separate defaults and ownership-protected edit/delete routes.
- Verified account holders can update their name and phone number, or change their email after confirming their current password; email changes invalidate verification until the new address is confirmed.
- Verified account holders can change their password after confirming the current password; remembered credentials are rotated, other sessions are revoked, and the change is recorded as a security event.
- Authenticated sessions are bound to the current password hash, so changing an account password or email revokes sessions on other devices.
- Pending two-factor sign-in challenges are bound to the account's current password and email, so credential changes invalidate challenges that have not completed yet.
- Verified account holders can sign out other active sessions after confirming their current password; the action is rate-limited and recorded in their security history.
- Verified account holders can review a paginated history of their own security events without exposing IP addresses or event metadata.
- Database-backed product listing with paginated search/sort, result counts, clear-filter empty states, stock-aware minimum-order messaging, product details, saved basket, and wishlist with wishlist-to-basket support and recovery/removal for products later deactivated from the catalogue.
- Admin wholesale pricing tiers must meet the product minimum order quantity and use unique quantity thresholds.
- Admins can add and remove multiple wholesale pricing tiers; edits only accept tier records belonging to the selected product.
- Blank wholesale tier prices remain optional and do not participate in MOQ or duplicate-threshold checks.
- Wholesale tier dates cover the full selected calendar days, and end dates cannot precede start dates.
- Products saved in customer baskets or wishlists cannot be hard-deleted from the admin catalogue; deactivate them to preserve customer entries.
- Admin catalogue supports search by product name, SKU, or slug, active-status filtering, and pagination.
- Retailer business applications, admin/MFA-protected approvals, and server-enforced wholesale-price visibility.
- Admin retailer review inbox supports searching pending applications by business, tax identifier, applicant name, or email.
- Approved, active, verified retailers have a separate database-style partner workspace; public visitors and customer accounts continue to use the public catalogue.
- Admins can search the retailer directory, suspend or reactivate approved company accounts, and revoke wholesale access without deleting the account history.
- MFA-protected admins can maintain contract metadata records (reference, title, type, owner, dates, status, and notice period). The module stores no contract documents or legal wording; the on-screen sample is fictional and cites [Salesforce Account fields](https://developer.salesforce.com/docs/atlas.en-us.object_reference.meta/object_reference/sforce_api_objects_account.htm) and [SpotDraft contract metadata reporting](https://www.spotdraft.com/blog/contract-metadata-reporting).
- Registration UI and customer/retailer validation are implemented but deliberately disabled until marketplace terms and privacy notices have been approved and published.
- One-time CLI administrator provisioning without a hardcoded account or source-code password.
- Database-backed wholesale inquiry submission with rate limiting and admin follow-up, including inquiry prefill from a signed-in user's saved basket; payment and order processing are not implemented yet.
- New inquiry email notifications can be enabled by setting `INQUIRY_NOTIFICATION_EMAIL` to the team's recipient and configuring Laravel's mailer; inquiries continue to be stored in the admin inbox.
- Admin dashboard includes a short queue of recent unanswered wholesale inquiries for follow-up.
- Legal pages: `/terms` and `/privacy` are built from the Creativa Legal PRO (physical goods) templates in `resources/views/legal/`. Highlighted `[uzupełnić: …]` marks are fields still to be completed, and `LEGAL_REVIEW_NOTICE=false` hides the review banner. Registration opens when `MARKETPLACE_REGISTRATION_ENABLED=true` and both `MARKETPLACE_*_VERSION` values do not start with `draft-`.
- Retailer area maintenance: `RETAILER_MAINTENANCE=true` (default, see `config/features.php`) returns a 503 maintenance page for the retailer workspace and application for everyone except admins, and hides partner prices and wholesale links. Set it to `false` to reopen.
- The collagen product retail price is 21.99 PLN (set by the migration/seeder). Payment and checkout are not implemented yet.
- Newsletter: double opt-in subscription form (`#newsletter`), signed confirm/unsubscribe links, and queued new-product emails sent when an active product is first published. Run `php artisan queue:work` to deliver them. Verified accounts whose email matches an active subscriber see a `SUBSCRIBER_DISCOUNT_PERCENT` (default 10) discount. Consent and email wording are drafts pending legal review.

## Local database and deployment

Run tests with `php artisan test` from `backend/`. Apply new additive local schema changes with `php artisan migrate` from `backend/`.

Local database and mail settings live in the ignored `backend/.env`; passwords and other secrets must stay in that local file and should never be committed or sent in chat. Set `INQUIRY_NOTIFICATION_EMAIL` only after choosing the team's notification address; use `MAIL_MAILER=log` for local-only development if desired. Automated tests use an isolated SQLite database and do not modify the local MySQL database.

The migrations create an initially empty catalogue and contract register. Run `php artisan db:seed --class=ProductSeeder --force` from `backend/` to add the verified Naturadała concentrate record. Prices and stock are intentionally left unset until the business confirms them. Approved retailer price records and the partner workspace are served only to active, verified accounts with both an approved retailer profile and the retailer role. Pending retailers can review their application in their account; approval is required before the partner workspace opens.

Admins review applications at `/admin/retailers`, manage existing company access in `/admin/retailer-directory`, and maintain contract metadata at `/admin/retailer-contracts`. Removing wholesale access retains the retailer profile and audit history rather than deleting a business record. Contract records contain metadata only, not the agreement file or legal wording.

Before opening registration, add reviewed marketplace terms and privacy views in `backend/resources/views/legal/terms.blade.php` and `backend/resources/views/legal/privacy.blade.php`, set non-draft document versions, and explicitly enable `MARKETPLACE_REGISTRATION_ENABLED`. The app also checks that both legal views exist before allowing registration. UTI.PL hosting has not been configured or contacted. Confirm its PHP/Laravel, database, HTTPS, email, and deployment support before planning a production connection.

## Production domain and DNS

The target production domain has been set as `naturadala.com.pl` and the current DNS assignment is:

- NS1: `ns11.rgb365.eu`
- NS2: `ns12.rgb365.eu`
- NS3: `ns13.rgb365.eu`

Do not connect the application to UTI.PL or production DNS until the user provides the platform access, account credentials, and exact hosting setup instructions. Once the access is available, the next deployment steps are to confirm Laravel/PHP version compatibility, MySQL connectivity, mail provider configuration, and HTTPS certificate setup on the UTI.PL hosting account.

To create the first local administrator, run `php artisan marketplace:provision-admin admin@example.com` from `backend/` in an interactive Laragon terminal, follow the prompts, and use a unique strong password from a password manager. The command marks that administrator email as verified and assigns the admin role; the protected admin area remains inaccessible until the administrator signs in and enables authenticator-app MFA. Review retailer applications at `/admin/retailers`. Do not create administrator accounts by editing a seeder or committing credentials.
