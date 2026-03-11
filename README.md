# 🥜 Snackzar — Bihar's Premium Makhana & Snacks Marketplace

<p align="center">
  <strong>A production-grade multi-vendor ecommerce platform for authentic Bihari snacks</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-red?logo=laravel" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Vue.js-3-green?logo=vue.js" alt="Vue 3">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?logo=php" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Tests-217%20passing-brightgreen" alt="Tests">
  <img src="https://img.shields.io/badge/License-MIT-yellow" alt="License">
</p>

---

## Overview

Snackzar is a full-featured multi-vendor marketplace built with **Laravel 12**, **Vue 3**, **Inertia.js**, and **TailwindCSS 4**. It connects Bihar's local snack producers (starting with Makhana) with customers nationwide, featuring four distinct user panels: **Admin**, **Seller**, **Buyer**, and **Delivery Partner**.

**Live:** [https://snackzar.com](https://snackzar.com)

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Vue 3, Inertia.js v2, TailwindCSS 4 |
| Database | MySQL 8+ |
| Cache & Queue | Redis (Predis) |
| Auth | Laravel Sanctum, Spatie Permission |
| Realtime | Laravel Reverb (WebSockets) |
| Queue Monitor | Laravel Horizon |
| API Docs | Scribe (OpenAPI 3.0) |
| Testing | Pest PHP 3.8 |
| Build | Vite 6 |

---

## Features

### 🛒 Buyer Panel
- Product browsing with filters (category, price, rating, search)
- Product detail pages with images, variants, and reviews
- Cart management (add, update quantity, remove, clear)
- Wishlist (toggle on/off)
- Order placement with address selection
- Order tracking and cancellation
- Product reviews and ratings
- User profile management
- Multiple delivery addresses

### 🏪 Seller Panel
- Seller registration with business profile (GST, PAN, bank details)
- Product management (CRUD with images, variants, pricing)
- Order management for seller's products
- Dashboard with sales stats

### 🚚 Delivery Partner Panel
- Delivery registration with vehicle details
- Assignment management (accept/reject, status updates)
- Dashboard with delivery stats

### 👨‍💼 Admin Panel
- Full dashboard with revenue, orders, users, sellers stats
- User management (list, view, ban/activate)
- Order management with status flow
- Seller & delivery partner approval
- Category management (CRUD with hierarchy)
- Blog management (CRUD with rich content)

### 🔧 Platform Features
- **Multi-vendor architecture** with seller profiles and commission system
- **Role-based access control** (Admin, User, Seller, Delivery Partner)
- **OTP authentication** via Twilio SMS
- **Google OAuth** social login
- **Real-time notifications** via Laravel Reverb WebSockets
- **Shiprocket integration** for logistics
- **ImageKit integration** for media storage
- **SEO optimization** with meta tags and XML sitemap
- **Blog system** with categories, tags, and view tracking
- **Redis caching** for homepage, categories, featured products, sitemap
- **API documentation** at `/docs` (80+ endpoints)

---

## Architecture

```
app/
├── Console/Commands/          # Artisan commands (ClearAppCache)
├── Http/Controllers/          # Web controllers (Home, Blog, Sitemap)
├── Models/                    # Eloquent models (26 models)
├── Observers/                 # Cache-busting observers (Product, Category, Review)
├── Providers/                 # Service providers
├── Traits/                    # ApiResponse trait
└── Modules/
    ├── Admin/                 # Admin module
    │   ├── Controllers/       # Dashboard, Orders, Users, Sellers, Categories, Blog
    │   ├── Services/          # AdminOrder, AdminUser, AdminSeller services
    │   └── routes/            # Admin API routes
    ├── User/                  # User module
    │   ├── Controllers/       # Auth, Profile, Cart, Orders, Wishlist, Reviews, etc.
    │   ├── Services/          # Cart, Order, Wishlist, Review, Profile services
    │   └── routes/            # User API + web routes
    ├── Seller/                # Seller module
    │   ├── Controllers/       # Dashboard, Products, Orders
    │   ├── Services/          # SellerProduct, SellerOrder, SellerDashboard services
    │   └── routes/            # Seller API routes
    ├── Delivery/              # Delivery module
    │   ├── Controllers/       # Dashboard, Assignments
    │   ├── Services/          # DeliveryAssignment, DeliveryDashboard services
    │   └── routes/            # Delivery API routes
    └── Shared/                # Shared module
        ├── Services/          # Product, Category, Blog, Sitemap, Notification services
        └── routes/            # Shared API routes
```

### Design Patterns
- **Modular Architecture**: Each domain (Admin, Seller, User, Delivery) is encapsulated
- **Service Layer**: Business logic lives in Services, controllers stay thin
- **ApiResponse Trait**: Consistent JSON responses across all API endpoints
- **Observer Pattern**: Automatic cache invalidation on model changes
- **Repository-like Services**: Each service manages a specific model/domain

---

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ and npm
- MySQL 8+
- Redis

### Steps

```bash
# Clone the repository
git clone https://github.com/yourusername/snackzar.git
cd snackzar

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your .env file (database, Redis, etc.)
# Required: DB_DATABASE, DB_USERNAME, DB_PASSWORD, REDIS_HOST

# Run migrations and seed demo data
php artisan migrate --seed

# Build frontend assets
npm run build

# Start the development server
php artisan serve
```

### Demo Credentials (after seeding)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@snackzar.com | password |
| Seller | seller@snackzar.com | password |
| Seller 2 | seller2@snackzar.com | password |
| Buyer | user@snackzar.com | password |
| Buyer 2 | user2@snackzar.com | password |
| Delivery | delivery@snackzar.com | password |

### Additional Services

```bash
# Start Redis (required for cache & queues)
docker run -d -p 6379:6379 redis

# Start Horizon queue worker
php artisan horizon

# Start Reverb WebSocket server
php artisan reverb:start

# Start Vite dev server (for development)
npm run dev
```

---

## API Documentation

API documentation is auto-generated via **Scribe** and available at:

- **Web UI**: `/docs`
- **Postman Collection**: `/docs/collection.json`
- **OpenAPI Spec**: `/docs/openapi.yaml`

### API Structure

All API endpoints are versioned under `/api/v1/`:

| Prefix | Middleware | Description |
|--------|-----------|-------------|
| `/api/v1/` | `api` | Public endpoints (products, categories, auth) |
| `/api/v1/user/` | `auth:sanctum` | Authenticated user endpoints |
| `/api/v1/seller/` | `auth:sanctum, role:seller` | Seller-only endpoints |
| `/api/v1/delivery/` | `auth:sanctum, role:delivery_partner` | Delivery partner endpoints |
| `/api/v1/admin/` | `auth:sanctum, role:admin` | Admin-only endpoints |

### Authentication

```bash
# Register
POST /api/v1/register

# Login (returns Bearer token)
POST /api/v1/login

# Use token in subsequent requests
Authorization: Bearer {token}
```

### Regenerate API Docs

```bash
php artisan scribe:generate
```

---

## Testing

The project uses **Pest PHP** with SQLite in-memory database for testing.

```bash
# Run all tests (217 tests, 502 assertions)
php artisan test

# Run specific test suite
php artisan test --filter=PerformanceTest
php artisan test --filter=AdminTest
php artisan test --filter=AuthenticationTest

# Run with coverage
php artisan test --coverage
```

### Test Coverage by Module

| Module | Tests | Assertions |
|--------|-------|-----------|
| Architecture & Config | 11 | 34 |
| Authentication & RBAC | 23 | 51 |
| Profile & Addresses | 17 | 37 |
| Product Catalog | 16 | 34 |
| Cart, Wishlist, Reviews | 22 | 41 |
| Orders | 11 | 20 |
| Seller Panel | 16 | 27 |
| Delivery Panel | 13 | 32 |
| Admin Dashboard | 19 | 40 |
| Notifications | 7 | 13 |
| Media Management | 6 | 12 |
| Shipping | 5 | 6 |
| Realtime | 10 | 18 |
| Blog & SEO | 27 | 93 |
| Performance & Caching | 15 | 46 |
| **Total** | **217** | **502** |

---

## Caching Strategy

Redis caching is implemented for high-traffic endpoints:

| Cache Key | TTL | Description |
|-----------|-----|-------------|
| `homepage:featured` | 5 min | Featured products on homepage |
| `homepage:new_arrivals` | 5 min | New arrivals on homepage |
| `homepage:categories` | 1 hour | Category listing on homepage |
| `homepage:top_rated` | 5 min | Top rated products |
| `homepage:reviews` | 5 min | Recent reviews |
| `homepage:stats` | 10 min | Product/category/customer counts |
| `categories:active` | 1 hour | Active category tree |
| `categories:slug:{slug}` | 1 hour | Individual category by slug |
| `products:featured:{limit}` | 10 min | Featured products |
| `sitemap:urls` | 1 hour | XML sitemap URLs |

Cache is automatically invalidated via **Model Observers** when products, categories, or reviews are created/updated/deleted.

```bash
# Manual cache clear
php artisan app:clear-cache
```

---

## Database

26 migrations covering:

- **Users & Auth**: users, personal_access_tokens, otps, roles/permissions
- **Catalog**: products, product_images, product_variants, categories
- **Commerce**: carts, cart_items, orders, order_items, payments
- **Social**: reviews, wishlists
- **Sellers**: seller_profiles, seller_payouts
- **Delivery**: delivery_profiles, delivery_assignments
- **Content**: blog_posts, seo_meta
- **System**: notifications, jobs, cache

---

## Environment Variables

Key configuration in `.env`:

```env
# Application
APP_NAME=Snackzar
APP_FAKER_LOCALE=en_IN

# Database
DB_DATABASE=snackzar

# Cache & Queue (Redis)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Third-party Integrations
GOOGLE_CLIENT_ID=         # Google OAuth
TWILIO_SID=               # OTP via SMS
IMAGEKIT_*=               # Media storage
SHIPROCKET_*=             # Shipping/logistics
MAPMYINDIA_*=             # Location services

# Reverb WebSocket
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
```

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
