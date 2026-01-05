# RBAC (Role-Based Access Control) API Planning Document

## Overview

This document outlines the planning for a comprehensive Role-Based Access Control (RBAC) system to manage users, roles, and permissions in the e-commerce Laravel application.

---

## 1. Database Structure

### 1.1 Tables Required

#### `roles` Table

```sql
- id (bigint, primary key)
- name (string, unique) - e.g., 'admin', 'manager', 'editor', 'customer'
- slug (string, unique) - e.g., 'admin', 'manager', 'editor', 'customer'
- description (text, nullable)
- is_active (boolean, default: true)
- created_at (timestamp)
- updated_at (timestamp)
```

#### `permissions` Table

```sql
- id (bigint, primary key)
- name (string, unique) - e.g., 'view_users', 'create_products', 'delete_orders'
- slug (string, unique) - e.g., 'view-users', 'create-products', 'delete-orders'
- description (text, nullable)
- module (string, nullable) - e.g., 'users', 'products', 'orders' (for grouping)
- created_at (timestamp)
- updated_at (timestamp)
```

#### `role_permission` Table (Pivot)

```sql
- id (bigint, primary key)
- role_id (bigint, foreign key -> roles.id)
- permission_id (bigint, foreign key -> permissions.id)
- created_at (timestamp)
- updated_at (timestamp)
- UNIQUE(role_id, permission_id)
```

#### `user_role` Table (Pivot)

```sql
- id (bigint, primary key)
- user_id (bigint, foreign key -> users.id)
- role_id (bigint, foreign key -> roles.id)
- created_at (timestamp)
- updated_at (timestamp)
- UNIQUE(user_id, role_id)
```

#### `users` Table Modifications

-   Remove or keep the existing `role` column (can be used as default role)
-   Add support for multiple roles via `user_role` pivot table

---

## 2. Models

### 2.1 Role Model (`app/Models/Role.php`)

```php
Relationships:
- belongsToMany(Permission::class) - via role_permission
- belongsToMany(User::class) - via user_role

Methods:
- hasPermission($permission) - Check if role has specific permission
- assignPermission($permission) - Assign permission to role
- revokePermission($permission) - Remove permission from role
```

### 2.2 Permission Model (`app/Models/Permission.php`)

```php
Relationships:
- belongsToMany(Role::class) - via role_permission

Methods:
- roles() - Get all roles that have this permission
```

### 2.3 User Model Updates (`app/Models/User.php`)

```php
Add Relationships:
- belongsToMany(Role::class) - via user_role
- hasManyThrough(Permission::class, Role::class) - via user_role and role_permission

Add Methods:
- hasRole($role) - Check if user has specific role
- hasPermission($permission) - Check if user has specific permission
- assignRole($role) - Assign role to user
- revokeRole($role) - Remove role from user
- hasAnyRole($roles) - Check if user has any of the given roles
- hasAllRoles($roles) - Check if user has all given roles
```

---

## 3. API Endpoints Structure

### 3.1 Base URL

All RBAC endpoints will be prefixed with `/api/admin/rbac` and require:

-   Authentication: `Bearer Token` (from `auth:sanctum`)
-   Admin Role: User must have admin role or specific permission

---

## 4. User Management APIs

### 4.1 Get All Users

**Endpoint:** `GET /api/admin/rbac/users`

**Query Parameters:**

-   `per_page` (optional, default: 15) - Items per page
-   `search` (optional) - Search by name or email
-   `role` (optional) - Filter by role slug
-   `status` (optional) - Filter by verify_status

**Response:**

```json
{
    "status": true,
    "users": {
        "data": [
            {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "roles": [
                    {
                        "id": 1,
                        "name": "Admin",
                        "slug": "admin"
                    }
                ],
                "permissions": [
                    {
                        "id": 1,
                        "name": "View Users",
                        "slug": "view-users"
                    }
                ],
                "verify_status": "completed",
                "created_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "current_page": 1,
        "per_page": 15,
        "total": 100
    }
}
```

**Required Permission:** `view-users`

---

### 4.2 Get Single User

