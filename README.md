# NaturaDala E-Commerce Marketplace

A comprehensive, responsive B2B wholesale marketplace for NaturaDala, a Polish health and wellness product company. This project delivers a professional storefront with advanced features including role-based access, wholesale pricing tiers, retailer partnerships, MFA-protected admin controls, and comprehensive security infrastructure.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Project Structure](#project-structure)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Project Organization](#project-organization)
- [Configuration](#configuration)
- [Database](#database)
- [Development](#development)
- [Security](#security)
- [Admin Functions](#admin-functions)
- [Marketplace Features](#marketplace-features)
- [Production Deployment](#production-deployment)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

This is the **NaturaDala E-Commerce Marketplace**, built to serve as the digital storefront for a Polish wholesale supplier. The initial milestone focuses on a responsive, bilingual (Polish/English) B2B wholesale marketplace with:

- Professional responsiveness across all devices (mobile, tablet, desktop)
- Bilingual interface (Polish/English with session-persistent language switching)
- Secure customer and retailer account management
- Role-based access control (Customer, Retailer, Admin)
- Advanced wholesale pricing tiers with date-based availability
- Retailer onboarding and approval workflow
- MFA-protected admin dashboard
- Comprehensive security event logging
- Newsletter subscription with subscriber discounts
- Inquiry-based wholesale ordering system

---

## Project Structure

```
ecommerce-marketplace/
├── README.md                           # This file
├── NaturadalaMarket/                   # Main application directory
│   ├── MENU_CONSOLIDATION_SUMMARY.md   # Menu UI consolidation notes
│   ├── README.md                       # Project-specific documentation
│   ├── website-showcase.png            # Brand reference imagery
│   └── backend/                        # Laravel application root
│       ├── app/
│       │   ├── Console/                # Artisan commands
│       │   ├── Http/                   # Controllers, middleware, requests
│       │   ├── Mail/                   # Mailable classes
│       │   ├── Models/                 # Eloquent models
│       │   └── Providers/              # Service providers
│       ├── config/                     # Configuration files
│       ├── database/
│       │   ├── migrations/             # Schema migrations
│       │   ├── factories/              # Model factories
│       │   └── seeders/                # Database seeders
│       ├── resources/
│       │   ├── views/                  # Blade templates
│       │   ├── css/                    # Stylesheets
│       │   └── js/                     # JavaScript assets
│       ├── public/                     # Public assets & web root
│       ├── tests/                      # Feature and unit tests
│       ├── .env.example                # Environment template
│       ├── .env                        # Local environment (not committed)
│       ├── composer.json               # PHP dependencies
│       ├── package.json                # Node dependencies
│       └── artisan                     # Laravel CLI tool
└── .git/                               # Git version control
```

---

## Key Features

### 🛍️ Storefront
- **Responsive Design**: Mobile-first layouts with breakpoints at 680px, 760px, and 980px
- **Bilingual Support**: Polish/English interface with persistent language selection
- **Product Showcase**: Full product catalog with descriptions, nutrition information, and imagery
- **Shopping Cart**: Browser-persisted inquiry list with quantity controls
- **Inquiry System**: Submit wholesale inquiries with contact details for admin follow-up

### 👥 Account Management
- **Role-Based Access**: Customer, Retailer, and Admin roles with distinct permissions
- **Account Registration**: Email verification, password reset with security event logging
- **Email Management**: Secure email change with password confirmation
- **Password Security**: Password change with session rotation and security logging
- **Device Management**: Sign out from other active sessions with password confirmation

### 🔐 Security
- **Two-Factor Authentication (MFA)**: Optional authenticator-app TOTP with recovery codes
- **Session Binding**: Sessions bound to password hash; password change revokes other devices
- **Security Event Logging**: Comprehensive audit trail of all security-related actions
- **Rate Limiting**: Protection against brute-force attacks on sensitive endpoints
- **CSRF Protection**: Built-in Laravel CSRF token protection
- **Password Reset Security**: Generic responses prevent account enumeration

### 💼 Wholesale Features
- **Retailer Onboarding**: Formal business application process with admin approval
- **Wholesale Pricing Tiers**: Date-based, quantity-based pricing for approved retailers
- **Partner Workspace**: Separate interface for verified, approved retailers
- **Contract Metadata**: Admin-managed contract records (reference, type, dates, status)
- **Inquiry Management**: Admin dashboard with pending inquiry queue

### 📧 Marketing
- **Newsletter System**: Double opt-in subscription with signed confirm/unsubscribe links
- **Subscriber Discounts**: Automatic discount applied to verified newsletter subscribers
- **New Product Notifications**: Queued emails when products are published
- **Consent Management**: GDPR-compliant cookie consent with category selection

### 🎨 User Experience
- **Animated Elements**: Shimmering text effects, hero animations, scroll-reveal imagery
- **Motion Preferences**: Respects user's "reduce motion" accessibility setting
- **Cookie Consent**: Accept All / Reject Optional / Choose Categories workflow
- **Announcement Bar**: Rotating promotions/announcements (sortable, pausable)
- **Persistent Preferences**: Language and consent stored across sessions

---

## Technology Stack

### Backend
- **Framework**: Laravel 13 (PHP)
- **Database**: MySQL
- **Authentication**: Laravel built-in with custom extensions
- **MFA**: TOTP-based authenticator apps (Laravel-Totp integration)
- **Job Queue**: Laravel Queue (supports multiple drivers)
- **Testing**: PHPUnit with feature tests

### Frontend
- **Template Engine**: Blade (Laravel)
- **CSS**: Custom responsive design (CSS Grid, Flexbox)
- **JavaScript**: Vanilla JS, no framework (lightweight)
- **Build Tool**: Vite (optional, configured)
- **Accessibility**: WCAG-compliant with motion preferences support

### Development Tools
- **Composer**: PHP dependency manager
- **NPM**: Node package manager
- **Artisan CLI**: Laravel command-line interface
- **Git**: Version control

---

## Prerequisites

- **PHP 8.3+** (Laravel 13 requirement)
- **Composer**: Latest version
- **MySQL 8.0+** or compatible
- **Node.js 18+** (for frontend build tools)
- **NPM 9+** or **Yarn**

### Local Development Setup
- **Laragon**: Recommended for Windows development (includes PHP, MySQL, Composer)
- **Valet**: Recommended for macOS
- **Docker**: Optional containerization

---

## Quick Start

### 1. Clone the Repository
```bash
git clone https://github.com/KarolAtkinson/ecommerce-marketplace.git
cd ecommerce-marketplace
```

### 2. Install Dependencies
```bash
cd NaturadalaMarket/backend
composer install
npm install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` file with:
- Database credentials
- Mail settings (optional for local development)
- Inquiry notification email (optional)

### 4. Database Setup
```bash
# Create database schema
php artisan migrate

# (Optional) Seed products
php artisan db:seed --class=ProductSeeder --force
```

### 5. Start Development Server
```bash
# Terminal 1: Laravel dev server
php artisan serve

# Terminal 2: Queue worker (if using queued jobs)
php artisan queue:work

# Terminal 3: Frontend assets (if using Vite)
npm run dev
```

Access the application at `http://localhost:8000`

---

## Project Organization

### Configuration Files
- **`.env.example`**: Template for environment variables (commit this, not `.env`)
- **`.gitignore`**: Excludes node_modules, .env, vendor, storage, and cache
- **`.editorconfig`**: Enforces consistent code formatting

### Key Application Directories

#### `app/Console`
Custom Artisan commands, including:
- `marketplace:provision-admin` - Create the first administrator account

#### `app/Http`
- **Controllers**: Application request handlers organized by feature
- **Middleware**: Authentication, authorization, CORS, and custom logic
- **Requests**: Form validation rules (FormRequest classes)

#### `app/Models`
Eloquent ORM models:
- `User` - Base user model with roles and MFA
- `Product` - Catalog products with pricing and stock
- `WholesalePricingTier` - Quantity-based pricing rules
- `RetailerApplication` - Business onboarding records
- `RetailerCompany` - Approved company accounts
- `Inquiry` - Wholesale inquiry submissions
- `Address` - Customer billing/shipping addresses
- `SecurityEvent` - Audit trail for security actions
- And more...

#### `config/`
Application configuration:
- `app.php` - Application settings
- `auth.php` - Authentication configuration
- `database.php` - Database connection settings
- `announcements.php` - Announcement bar messages (edit here to change promotions)
- `features.php` - Feature flags (retailer maintenance mode, etc.)

#### `database/`
- **migrations/**: Schema change files (run with `php artisan migrate`)
- **seeders/**: Data population scripts (run with `php artisan db:seed`)
- **factories/**: Model factories for testing

#### `resources/views/`
Blade template directory:
- **layouts/**: Base page layouts
- **auth/**: Login, registration, password reset pages
- **account/**: Customer account pages
- **admin/**: Admin dashboard and management interfaces
- **storefront/**: Public-facing product and landing pages
- **legal/**: Terms and privacy pages (incomplete, needs legal review)
- **shared/**: Reusable components (basket panel, cookie consent, etc.)

#### `public/`
Web-accessible directory:
- **images/**: Brand imagery, product images
- **css/**: Compiled stylesheets
- **js/**: Frontend JavaScript (including translations)
- **en-translations.js**: English UI text (edit to update English copy)

#### `tests/`
Automated test suite:
- **Feature/**: End-to-end tests
- **Unit/**: Individual component tests
- Uses SQLite in-memory database (doesn't affect production)

---

## Configuration

### Environment Variables (`.env`)

**Essential:**
```env
APP_NAME=NaturaDala
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=naturadala
DB_USERNAME=root
DB_PASSWORD=
```

**Optional (Mail/Notifications):**
```env
INQUIRY_NOTIFICATION_EMAIL=team@example.com
MAIL_MAILER=log  # Use 'log' for local development
```

**Feature Flags:**
```env
MARKETPLACE_REGISTRATION_ENABLED=false  # Set true when legal pages are ready
RETAILER_MAINTENANCE=true               # Set false to open retailer workspace
LEGAL_REVIEW_NOTICE=false               # Hide review banner when false
```

**Marketplace Terms (required before launch):**
```env
MARKETPLACE_TERMS_VERSION=1.0           # Must not start with "draft-"
MARKETPLACE_PRIVACY_VERSION=1.0         # Must not start with "draft-"
```

**Newsletter (optional):**
```env
SUBSCRIBER_DISCOUNT_PERCENT=10          # Discount for verified subscribers
```

---

## Database

### Key Tables

**Users & Authentication**
- `users` - Accounts with roles, MFA status, and security flags
- `mfa_secrets` - Encrypted TOTP secrets
- `security_events` - Audit trail of password changes, MFA updates, etc.

**Products & Pricing**
- `products` - Catalog with SKU, pricing, descriptions
- `wholesale_pricing_tiers` - Quantity/date-based pricing for retailers
- `product_wishlist` - Customer favorites

**Wholesale & Retailers**
- `retailer_applications` - Pending business applications
- `retailer_companies` - Approved company accounts
- `retailer_company_wholesale_prices` - Retailer-specific pricing
- `contracts` - Contract metadata records
- `addresses` - Billing/shipping addresses

**Orders & Inquiries**
- `inquiries` - Wholesale inquiry submissions with status tracking
- `inquiry_items` - Line items within inquiries

**Marketing**
- `newsletter_subscribers` - Subscription records
- `newsletter_subscriptions_log` - Confirm/unsubscribe history

---

## Development

### Running Tests
```bash
php artisan test
```

Tests use an isolated SQLite database and don't affect your local MySQL setup.

### Database Migrations
```bash
# Create new migration
php artisan make:migration create_table_name

# Run pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Reset all (caution: data loss)
php artisan migrate:reset
php artisan migrate:fresh
```

### Creating an Admin Account
```bash
php artisan marketplace:provision-admin admin@example.com
# Follow interactive prompts for password
```

**Admin cannot access `/admin/*` until MFA is enabled on first login.**

### Frontend Development
If using Vite for frontend build:
```bash
npm run dev    # Development with hot reload
npm run build  # Production build
```

---

## Security

### Authentication & Authorization
- **Login**: Email + password verification with optional MFA challenge
- **Email Verification**: New accounts must verify email before access
- **Password Reset**: Secure token-based reset with generic responses
- **MFA Recovery Codes**: One-use, hashed recovery codes for account recovery

### Session Security
- **Session Binding**: Sessions are bound to the user's current password hash
- **Password Change**: Changing password invalidates all other sessions
- **Email Change**: Requires password confirmation; invalidates sessions on other devices
- **Sign Out Other Sessions**: Rate-limited ability to terminate other device sessions

### Admin Security
- **MFA Required**: Admin accounts require MFA for access to `/admin/*`
- **Role Enforcement**: Fine-grained permission checks on all admin routes
- **Audit Logging**: All admin actions logged for compliance

### Data Protection
- **CSRF Protection**: All forms include CSRF tokens
- **SQL Injection Prevention**: Eloquent ORM prevents injection attacks
- **XSS Prevention**: Blade templates auto-escape output
- **Rate Limiting**: Protection on login, password reset, inquiry submission
- **GDPR Compliance**: Cookie consent system, newsletter opt-in

---

## Admin Functions

Accessible at `/admin/*` (MFA required):

### Dashboard (`/admin`)
- Quick queue of recent unanswered wholesale inquiries
- System status and feature flags

### Retailer Management
- **Applications** (`/admin/retailers`): Review pending business applications
- **Directory** (`/admin/retailer-directory`): Search, suspend, or reactivate approved retailers
- **Revoke Access**: Remove wholesale pricing visibility without deleting history

### Contract Management (`/admin/retailer-contracts`)
- Add/edit/delete contract metadata records
- Track contract reference, type, dates, status, and notice period
- Note: Does not store agreement files or legal documents

### Catalog Management
- Search products by name, SKU, or slug
- Filter by active status
- Add wholesale pricing tiers (must meet product MOQ)
- Pricing tiers use unique quantity thresholds and date ranges

---

## Marketplace Features

### For Customers
- Browse public product catalog
- Create account and verify email
- Manage billing/shipping addresses
- Add products to inquiry list (browser-persisted)
- Submit wholesale inquiries for admin follow-up
- Subscribe to newsletter (double opt-in)
- View security event history

### For Retailers
- Apply for wholesale access
- Review application status
- Browse partner workspace (after approval)
- Access wholesale pricing tiers
- Special partner pricing not visible to public customers
- Submit inquiries with retail quantities

### For Admins
- Review and approve/reject retailer applications
- Manage wholesale pricing tiers
- Maintain contract metadata
- Respond to wholesale inquiries
- Monitor security events
- Manage newsletter subscribers
- Provision new admin accounts via CLI

---

## Production Deployment

### Pre-Launch Checklist

- [ ] Update legal pages in `backend/resources/views/legal/terms.blade.php` and `privacy.blade.php`
- [ ] Set `MARKETPLACE_TERMS_VERSION` and `MARKETPLACE_PRIVACY_VERSION` to non-draft values
- [ ] Review and test newsletter system with real email configuration
- [ ] Configure `INQUIRY_NOTIFICATION_EMAIL` with team email
- [ ] Enable `MARKETPLACE_REGISTRATION_ENABLED=true` when ready
- [ ] Test payment gateway (not yet implemented)
- [ ] Review all security settings in `.env`
- [ ] Run production migrations on target database

### Hosting Configuration

**Target Domain**: `naturadala.com.pl`

**Current DNS Setup**:
- NS1: `ns11.rgb365.eu`
- NS2: `ns12.rgb365.eu`
- NS3: `ns13.rgb365.eu`

**Hosting Platform**: UTI.PL (not yet configured)

**Before connecting to UTI.PL, confirm:**
- PHP 8.3+ and Laravel 13 compatibility
- MySQL 8.0+ database access
- HTTPS/SSL certificate support
- Mail provider configuration (SMTP)
- Outbound email capability
- Proper file permissions and directory structure

### Deployment Steps

1. **Clone to production server**
   ```bash
   git clone https://github.com/KarolAtkinson/ecommerce-marketplace.git
   cd NaturadalaMarket/backend
   ```

2. **Install dependencies**
   ```bash
   composer install --no-dev
   npm ci
   ```

3. **Configure production .env**
   - Database credentials
   - Mail provider settings
   - Feature flags (enable registration, disable maintenance mode)
   - App key (generate with `php artisan key:generate`)

4. **Database setup**
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=ProductSeeder --force
   ```

5. **Create admin account**
   ```bash
   php artisan marketplace:provision-admin admin@production.com
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Configure web server** (nginx/Apache)
   - Point to `public/` as document root
   - Enable mod_rewrite for pretty URLs
   - Set appropriate permissions on `storage/` and `bootstrap/cache/`

8. **Queue worker** (optional, for newsletters)
   ```bash
   php artisan queue:work --daemon
   ```

---

## Contributing

This project is currently maintained by Karol Atkinson. For contributions:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes following existing code style
4. Write tests for new features
5. Commit with clear messages (include Co-authored-by trailer)
6. Push to the branch
7. Create a Pull Request

---

## License

This project is proprietary software for NaturaDala. All rights reserved.

---

## Project References

- **NaturaDala Brand**: Polish health and wellness product company
- **Product Focus**: Wholesale B2B marketplace
- **Legal Templates**: Creativa Legal PRO (physical goods)
- **Contract Reference**: Salesforce Account fields structure

---

## Support & Maintenance

For issues, feature requests, or maintenance needs, contact the development team.

**Last Updated**: November 19, 2026
**Current Version**: 1.0.0 (In Development)
**Status**: Active Development (Pre-Launch)
