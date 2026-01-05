# RBAC API Documentation

## Base URL
All RBAC endpoints are prefixed with `/api/admin/rbac` and require:
- **Authentication:** `Bearer Token` (from `auth:sanctum`)
- **Permissions:** Each endpoint requires specific permissions (see below)

## Authentication
Include the token in the Authorization header:
```
Authorization: Bearer {your_token}
```

---

## Table of Contents
1. [User Management APIs](#user-management-apis)
2. [Role Management APIs](#role-management-apis)
3. [Permission Management APIs](#permission-management-apis)
4. [Error Responses](#error-responses)
5. [Quick Reference](#quick-reference)

---

## User Management APIs

### 1. Get All Users

**Endpoint:** `GET /api/admin/rbac/users`

**Required Permission:** `view-users`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `per_page` | integer | No | 15 | Items per page |
| `search` | string | No | - | Search by name or email |
| `role` | string | No | - | Filter by role slug (e.g., 'admin', 'manager') |
| `status` | string | No | - | Filter by verify_status ('pending' or 'completed') |

**Example Request:**
```bash
GET /api/admin/rbac/users?per_page=20&search=john&role=admin
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "users": {
    "data": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "1234567890",
        "address": "123 Main St",
        "city": "New York",
        "state": "NY",
        "zip": "10001",
        "verify_status": "completed",
        "roles": [
          {
            "id": 1,
            "name": "Admin",
            "slug": "admin",
            "description": "Administrative access",
            "is_active": true
          }
        ],
        "permissions": [
          {
            "id": 1,
            "name": "View Users",
            "slug": "view-users",
            "module": "users"
          },
          {
            "id": 2,
            "name": "Create Users",
            "slug": "create-users",
            "module": "users"
          }
        ],
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5
  }
}
```

---

### 2. Get Single User

**Endpoint:** `GET /api/admin/rbac/users/{id}`

**Required Permission:** `view-users`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | User ID |

**Example Request:**
```bash
GET /api/admin/rbac/users/1
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "address": "123 Main St",
    "city": "New York",
    "state": "NY",
    "zip": "10001",
    "verify_status": "completed",
    "roles": [
      {
        "id": 1,
        "name": "Admin",
        "slug": "admin",
        "description": "Administrative access",
        "is_active": true
      }
    ],
    "permissions": [
      {
        "id": 1,
        "name": "View Users",
        "slug": "view-users",
        "module": "users"
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### 3. Create User

**Endpoint:** `POST /api/admin/rbac/users`

**Required Permission:** `create-users`

**Request Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "password123",
  "phone": "9876543210",
  "address": "456 Oak Ave",
  "city": "Los Angeles",
  "state": "CA",
  "zip": "90001",
  "verify_status": "completed",
  "roles": [1, 2]
}
```

**Request Fields:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | User's full name |
| `email` | string | Yes | User's email (must be unique) |
| `password` | string | Yes | Password (min 8 characters) |
| `phone` | string | No | User's phone number |
| `address` | string | No | User's address |
| `city` | string | No | User's city |
| `state` | string | No | User's state |
| `zip` | string | No | User's zip code |
| `verify_status` | string | No | 'pending' or 'completed' (default: 'completed') |
| `roles` | array | No | Array of role IDs to assign |

**Example Request:**
```bash
POST /api/admin/rbac/users
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "password123",
  "roles": [1]
}
```

**Success Response (201):**
```json
{
  "status": true,
  "message": "User created successfully",
  "user": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "roles": [
      {
        "id": 1,
        "name": "Admin",
        "slug": "admin"
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### 4. Update User

**Endpoint:** `PUT /api/admin/rbac/users/{id}`

**Required Permission:** `update-users`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | User ID |

**Request Body:** (All fields are optional)
```json
{
  "name": "Jane Smith Updated",
  "email": "jane.new@example.com",
  "password": "newpassword123",
  "phone": "9876543210",
  "verify_status": "completed",
  "roles": [1, 3]
}
```

**Example Request:**
```bash
PUT /api/admin/rbac/users/2
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Jane Smith Updated",
  "roles": [1, 3]
}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "User updated successfully",
  "user": {
    "id": 2,
    "name": "Jane Smith Updated",
    "email": "jane.new@example.com",
    "roles": [
      {
        "id": 1,
        "name": "Admin",
        "slug": "admin"
      },
      {
        "id": 3,
        "name": "Manager",
        "slug": "manager"
      }
    ]
  }
}
```

**Note:** Users cannot revoke their own admin role.

---

### 5. Delete User

**Endpoint:** `DELETE /api/admin/rbac/users/{id}`

**Required Permission:** `delete-users`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | User ID |

**Example Request:**
```bash
DELETE /api/admin/rbac/users/2
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "User deleted successfully"
}
```

**Error Responses:**
- **400:** Cannot delete your own account
- **400:** Cannot delete the last admin user
- **404:** User not found

---

### 6. Assign Role to User

**Endpoint:** `POST /api/admin/rbac/users/{id}/roles`

**Required Permission:** `assign-roles`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | User ID |

**Request Body:**
```json
{
  "role_id": 2
}
```

**Request Fields:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `role_id` | integer | Yes | Role ID to assign |

**Example Request:**
```bash
POST /api/admin/rbac/users/2/roles
Authorization: Bearer {token}
Content-Type: application/json

{
  "role_id": 2
}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Role assigned successfully",
  "user": {
    "id": 2,
    "name": "Jane Smith",
    "roles": [
      {
        "id": 1,
        "name": "Admin",
        "slug": "admin"
      },
      {
        "id": 2,
        "name": "Manager",
        "slug": "manager"
      }
    ]
  }
}
```

---

### 7. Revoke Role from User

**Endpoint:** `DELETE /api/admin/rbac/users/{id}/roles/{roleId}`

**Required Permission:** `revoke-roles`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | User ID |
| `roleId` | integer | Role ID to revoke |

**Example Request:**
```bash
DELETE /api/admin/rbac/users/2/roles/2
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Role revoked successfully",
  "user": {
    "id": 2,
    "name": "Jane Smith",
    "roles": [
      {
        "id": 1,
        "name": "Admin",
        "slug": "admin"
      }
    ]
  }
}
```

**Error Responses:**
- **400:** Cannot revoke your own admin role
- **400:** Cannot revoke the last admin role
- **404:** User or Role not found

---

### 8. Get User Permissions

**Endpoint:** `GET /api/admin/rbac/users/{id}/permissions`

**Required Permission:** `view-users`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | User ID |

**Example Request:**
```bash
GET /api/admin/rbac/users/1/permissions
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "permissions": [
    {
      "id": 1,
      "name": "View Users",
      "slug": "view-users",
      "description": "View users list",
      "module": "users",
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Create Users",
      "slug": "create-users",
      "description": "Create new users",
      "module": "users",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

## Role Management APIs

### 1. Get All Roles

**Endpoint:** `GET /api/admin/rbac/roles`

**Required Permission:** `view-roles`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `per_page` | integer | No | 15 | Items per page |
| `search` | string | No | - | Search by name, slug, or description |
| `is_active` | boolean | No | - | Filter by active status (true/false) |

**Example Request:**
```bash
GET /api/admin/rbac/roles?per_page=20&is_active=true
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "roles": {
    "data": [
      {
        "id": 1,
        "name": "Admin",
        "slug": "admin",
        "description": "Administrative access",
        "is_active": true,
        "permissions_count": 25,
        "users_count": 5,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 20,
    "total": 10,
    "last_page": 1
  }
}
```

---

### 2. Get Single Role

**Endpoint:** `GET /api/admin/rbac/roles/{id}`

**Required Permission:** `view-roles`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Role ID |

**Example Request:**
```bash
GET /api/admin/rbac/roles/1
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "role": {
    "id": 1,
    "name": "Admin",
    "slug": "admin",
    "description": "Administrative access",
    "is_active": true,
    "permissions": [
      {
        "id": 1,
        "name": "View Users",
        "slug": "view-users",
        "module": "users"
      },
      {
        "id": 2,
        "name": "Create Users",
        "slug": "create-users",
        "module": "users"
      }
    ],
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### 3. Create Role

**Endpoint:** `POST /api/admin/rbac/roles`

**Required Permission:** `create-roles`

**Request Body:**
```json
{
  "name": "Manager",
  "slug": "manager",
  "description": "Manager role with limited access",
  "is_active": true,
  "permissions": [1, 2, 3]
}
```

**Request Fields:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Role name (must be unique) |
| `slug` | string | No | Role slug (auto-generated from name if not provided) |
| `description` | string | No | Role description |
| `is_active` | boolean | No | Active status (default: true) |
| `permissions` | array | No | Array of permission IDs to assign |

**Example Request:**
```bash
POST /api/admin/rbac/roles
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Manager",
  "description": "Manager role with limited access",
  "permissions": [1, 2, 3]
}
```

**Success Response (201):**
```json
{
  "status": true,
  "message": "Role created successfully",
  "role": {
    "id": 3,
    "name": "Manager",
    "slug": "manager",
    "description": "Manager role with limited access",
    "is_active": true,
    "permissions": [
      {
        "id": 1,
        "name": "View Users",
        "slug": "view-users"
      },
      {
        "id": 2,
        "name": "Create Users",
        "slug": "create-users"
      },
      {
        "id": 3,
        "name": "Update Users",
        "slug": "update-users"
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### 4. Update Role

**Endpoint:** `PUT /api/admin/rbac/roles/{id}`

**Required Permission:** `update-roles`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Role ID |

**Request Body:** (All fields are optional)
```json
{
  "name": "Manager Updated",
  "description": "Updated description",
  "is_active": false,
  "permissions": [1, 2, 4, 5]
}
```

**Example Request:**
```bash
PUT /api/admin/rbac/roles/3
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Manager Updated",
  "permissions": [1, 2, 4, 5]
}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Role updated successfully",
  "role": {
    "id": 3,
    "name": "Manager Updated",
    "slug": "manager",
    "description": "Updated description",
    "is_active": false,
    "permissions": [
      {
        "id": 1,
        "name": "View Users",
        "slug": "view-users"
      },
      {
        "id": 2,
        "name": "Create Users",
        "slug": "create-users"
      },
      {
        "id": 4,
        "name": "Delete Users",
        "slug": "delete-users"
      },
      {
        "id": 5,
        "name": "Assign Roles",
        "slug": "assign-roles"
      }
    ]
  }
}
```

---

### 5. Delete Role

**Endpoint:** `DELETE /api/admin/rbac/roles/{id}`

**Required Permission:** `delete-roles`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Role ID |

**Example Request:**
```bash
DELETE /api/admin/rbac/roles/3
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Role deleted successfully"
}
```

**Error Responses:**
- **400:** Cannot delete critical role (admin, super-admin)
- **400:** Cannot delete role that is assigned to users
- **404:** Role not found

---

### 6. Assign Permission to Role

**Endpoint:** `POST /api/admin/rbac/roles/{id}/permissions`

**Required Permission:** `assign-permissions`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Role ID |

**Request Body:**
```json
{
  "permission_id": 5
}
```

**Request Fields:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `permission_id` | integer | Yes | Permission ID to assign |

**Example Request:**
```bash
POST /api/admin/rbac/roles/3/permissions
Authorization: Bearer {token}
Content-Type: application/json

{
  "permission_id": 5
}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Permission assigned successfully",
  "role": {
    "id": 3,
    "name": "Manager",
    "permissions": [
      {
        "id": 1,
        "name": "View Users",
        "slug": "view-users"
      },
      {
        "id": 5,
        "name": "Assign Roles",
        "slug": "assign-roles"
      }
    ]
  }
}
```

---

### 7. Revoke Permission from Role

**Endpoint:** `DELETE /api/admin/rbac/roles/{id}/permissions/{permissionId}`

**Required Permission:** `revoke-permissions`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Role ID |
| `permissionId` | integer | Permission ID to revoke |

**Example Request:**
```bash
DELETE /api/admin/rbac/roles/3/permissions/5
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Permission revoked successfully",
  "role": {
    "id": 3,
    "name": "Manager",
    "permissions": [
      {
        "id": 1,
        "name": "View Users",
        "slug": "view-users"
      }
    ]
  }
}
```

---

### 8. Get Role Permissions

**Endpoint:** `GET /api/admin/rbac/roles/{id}/permissions`

**Required Permission:** `view-roles`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Role ID |

**Example Request:**
```bash
GET /api/admin/rbac/roles/1/permissions
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "permissions": [
    {
      "id": 1,
      "name": "View Users",
      "slug": "view-users",
      "description": "View users list",
      "module": "users",
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Create Users",
      "slug": "create-users",
      "description": "Create new users",
      "module": "users",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

## Permission Management APIs

### 1. Get All Permissions

**Endpoint:** `GET /api/admin/rbac/permissions`

**Required Permission:** `view-permissions`

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `per_page` | integer | No | 15 | Items per page |
| `search` | string | No | - | Search by name or slug |
| `module` | string | No | - | Filter by module (e.g., 'users', 'products') |

**Example Request:**
```bash
GET /api/admin/rbac/permissions?module=users&per_page=20
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "permissions": {
    "data": [
      {
        "id": 1,
        "name": "View Users",
        "slug": "view-users",
        "description": "View users list",
        "module": "users",
        "roles_count": 3,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 20,
    "total": 50,
    "last_page": 3
  }
}
```

---

### 2. Get Single Permission

**Endpoint:** `GET /api/admin/rbac/permissions/{id}`

**Required Permission:** `view-permissions`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Permission ID |

**Example Request:**
```bash
GET /api/admin/rbac/permissions/1
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "permission": {
    "id": 1,
    "name": "View Users",
    "slug": "view-users",
    "description": "View users list",
    "module": "users",
    "roles": [
      {
        "id": 1,
        "name": "Admin",
        "slug": "admin"
      },
      {
        "id": 3,
        "name": "Manager",
        "slug": "manager"
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### 3. Create Permission

**Endpoint:** `POST /api/admin/rbac/permissions`

**Required Permission:** `create-permissions`

**Request Body:**
```json
{
  "name": "Export Products",
  "slug": "export-products",
  "description": "Permission to export products",
  "module": "products"
}
```

**Request Fields:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Permission name (must be unique) |
| `slug` | string | No | Permission slug (auto-generated from name if not provided) |
| `description` | string | No | Permission description |
| `module` | string | No | Module name for grouping (e.g., 'users', 'products') |

**Example Request:**
```bash
POST /api/admin/rbac/permissions
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Export Products",
  "description": "Permission to export products",
  "module": "products"
}
```

**Success Response (201):**
```json
{
  "status": true,
  "message": "Permission created successfully",
  "permission": {
    "id": 31,
    "name": "Export Products",
    "slug": "export-products",
    "description": "Permission to export products",
    "module": "products",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### 4. Update Permission

**Endpoint:** `PUT /api/admin/rbac/permissions/{id}`

**Required Permission:** `update-permissions`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Permission ID |

**Request Body:** (All fields are optional)
```json
{
  "name": "Export Products Updated",
  "description": "Updated description",
  "module": "products"
}
```

**Example Request:**
```bash
PUT /api/admin/rbac/permissions/31
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Export Products Updated",
  "description": "Updated description"
}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Permission updated successfully",
  "permission": {
    "id": 31,
    "name": "Export Products Updated",
    "slug": "export-products",
    "description": "Updated description",
    "module": "products"
  }
}
```

---

### 5. Delete Permission

**Endpoint:** `DELETE /api/admin/rbac/permissions/{id}`

**Required Permission:** `delete-permissions`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Permission ID |

**Example Request:**
```bash
DELETE /api/admin/rbac/permissions/31
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Permission deleted successfully"
}
```

**Error Responses:**
- **400:** Cannot delete permission that is assigned to roles
- **404:** Permission not found

---

### 6. Get Permission Roles

**Endpoint:** `GET /api/admin/rbac/permissions/{id}/roles`

**Required Permission:** `view-permissions`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Permission ID |

**Example Request:**
```bash
GET /api/admin/rbac/permissions/1/roles
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "roles": [
    {
      "id": 1,
      "name": "Admin",
      "slug": "admin",
      "description": "Administrative access",
      "is_active": true
    },
    {
      "id": 3,
      "name": "Manager",
      "slug": "manager",
      "description": "Management access",
      "is_active": true
    }
  ]
}
```

---

## Error Responses

### 401 Unauthorized
Occurs when the request is missing authentication or the token is invalid.

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
Occurs when the user doesn't have the required permission.

```json
{
  "status": false,
  "message": "Unauthorized. You do not have the required permission."
}
```

### 404 Not Found
Occurs when the requested resource doesn't exist.

```json
{
  "status": false,
  "message": "User not found"
}
```

### 422 Validation Error
Occurs when request validation fails.

```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

### 400 Bad Request
Occurs when the request cannot be processed due to business logic constraints.

```json
{
  "status": false,
  "message": "Cannot delete your own account"
}
```

---

## Quick Reference

### User Management Endpoints

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/admin/rbac/users` | `view-users` | Get all users |
| GET | `/api/admin/rbac/users/{id}` | `view-users` | Get single user |
| POST | `/api/admin/rbac/users` | `create-users` | Create user |
| PUT | `/api/admin/rbac/users/{id}` | `update-users` | Update user |
| DELETE | `/api/admin/rbac/users/{id}` | `delete-users` | Delete user |
| POST | `/api/admin/rbac/users/{id}/roles` | `assign-roles` | Assign role to user |
| DELETE | `/api/admin/rbac/users/{id}/roles/{roleId}` | `revoke-roles` | Revoke role from user |
| GET | `/api/admin/rbac/users/{id}/permissions` | `view-users` | Get user permissions |

### Role Management Endpoints

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/admin/rbac/roles` | `view-roles` | Get all roles |
| GET | `/api/admin/rbac/roles/{id}` | `view-roles` | Get single role |
| POST | `/api/admin/rbac/roles` | `create-roles` | Create role |
| PUT | `/api/admin/rbac/roles/{id}` | `update-roles` | Update role |
| DELETE | `/api/admin/rbac/roles/{id}` | `delete-roles` | Delete role |
| POST | `/api/admin/rbac/roles/{id}/permissions` | `assign-permissions` | Assign permission to role |
| DELETE | `/api/admin/rbac/roles/{id}/permissions/{permissionId}` | `revoke-permissions` | Revoke permission from role |
| GET | `/api/admin/rbac/roles/{id}/permissions` | `view-roles` | Get role permissions |

### Permission Management Endpoints

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/admin/rbac/permissions` | `view-permissions` | Get all permissions |
| GET | `/api/admin/rbac/permissions/{id}` | `view-permissions` | Get single permission |
| POST | `/api/admin/rbac/permissions` | `create-permissions` | Create permission |
| PUT | `/api/admin/rbac/permissions/{id}` | `update-permissions` | Update permission |
| DELETE | `/api/admin/rbac/permissions/{id}` | `delete-permissions` | Delete permission |
| GET | `/api/admin/rbac/permissions/{id}/roles` | `view-permissions` | Get permission roles |

---

## Frontend Integration Examples

### JavaScript/TypeScript Example (Axios)

```javascript
import axios from 'axios';

const API_BASE_URL = 'https://your-api-domain.com/api/admin/rbac';

// Create axios instance with default config
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add token to requests
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Get all users
export const getUsers = async (params = {}) => {
  try {
    const response = await apiClient.get('/users', { params });
    return response.data;
  } catch (error) {
    console.error('Error fetching users:', error);
    throw error;
  }
};

// Create user
export const createUser = async (userData) => {
  try {
    const response = await apiClient.post('/users', userData);
    return response.data;
  } catch (error) {
    console.error('Error creating user:', error);
    throw error;
  }
};

// Update user
export const updateUser = async (userId, userData) => {
  try {
    const response = await apiClient.put(`/users/${userId}`, userData);
    return response.data;
  } catch (error) {
    console.error('Error updating user:', error);
    throw error;
  }
};

// Delete user
export const deleteUser = async (userId) => {
  try {
    const response = await apiClient.delete(`/users/${userId}`);
    return response.data;
  } catch (error) {
    console.error('Error deleting user:', error);
    throw error;
  }
};

// Assign role to user
export const assignRoleToUser = async (userId, roleId) => {
  try {
    const response = await apiClient.post(`/users/${userId}/roles`, {
      role_id: roleId,
    });
    return response.data;
  } catch (error) {
    console.error('Error assigning role:', error);
    throw error;
  }
};

// Get all roles
export const getRoles = async (params = {}) => {
  try {
    const response = await apiClient.get('/roles', { params });
    return response.data;
  } catch (error) {
    console.error('Error fetching roles:', error);
    throw error;
  }
};

// Create role
export const createRole = async (roleData) => {
  try {
    const response = await apiClient.post('/roles', roleData);
    return response.data;
  } catch (error) {
    console.error('Error creating role:', error);
    throw error;
  }
};

// Get all permissions
export const getPermissions = async (params = {}) => {
  try {
    const response = await apiClient.get('/permissions', { params });
    return response.data;
  } catch (error) {
    console.error('Error fetching permissions:', error);
    throw error;
  }
};
```

### React Hook Example

```javascript
import { useState, useEffect } from 'react';
import { getUsers, createUser, updateUser, deleteUser } from './api';

function UserManagement() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchUsers();
  }, []);

  const fetchUsers = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await getUsers({ per_page: 20 });
      setUsers(response.users.data);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to fetch users');
    } finally {
      setLoading(false);
    }
  };

  const handleCreateUser = async (userData) => {
    try {
      const response = await createUser(userData);
      if (response.status) {
        fetchUsers(); // Refresh list
        return response;
      }
    } catch (err) {
      throw err.response?.data || err;
    }
  };

  const handleUpdateUser = async (userId, userData) => {
    try {
      const response = await updateUser(userId, userData);
      if (response.status) {
        fetchUsers(); // Refresh list
        return response;
      }
    } catch (err) {
      throw err.response?.data || err;
    }
  };

  const handleDeleteUser = async (userId) => {
    if (!window.confirm('Are you sure you want to delete this user?')) {
      return;
    }
    try {
      const response = await deleteUser(userId);
      if (response.status) {
        fetchUsers(); // Refresh list
      }
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete user');
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      {/* Your user management UI */}
    </div>
  );
}
```

---

## Notes

1. **Authentication:** All endpoints require a valid Bearer token in the Authorization header
2. **Permissions:** Each endpoint requires specific permissions. Users without the required permission will receive a 403 Forbidden response
3. **Pagination:** List endpoints support pagination via `per_page` query parameter
4. **Search:** Most list endpoints support search functionality
5. **Validation:** All create/update endpoints validate input data and return detailed error messages
6. **Security:** 
   - Users cannot delete themselves
   - Users cannot revoke their own admin role
   - The last admin user cannot be deleted
   - Critical roles (admin, super-admin) cannot be deleted
7. **Response Format:** All responses follow a consistent format with `status`, `message`, and `data` fields

---

## Support

For issues or questions, please refer to the main planning document: `RBAC_API_PLANNING.md`

