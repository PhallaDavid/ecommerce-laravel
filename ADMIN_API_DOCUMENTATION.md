# Admin API Documentation

## Base URL
All admin endpoints are prefixed with `/api/admin` and require:
- Authentication: `Bearer Token` (from `auth:sanctum`)
- Admin Role: User must have `role = 'admin'`

## Authentication
Include the token in the Authorization header:
```
Authorization: Bearer {your_token}
```

---

## Dashboard Statistics

### GET `/api/admin/dashboard/stats`
Get dashboard statistics.

**Response:**
```json
{
  "status": true,
  "stats": {
    "total_products": 156,
    "total_users": 1243,
    "total_orders": 89,
    "total_revenue": 45678.50,
    "pending_orders": 12,
    "completed_orders": 77
  }
}
```

---

## Users Management

### GET `/api/admin/users`
Get all users with pagination.

**Query Parameters:**
- `per_page` (optional, default: 15) - Items per page
- `search` (optional) - Search by name or email

**Response:**
```json
{
  "status": true,
  "users": {
    "data": [...],
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

### GET `/api/admin/users/{id}`
Get single user by ID.

**Response:**
```json
{
  "status": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    ...
  }
}
```

### POST `/api/admin/users`
Create new user.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "user",
  "phone": "1234567890",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "zip": "10001",
  "verify_status": "completed"
}
```

**Response:**
```json
{
  "status": true,
  "message": "User created successfully",
  "user": {...}
}
```

### PUT `/api/admin/users/{id}`
Update user.

**Request Body:** (all fields optional)
```json
{
  "name": "John Doe Updated",
  "email": "johnnew@example.com",
  "password": "newpassword123",
  "role": "admin",
  "verify_status": "completed"
}
```

### DELETE `/api/admin/users/{id}`
Delete user (cannot delete own account).

**Response:**
```json
{
  "status": true,
  "message": "User deleted successfully"
}
```

---

## Products Management

### GET `/api/admin/products`
Get all products with pagination.

**Query Parameters:**
- `per_page` (optional, default: 15)
- `search` (optional) - Search by name, description, or SKU
- `category_id` (optional) - Filter by category

**Response:**
```json
{
  "status": true,
  "products": {
    "data": [...],
    "current_page": 1,
    "per_page": 15,
    "total": 50
  }
}
```

### POST `/api/admin/products`
Create new product.

**Request Body:**
```json
{
  "name": "Product Name",
  "description": "Product description",
  "price": 99.99,
  "sale_price": 79.99,
  "stock": 100,
  "category_id": 1,
  "images": [/* file uploads */],
  "sku": "SKU123",
  "barcode": "123456789",
  "featured": true,
  "is_active": true,
  "weight": 500,
  "length": 10,
  "width": 5,
  "height": 3,
  "sizes": ["S", "M", "L"],
  "colors": ["Red", "Blue"],
  "promotion_start": "2024-01-01",
  "promotion_end": "2024-01-31"
}
```

**Response:**
```json
{
  "message": "Product created successfully",
  "product": {...}
}
```

### PUT `/api/admin/products/{id}`
Update product.

**Request Body:** (all fields optional, same as create)

### DELETE `/api/admin/products/{id}`
Delete product.

**Response:**
```json
{
  "message": "Product deleted successfully"
}
```

---

## Orders Management

### GET `/api/admin/orders`
Get all orders with pagination.

**Query Parameters:**
- `per_page` (optional, default: 15)
- `status` (optional) - Filter by status: pending, processing, shipped, completed, cancelled

**Response:**
```json
{
  "status": true,
  "orders": {
    "data": [
      {
        "id": 1,
        "user": {...},
        "items": [...],
        "total": 299.99,
        "status": "pending",
        ...
      }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 30
  }
}
```

### GET `/api/admin/orders/{id}`
Get single order by ID.

**Response:**
```json
{
  "status": true,
  "order": {
    "id": 1,
    "user": {...},
    "items": [
      {
        "product": {...},
        "quantity": 2,
        "price": 99.99
      }
    ],
    "total": 199.98,
    "status": "pending"
  }
}
```

### PUT `/api/admin/orders/{id}/status`
Update order status.

**Request Body:**
```json
{
  "status": "completed"
}
```

**Valid statuses:** `pending`, `processing`, `shipped`, `completed`, `cancelled`

**Response:**
```json
{
  "status": true,
  "message": "Order status updated successfully",
  "order": {...}
}
```

---

## Error Responses

All endpoints return standard error responses:

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden:**
```json
{
  "message": "Unauthorized. Admin access required."
}
```

**404 Not Found:**
```json
{
  "message": "Resource not found"
}
```

**422 Validation Error:**
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

## Notes

1. All admin endpoints require the user to have `role = 'admin'`
2. Products can also be created/updated via the public endpoints, but admin endpoints provide additional security
3. The admin cannot delete their own account
4. All endpoints support pagination where applicable
5. Search functionality is available for users and products

