# Introduction

REST API for Snackzar — Bihar's premium Makhana & snacks ecommerce marketplace. Supports User, Seller, Delivery Partner, and Admin panels.

<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>

## Welcome to the Snackzar API

This API powers the Snackzar ecommerce platform — a multi-vendor marketplace for Bihar's finest snacks.

### Authentication
All protected endpoints require a **Bearer token** obtained via the Login endpoint. Include the token in the `Authorization` header:
```
Authorization: Bearer {YOUR_AUTH_TOKEN}
```

### Roles
- **User**: Browse products, manage cart, place orders
- **Seller**: Manage products, fulfill orders, view payouts
- **Delivery Partner**: Accept/manage delivery assignments
- **Admin**: Full platform management

### Response Format
All API responses follow a consistent format:
```json
{
  "success": true,
  "message": "Success",
  "data": { ... }
}
```

<aside>Code examples are shown on the right (or below on mobile). Switch languages using the tabs.</aside>

