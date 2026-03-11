# Snackzar — API Usage Guide

## Base URL

```
Production: https://snackzar.com/api/v1
Local:      http://localhost:8000/api/v1
```

## Authentication

All authenticated endpoints require a Bearer token obtained via login.

### Register

```http
POST /api/v1/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "9876543210"
}
```

### Login

```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": { "id": 1, "name": "John Doe", ... },
        "token": "1|abc123..."
    }
}
```

### Using the Token

```http
Authorization: Bearer 1|abc123...
```

---

## Response Format

All API responses follow a consistent format:

### Success
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

### Error
```json
{
    "success": false,
    "message": "Error description",
    "errors": { "field": ["Validation message"] }
}
```

---

## Public Endpoints (No Auth Required)

### Products

```http
# List products (paginated)
GET /api/v1/products?page=1&per_page=15

# Filter by category
GET /api/v1/products?category_id=1

# Search
GET /api/v1/products?search=makhana

# Price range
GET /api/v1/products?min_price=100&max_price=500

# Featured only
GET /api/v1/products?featured=1

# Sort: price, name, created_at, avg_rating, total_sold
GET /api/v1/products?sort_by=price&sort_dir=asc

# Single product by slug
GET /api/v1/products/{slug}
```

### Categories

```http
# List active categories (with children)
GET /api/v1/categories

# Single category by slug
GET /api/v1/categories/{slug}
```

### Reviews

```http
# List approved reviews for a product
GET /api/v1/products/{product_id}/reviews
```

---

## Buyer Endpoints (Auth Required, role: user)

### Profile

```http
GET    /api/v1/user/profile
PUT    /api/v1/user/profile
PUT    /api/v1/user/profile/password
DELETE /api/v1/user/profile
```

### Addresses

```http
GET    /api/v1/user/addresses
POST   /api/v1/user/addresses
PUT    /api/v1/user/addresses/{id}
DELETE /api/v1/user/addresses/{id}
PUT    /api/v1/user/addresses/{id}/default
```

### Cart

```http
GET    /api/v1/user/cart
POST   /api/v1/user/cart/items          # { product_id, quantity, variant_id? }
PUT    /api/v1/user/cart/items/{id}     # { quantity }
DELETE /api/v1/user/cart/items/{id}
DELETE /api/v1/user/cart                 # Clear cart
```

### Orders

```http
GET    /api/v1/user/orders
POST   /api/v1/user/orders              # { address_id, payment_method?, notes? }
GET    /api/v1/user/orders/{id}
POST   /api/v1/user/orders/{id}/cancel  # { reason }
```

### Wishlist

```http
GET    /api/v1/user/wishlist
POST   /api/v1/user/wishlist/toggle     # { product_id }
DELETE /api/v1/user/wishlist/{id}
```

### Reviews

```http
POST   /api/v1/user/reviews             # { product_id, rating, comment? }
PUT    /api/v1/user/reviews/{id}
DELETE /api/v1/user/reviews/{id}
```

---

## Seller Endpoints (Auth Required, role: seller)

### Dashboard

```http
GET /api/v1/seller/dashboard
```

### Products

```http
GET    /api/v1/seller/products
POST   /api/v1/seller/products
GET    /api/v1/seller/products/{id}
PUT    /api/v1/seller/products/{id}
DELETE /api/v1/seller/products/{id}
PUT    /api/v1/seller/products/{id}/toggle-active
```

### Orders

```http
GET /api/v1/seller/orders
GET /api/v1/seller/orders/{id}
PUT /api/v1/seller/orders/{id}/status   # { status }
```

---

## Delivery Partner Endpoints (Auth Required, role: delivery_partner)

### Dashboard

```http
GET /api/v1/delivery/dashboard
```

### Assignments

```http
GET  /api/v1/delivery/assignments
GET  /api/v1/delivery/assignments/{id}
PUT  /api/v1/delivery/assignments/{id}/accept
PUT  /api/v1/delivery/assignments/{id}/reject
PUT  /api/v1/delivery/assignments/{id}/status  # { status }
```

---

## Admin Endpoints (Auth Required, role: admin)

### Dashboard

```http
GET /api/v1/admin/dashboard
```

### Users

```http
GET  /api/v1/admin/users
GET  /api/v1/admin/users/{id}
PUT  /api/v1/admin/users/{id}/ban
PUT  /api/v1/admin/users/{id}/activate
```

### Orders

```http
GET /api/v1/admin/orders
GET /api/v1/admin/orders/{id}
PUT /api/v1/admin/orders/{id}/status
PUT /api/v1/admin/orders/{id}/assign-delivery
```

### Sellers & Delivery

```http
GET /api/v1/admin/sellers
PUT /api/v1/admin/sellers/{id}/approve
PUT /api/v1/admin/sellers/{id}/reject
```

### Categories

```http
GET    /api/v1/admin/categories
POST   /api/v1/admin/categories
GET    /api/v1/admin/categories/{id}
PUT    /api/v1/admin/categories/{id}
DELETE /api/v1/admin/categories/{id}
```

### Blog

```http
GET    /api/v1/admin/blog
POST   /api/v1/admin/blog
GET    /api/v1/admin/blog/{id}
PUT    /api/v1/admin/blog/{id}
DELETE /api/v1/admin/blog/{id}
```

---

## Interactive API Docs

Full interactive documentation with try-it-out functionality is available at:

- **Web UI:** `{APP_URL}/docs`
- **Postman Collection:** `{APP_URL}/docs/collection.json`
- **OpenAPI 3.0 Spec:** `{APP_URL}/docs/openapi.yaml`
