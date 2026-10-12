# DinkSite — Pickleball Sites and Court Booking

DinkSite is a multi-vendor pickleball marketplace built with Laravel. Customers can discover Sites and Courts, review real-time court availability, make hourly reservations, and register for Open Play sessions. Vendors manage their Sites, Courts, schedules, bookings, payment accounts, and subscriptions, while administrators oversee verification, packages, payments, content, and marketplace operations.

## Application Roles

### Customers

- Browse active Sites and Courts using grid or map views.
- Search by location, category, amenities, rating, date, time, and booking duration.
- View hourly Court availability from its opening time through closing time.
- See vacant, occupied, blocked, and Open Play schedule periods.
- Save Courts to a wishlist and submit reviews.
- Book Courts through the configured online, offline, or vendor e-wallet payment methods.
- Register multiple players for eligible Open Play sessions.
- View booking history, invoices, and payment status from the customer account.

### Vendors and Site Owners

- Register and manage a vendor account.
- Create and maintain Sites and their Courts.
- Configure Court operating hours, hourly prices, amenities, images, and booking settings.
- Add vendor bookings or block unavailable Court periods.
- Schedule multi-hour Open Play sessions with title, category, price per player, maximum capacity, and player details.
- Review bookings, transactions, schedules, rosters, and dashboard activity.
- Configure customer-facing vendor e-wallet accounts and submit their verification documents.
- Purchase or extend subscription packages using supported billing terms.
- Use package-controlled features such as Open Play and Live Court Display when included in the active package.

### Administrators

- Manage customers, vendors, Sites, Courts, bookings, and marketplace content.
- Review Site verification documents and approve or reject Site verification.
- Review vendor e-wallet verification information and supporting documents.
- Configure subscription packages, enabled billing terms, discounts, limits, and feature entitlements.
- Configure payment gateways, including subscription e-wallet instructions and QR codes.
- Review subscription payment logs, customer transactions, and booking activity.
- Manage homepage content, SEO settings, languages, email settings, and other platform configuration.

## Court Availability and Booking Rules

Court cards on the homepage and Courts grid/map views display a compact day-calendar from the Court's configured opening time through closing time. The selected search date is used when present; otherwise, availability is shown for the current date in the configured marketplace timezone.

Availability combines:

- Confirmed or pending bookings that have not been rejected.
- Vendor-created blocked periods.
- Open Play schedule periods.
- Overnight operating hours and 24-hour Courts.

Hourly periods use the same overlap rule as checkout: a reservation occupies a period when it starts before the period ends and ends after the period starts. Availability is recalculated and enforced again during checkout and final booking persistence.

Public availability is displayed only when all of the following are true:

1. The Site has a matching active vendor account.
2. The Site is active and administrator-approved.
3. The vendor has a current subscription package.

When a requirement is missing, the Court card shows the appropriate Site-owner registration, Site verification, or subscription notice instead of exposing the schedule.

## Site Verification

Vendors may save a Site before uploading verification documents, but an unverified Site cannot accept public bookings. Verification supports the Site agreement and private document storage for the business permit, business registration, and mayor's permit. Administrators can securely inspect the submitted files before approving or rejecting the Site.

## Subscription Packages

- Packages use a monthly base price with enabled monthly, semi-annual, or annual purchase terms.
- Administrators can activate terms and configure term-specific discounts.
- The selected billing term, calculated total, start date, and expiration are persisted with the membership.
- Package limits and feature entitlements are enforced in controllers as well as the user interface.
- Free packages bypass payment controls and use the platform's internal free-package defaults.
- Subscription e-wallet payments use administrator-managed offline gateway records and retain payer information separately from internal transaction identifiers.

## Open Play

Vendors can schedule multi-hour Open Play periods for a Court. A session can include a title, category, price per player, maximum number of players, poster, and schedule details. Customers provide the required player information during registration, and the server validates session eligibility and remaining capacity inside a locked transaction before completing the booking.

Open Play is available only when the vendor's current package includes the feature. Expired or deleted sessions return the normal not-found response, while a valid session belonging to an inactive or unverified Site displays an eligibility notice.

## Payments and Transactions

The application retains its established booking and subscription payment integrations. Customer bookings can use configured gateways, offline payments, or a verified vendor e-wallet. Vendor subscription e-wallet payments use the administrator-managed offline gateway workflow. Transaction records preserve booking references, gateway references, payer details, and verification status for reconciliation.

## SEO and Public Routes

The public marketplace includes configurable metadata, localized content, Google-compatible sitemap output, and robots directives. Primary public routes include:

- `/` — Homepage and featured Courts.
- `/sites` — Site listings.
- `/site/{slug}/{id}` — Site details.
- `/courts` — Court grid or map listing.
- `/court/{slug}/{id}` — Court details, schedule, and booking.
- `/pricing` — Vendor subscription packages.
- `/sitemap.xml` — Search-engine sitemap.
- `/robots.txt` — Crawler directives.

Legacy Sports hub and court URLs remain supported through redirects and compatibility mappings.

## Compatibility Architecture

The user-facing domain uses **Sites** and **Courts**, while the established database and integrations retain the legacy `hotels` and `rooms` tables. The `Site` and `Court` model aliases extend the existing `Hotel` and `Room` models. This preserves historical bookings, payments, routes, and third-party integrations while presenting pickleball terminology throughout the application.

## Technical Requirements

- PHP `^8.2`
- Laravel `^9.52.16`
- Composer
- Node.js and npm
- A supported SQL database configured through `.env`

## Local Setup

1. Install PHP dependencies with `composer install`.
2. Copy `.env.example` to `.env` and configure the application URL, database, mail, timezone, and payment settings.
3. Generate the application key with `php artisan key:generate`.
4. Apply the schema with `php artisan migrate`.
5. Install frontend dependencies with `npm install`.
6. Build development assets with `npm run development` or production assets with `npm run production`.
7. Start the application with `php artisan serve`, or configure the project through Apache/XAMPP.

The default database seeder does not create application records. Existing installation data or separately prepared administration records are required to configure the marketplace.

## Useful Validation Commands

```bash
php artisan view:cache
php artisan test --filter=CourtDailyAvailabilityTest
php artisan test --filter=PickleballDomainTest
```

## Laravel Framework Information

The original Laravel documentation is retained below.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
