# SNACKZAR — Project Implementation Document

**Domain:** https://snackzar.com
**Description:** Enterprise-grade ecommerce marketplace for Bihari snacks (Makhana & more)
**Framework:** Laravel 12.54.0 / PHP 8.5

---

## Architecture Overview

### Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.3+ |
| Frontend | Vue 3, Inertia.js v2, TailwindCSS 4 |
| Database | MySQL |
| Cache/Queue | Redis |
| Realtime | Laravel Reverb |
| Authentication | Laravel Sanctum, Google OAuth, Twilio OTP |
| Media | ImageKit CDN |
| Shipping | Shiprocket API |
| Maps | MapMyIndia API |
| API Docs | Laravel Scribe |
| Testing | Pest PHP |
| Queue Monitoring | Laravel Horizon |

### Design Patterns

- **Modular Architecture** — Code organized into domain modules
- **Service Layer Pattern** — Business logic encapsulated in services
- **Repository Pattern** — Database access abstracted via repositories
- **DTO Pattern** — Data transfer objects for type-safe data flow
- **SOLID Principles** — Single responsibility, open/closed, etc.

---

## Project Structure

```
app/
├── Http/
│   └── Middleware/
│       └── HandleInertiaRequests.php
├── Models/
├── Modules/
│   ├── Admin/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── DTOs/
│   │   ├── Requests/
│   │   ├── Policies/
│   │   ├── Events/
│   │   ├── Listeners/
│   │   ├── routes/ (web.php, api.php)
│   │   ├── database/migrations/
│   │   └── AdminServiceProvider.php
│   ├── User/
│   │   ├── (same structure)
│   │   └── UserServiceProvider.php
│   ├── Seller/
│   │   ├── (same structure)
│   │   └── SellerServiceProvider.php
│   ├── Delivery/
│   │   ├── (same structure)
│   │   └── DeliveryServiceProvider.php
│   └── Shared/
│       ├── Contracts/
│       │   └── RepositoryInterface.php
│       ├── Repositories/
│       │   └── BaseRepository.php
│       ├── DTOs/
│       │   └── BaseDTO.php
│       └── Traits/
│           └── ApiResponse.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── ModuleServiceProvider.php
```

---

## Panels

| Panel | Prefix | Role |
|-------|--------|------|
| Admin | `/admin` | admin |
| User | `/` | user |
| Seller | `/seller` | seller |
| Delivery | `/delivery` | delivery_partner |

---

## API Structure

All API endpoints are prefixed with `/api/v1/` and organized by module:

- `/api/v1/admin/*` — Admin endpoints (requires `admin` role)
- `/api/v1/user/*` — User endpoints (requires authentication)
- `/api/v1/seller/*` — Seller endpoints (requires `seller` role)
- `/api/v1/delivery/*` — Delivery endpoints (requires `delivery_partner` role)

Authentication via Laravel Sanctum (Bearer token).

---

## Installed Packages

### Production
- `inertiajs/inertia-laravel` ^2.0
- `laravel/sanctum` ^4.3
- `spatie/laravel-permission` ^7.2
- `laravel/horizon` ^5.45
- `laravel/reverb` ^1.8

### Development
- `pestphp/pest` ^3.8
- `pestphp/pest-plugin-laravel` ^3.2
- `knuckleswtf/scribe` ^5.8

### Frontend
- `vue` ^3.5
- `@inertiajs/vue3` ^2.3
- `@vitejs/plugin-vue` ^6.0
- `tailwindcss` ^4.0

---

## Configuration

### Environment Variables

All configuration is driven via `.env`. Key sections:

- **Database:** MySQL (DB_CONNECTION=mysql)
- **Cache/Session/Queue:** Redis
- **Broadcasting:** Laravel Reverb
- **Mail:** SMTP
- **Third-party:** Google OAuth, Twilio, ImageKit, Shiprocket, MapMyIndia

See `.env.example` for the full template.

### Custom Config

- `config/snackzar.php` — Application-specific settings (OTP, pagination, third-party keys)
- `config/sanctum.php` — API authentication
- `config/horizon.php` — Queue monitoring
- `config/permission.php` — Spatie roles/permissions

---

## Testing

**Framework:** Pest PHP
**Database:** SQLite in-memory (for tests)

Run tests:
```bash
./vendor/bin/pest
```

### Test Coverage (Phase 1)

| Test Suite | Tests | Assertions |
|-----------|-------|------------|
| WelcomePageTest | 3 | 3 |
| ConfigurationTest | 4 | 7 |
| ModuleArchitectureTest | 3 | 7 |
| ApiResponseTraitTest | 1 | 17 |
| **Total** | **11** | **34** |

All tests passing ✅

---

## Implementation Phases

| Phase | Description | Status |
|-------|------------|--------|
| 1 | Project Initialization | ✅ Complete |
| 2 | Authentication System | ⬜ Pending |
| 3 | Role Based Access Control | ⬜ Pending |
| 4 | User Panel | ⬜ Pending |
| 5 | Product Catalog | ⬜ Pending |
| 6 | Order System | ⬜ Pending |
| 7 | Seller Panel | ⬜ Pending |
| 8 | Delivery Partner Panel | ⬜ Pending |
| 9 | Shipping Integration | ⬜ Pending |
| 10 | SMS & Email | ⬜ Pending |
| 11 | Image System | ⬜ Pending |
| 12 | Realtime Events | ⬜ Pending |
| 13 | SEO System | ⬜ Pending |
| 14 | Blog System | ⬜ Pending |
| 15 | API Documentation | ⬜ Pending |
| 16 | Testing (80%+ coverage) | ⬜ Pending |
| 17 | Performance Optimization | ⬜ Pending |
| 18 | Project Documentation | ⬜ Pending |
| 19 | CI/CD Ready | ⬜ Pending |

---

## Deployment Guide

### Prerequisites
- PHP 8.3+
- MySQL 8.0+
- Redis 7+
- Node.js 20+
- Composer 2.7+

### Steps
```bash
# Clone and install
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Build assets
npm run build

# Start queue worker
php artisan horizon

# Start websocket server
php artisan reverb:start
```

---

*Last updated: Phase 1 — Project Initialization*