**Endpoint:** `GET /api/admin/rbac/users/{id}`

**Response:**

```json
{
  "status": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "address": "123 Main St",
    "roles": [...],
    "permissions": [...],
    "verify_status": "completed",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

**Required Permission:** `view-users`

---

### 4.3 Create User

**Endpoint:** `POST /api/admin/rbac/users`

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "phone": "1234567890",
    "address": "123 Main St",
    "city": "New York",
    "state": "NY",
    "zip": "10001",
    "verify_status": "completed",
    "roles": [1, 2] // Array of role IDs
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

**Required Permission:** `create-users`

---

### 4.4 Update User

**Endpoint:** `PUT /api/admin/rbac/users/{id}`

**Request Body:** (all fields optional)

```json
{
    "name": "John Doe Updated",
    "email": "johnnew@example.com",
    "password": "newpassword123",
    "phone": "1234567890",
    "verify_status": "completed",
    "roles": [1, 3] // Update roles
}
```

**Response:**

```json
{
  "status": true,
  "message": "User updated successfully",
  "user": {...}
}
```

**Required Permission:** `update-users`

---

### 4.5 Delete User

**Endpoint:** `DELETE /api/admin/rbac/users/{id}`

**Response:**

```json
{
    "status": true,
    "message": "User deleted successfully"
}
```

**Required Permission:** `delete-users`
**Note:** Cannot delete own account

---

### 4.6 Assign Role to User

**Endpoint:** `POST /api/admin/rbac/users/{id}/roles`

**Request Body:**

```json
{
    "role_id": 2
}
```

**Response:**

```json
{
  "status": true,
  "message": "Role assigned successfully",
  "user": {...}
}
```

**Required Permission:** `assign-roles`

---

### 4.7 Revoke Role from User

**Endpoint:** `DELETE /api/admin/rbac/users/{id}/roles/{roleId}`

**Response:**

```json
{
  "status": true,
  "message": "Role revoked successfully",
  "user": {...}
}
```

**Required Permission:** `revoke-roles`

---

### 4.8 Get User Permissions

**Endpoint:** `GET /api/admin/rbac/users/{id}/permissions`

**Response:**

```json
{
    "status": true,
    "permissions": [
        {
            "id": 1,
            "name": "View Users",
            "slug": "view-users",
            "module": "users"
        }
    ]
}
```

**Required Permission:** `view-users`

---

## 5. Role Management APIs

### 5.1 Get All Roles

**Endpoint:** `GET /api/admin/rbac/roles`

**Query Parameters:**

-   `per_page` (optional, default: 15)
-   `search` (optional) - Search by name
-   `is_active` (optional) - Filter by active status

**Response:**

```json
{
    "status": true,
    "roles": {
        "data": [
            {
                "id": 1,
                "name": "Admin",
                "slug": "admin",
                "description": "Administrator with full access",
                "is_active": true,
                "permissions_count": 25,
                "users_count": 5,
                "created_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "current_page": 1,
        "per_page": 15,
        "total": 10
    }
}
```

**Required Permission:** `view-roles`

---

### 5.2 Get Single Role

**Endpoint:** `GET /api/admin/rbac/roles/{id}`

**Response:**

```json
{
    "status": true,
    "role": {
        "id": 1,
        "name": "Admin",
        "slug": "admin",
        "description": "Administrator with full access",
        "is_active": true,
        "permissions": [
            {
                "id": 1,
                "name": "View Users",
                "slug": "view-users",
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
        "created_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

**Required Permission:** `view-roles`

---

### 5.3 Create Role

**Endpoint:** `POST /api/admin/rbac/roles`

**Request Body:**

```json
{
    "name": "Manager",
    "slug": "manager",
    "description": "Manager role with limited access",
    "is_active": true,
    "permissions": [1, 2, 3] // Array of permission IDs
}
```

**Response:**

```json
{
  "status": true,
  "message": "Role created successfully",
  "role": {...}
}
```

**Required Permission:** `create-roles`

---

### 5.4 Update Role

**Endpoint:** `PUT /api/admin/rbac/roles/{id}`

**Request Body:** (all fields optional)

```json
{
    "name": "Manager Updated",
    "description": "Updated description",
    "is_active": false,
    "permissions": [1, 2, 4, 5] // Update permissions
}
```

**Response:**

```json
{
  "status": true,
  "message": "Role updated successfully",
  "role": {...}
}
```

**Required Permission:** `update-roles`

---

### 5.5 Delete Role

**Endpoint:** `DELETE /api/admin/rbac/roles/{id}`

**Response:**

```json
{
    "status": true,
    "message": "Role deleted successfully"
}
```

**Required Permission:** `delete-roles`
**Note:** Cannot delete role if it's assigned to users (or handle cascade)

---

### 5.6 Assign Permission to Role

**Endpoint:** `POST /api/admin/rbac/roles/{id}/permissions`

**Request Body:**

```json
{
    "permission_id": 5
}
```

**Response:**

```json
{
  "status": true,
  "message": "Permission assigned successfully",
  "role": {...}
}
```

**Required Permission:** `assign-permissions`

---

### 5.7 Revoke Permission from Role

**Endpoint:** `DELETE /api/admin/rbac/roles/{id}/permissions/{permissionId}`

**Response:**

```json
{
  "status": true,
  "message": "Permission revoked successfully",
  "role": {...}
}
```

**Required Permission:** `revoke-permissions`

---

### 5.8 Get Role Permissions

**Endpoint:** `GET /api/admin/rbac/roles/{id}/permissions`

**Response:**

```json
{
    "status": true,
    "permissions": [
        {
            "id": 1,
            "name": "View Users",
            "slug": "view-users",
            "module": "users"
        }
    ]
}
```

**Required Permission:** `view-roles`

---

## 6. Permission Management APIs

### 6.1 Get All Permissions

**Endpoint:** `GET /api/admin/rbac/permissions`

**Query Parameters:**

-   `per_page` (optional, default: 15)
-   `search` (optional) - Search by name or slug
-   `module` (optional) - Filter by module

**Response:**

```json
{
    "status": true,
    "permissions": {
        "data": [
            {
                "id": 1,
                "name": "View Users",
                "slug": "view-users",
                "description": "Permission to view users",
                "module": "users",
                "roles_count": 3,
                "created_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "current_page": 1,
        "per_page": 15,
        "total": 50
    }
}
```

**Required Permission:** `view-permissions`

---

### 6.2 Get Single Permission

**Endpoint:** `GET /api/admin/rbac/permissions/{id}`

**Response:**

```json
{
    "status": true,
    "permission": {
        "id": 1,
        "name": "View Users",
        "slug": "view-users",
        "description": "Permission to view users",
        "module": "users",
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

**Required Permission:** `view-permissions`

---

### 6.3 Create Permission

**Endpoint:** `POST /api/admin/rbac/permissions`

**Request Body:**

```json
{
    "name": "Export Products",
    "slug": "export-products",
    "description": "Permission to export products",
    "module": "products"
}
```

**Response:**

```json
{
  "status": true,
  "message": "Permission created successfully",
  "permission": {...}
}
```

**Required Permission:** `create-permissions`

---

### 6.4 Update Permission

**Endpoint:** `PUT /api/admin/rbac/permissions/{id}`

**Request Body:** (all fields optional)

```json
{
    "name": "Export Products Updated",
    "description": "Updated description",
    "module": "products"
}
```

**Response:**

```json
{
  "status": true,
  "message": "Permission updated successfully",
  "permission": {...}
}
```

**Required Permission:** `update-permissions`

---

### 6.5 Delete Permission

**Endpoint:** `DELETE /api/admin/rbac/permissions/{id}`

**Response:**

```json
{
    "status": true,
    "message": "Permission deleted successfully"
}
```

**Required Permission:** `delete-permissions`

---

### 6.6 Get Permission Roles

**Endpoint:** `GET /api/admin/rbac/permissions/{id}/roles`

**Response:**

```json
{
    "status": true,
    "roles": [
        {
            "id": 1,
            "name": "Admin",
            "slug": "admin"
        }
    ]
}
```

**Required Permission:** `view-permissions`

---

## 7. Middleware & Authorization

### 7.1 Permission Middleware

**File:** `app/Http/Middleware/CheckPermission.php`

**Usage:**

```php
Route::middleware(['auth:sanctum', 'permission:view-users'])->group(function () {
    // Routes that require 'view-users' permission
});
```

### 7.2 Role Middleware

**File:** `app/Http/Middleware/CheckRole.php`

**Usage:**

```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Routes that require 'admin' role
});
```

### 7.3 Multiple Roles/Permissions

Support checking multiple roles or permissions:

```php
Route::middleware(['auth:sanctum', 'role:admin|manager'])->group(...);
Route::middleware(['auth:sanctum', 'permission:view-users|create-users'])->group(...);
```

---

## 8. Default Roles & Permissions

### 8.1 Default Roles

1. **Super Admin** - Full system access
2. **Admin** - Administrative access
3. **Manager** - Management access
4. **Editor** - Content editing access
5. **Customer** - Basic user access

### 8.2 Default Permission Modules

#### Users Module

-   `view-users` - View users list
-   `create-users` - Create new users
-   `update-users` - Update existing users
-   `delete-users` - Delete users
-   `assign-roles` - Assign roles to users
-   `revoke-roles` - Revoke roles from users

#### Roles Module

-   `view-roles` - View roles list
-   `create-roles` - Create new roles
-   `update-roles` - Update existing roles
-   `delete-roles` - Delete roles
-   `assign-permissions` - Assign permissions to roles
-   `revoke-permissions` - Revoke permissions from roles

#### Permissions Module

-   `view-permissions` - View permissions list
-   `create-permissions` - Create new permissions
-   `update-permissions` - Update existing permissions
-   `delete-permissions` - Delete permissions

#### Products Module

-   `view-products` - View products
-   `create-products` - Create products
-   `update-products` - Update products
-   `delete-products` - Delete products
-   `export-products` - Export products

#### Orders Module

-   `view-orders` - View orders
-   `update-orders` - Update orders
-   `delete-orders` - Delete orders
-   `export-orders` - Export orders

#### Categories Module

-   `view-categories` - View categories
-   `create-categories` - Create categories
-   `update-categories` - Update categories
-   `delete-categories` - Delete categories

#### Dashboard Module

-   `view-dashboard` - View dashboard

---

## 9. Implementation Steps

### Phase 1: Database Setup

1. Create migrations for `roles`, `permissions`, `role_permission`, `user_role` tables
2. Create seeders for default roles and permissions
3. Update `users` table if needed

### Phase 2: Models & Relationships

1. Create `Role` model
2. Create `Permission` model
3. Update `User` model with relationships and helper methods
4. Create pivot models if needed

### Phase 3: Middleware

1. Create `CheckPermission` middleware
2. Create `CheckRole` middleware
3. Register middleware in `Kernel.php`

### Phase 4: Controllers

1. Create `UserRoleController` for user-role management
2. Create `RoleController` for role management
3. Create `PermissionController` for permission management
4. Update `AdminController` or create separate RBAC controller

### Phase 5: Routes

1. Define all RBAC routes in `routes/api.php`
2. Apply appropriate middleware
3. Group routes logically

### Phase 6: Validation & Requests

1. Create Form Request classes for validation
2. Implement proper validation rules
3. Add authorization checks in requests

### Phase 7: Testing

1. Test all endpoints
2. Test middleware
3. Test edge cases (self-deletion, etc.)

---

## 10. Security Considerations

1. **Self-Protection:** Users cannot delete themselves or revoke their own admin role
2. **Last Admin:** Prevent deletion of last admin user
3. **Role Protection:** Prevent deletion of critical roles (e.g., admin)
4. **Permission Validation:** Validate all permission checks server-side
5. **Audit Logging:** Consider adding audit logs for role/permission changes
6. **Rate Limiting:** Apply rate limiting to sensitive endpoints

---

## 11. Response Format Standards

### Success Response

```json
{
  "status": true,
  "message": "Operation successful",
  "data": {...}
}
```

### Error Response

```json
{
    "status": false,
    "message": "Error message",
    "errors": {
        "field": ["Error details"]
    }
}
```

### Pagination Response

```json
{
  "status": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

---

## 12. API Versioning (Optional)

Consider versioning for future changes:

-   `/api/v1/admin/rbac/users`
-   `/api/v2/admin/rbac/users`

---

## 13. Documentation

1. Update API documentation with all new endpoints
2. Include request/response examples
3. Document permission requirements
4. Add Postman collection or OpenAPI spec

---

## 14. Future Enhancements

1. **Permission Groups:** Group related permissions
2. **Role Hierarchy:** Implement role inheritance
3. **Temporary Permissions:** Time-based permission grants
4. **Permission Conditions:** Conditional permissions based on data
5. **Audit Trail:** Track all permission/role changes
6. **Bulk Operations:** Bulk assign/revoke roles and permissions
7. **Role Templates:** Pre-configured role templates

---

---

## 15. Quick Reference - API Endpoints Summary

### User Management

| Method | Endpoint                                    | Permission Required | Description           |
| ------ | ------------------------------------------- | ------------------- | --------------------- |
| GET    | `/api/admin/rbac/users`                     | `view-users`        | Get all users         |
| GET    | `/api/admin/rbac/users/{id}`                | `view-users`        | Get single user       |
| POST   | `/api/admin/rbac/users`                     | `create-users`      | Create user           |
| PUT    | `/api/admin/rbac/users/{id}`                | `update-users`      | Update user           |
| DELETE | `/api/admin/rbac/users/{id}`                | `delete-users`      | Delete user           |
| POST   | `/api/admin/rbac/users/{id}/roles`          | `assign-roles`      | Assign role to user   |
| DELETE | `/api/admin/rbac/users/{id}/roles/{roleId}` | `revoke-roles`      | Revoke role from user |
| GET    | `/api/admin/rbac/users/{id}/permissions`    | `view-users`        | Get user permissions  |

### Role Management

| Method | Endpoint                                                | Permission Required  | Description                 |
| ------ | ------------------------------------------------------- | -------------------- | --------------------------- |
| GET    | `/api/admin/rbac/roles`                                 | `view-roles`         | Get all roles               |
| GET    | `/api/admin/rbac/roles/{id}`                            | `view-roles`         | Get single role             |
| POST   | `/api/admin/rbac/roles`                                 | `create-roles`       | Create role                 |
| PUT    | `/api/admin/rbac/roles/{id}`                            | `update-roles`       | Update role                 |
| DELETE | `/api/admin/rbac/roles/{id}`                            | `delete-roles`       | Delete role                 |
| POST   | `/api/admin/rbac/roles/{id}/permissions`                | `assign-permissions` | Assign permission to role   |
| DELETE | `/api/admin/rbac/roles/{id}/permissions/{permissionId}` | `revoke-permissions` | Revoke permission from role |
| GET    | `/api/admin/rbac/roles/{id}/permissions`                | `view-roles`         | Get role permissions        |

### Permission Management

| Method | Endpoint                                 | Permission Required  | Description           |
| ------ | ---------------------------------------- | -------------------- | --------------------- |
| GET    | `/api/admin/rbac/permissions`            | `view-permissions`   | Get all permissions   |
| GET    | `/api/admin/rbac/permissions/{id}`       | `view-permissions`   | Get single permission |
| POST   | `/api/admin/rbac/permissions`            | `create-permissions` | Create permission     |
| PUT    | `/api/admin/rbac/permissions/{id}`       | `update-permissions` | Update permission     |
| DELETE | `/api/admin/rbac/permissions/{id}`       | `delete-permissions` | Delete permission     |
| GET    | `/api/admin/rbac/permissions/{id}/roles` | `view-permissions`   | Get permission roles  |

---

## Notes

-   All endpoints require authentication via Sanctum
-   Permission checks should be done at both middleware and controller levels
-   Consider caching roles and permissions for performance
-   Use database transactions for multi-step operations
-   Implement soft deletes for roles and permissions if needed
-   Add indexes on foreign keys and frequently queried columns
