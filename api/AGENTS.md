# 🧠 AGENTS.md — E-Commerce API Architecture Guide

## 📌 Project Overview

This is a **scalable API-based eCommerce system** built with Laravel.
Supports:

* RBAC (Admin, Manager, Customer)
* Advanced Coupon & Offer System
* Analytics & Reports
* Soft Deletes (Trash सिस्टम)
* Event-driven architecture

---

## 🧑‍💼 Roles & Permissions (RBAC)

### Roles:

* **Admin** → Full access
* **Manager** → Product + Order + Limited Analytics
* **Customer** → Store access only

### Implementation:

* Use `spatie/laravel-permission`
* Middleware:

  * `role:admin`
  * `permission:edit products`

---

## ⚙️ Core Modules

### 1. Auth System

* Laravel Sanctum (recommended)
* Login, Register, Logout
* Role-based redirection handled in frontend

---

### 2. Product System

* Products, Categories, Brands
* Attributes & Variations (Size, Color)
* SKU & Inventory management

---

### 3. Order System

* Order lifecycle:

  * Pending → Paid → Shipped → Delivered
* Order Items
* Payment tracking

---

### 4. Coupon System

Supports:

* Product-based
* Category-based
* Cart-based
* Usage limits & expiration

---

### 5. Offer System

Supports:

* Black Friday Deals
* Buy X Get Y (BXGY)
* Flash Sales
* Rule-based discounts (JSON conditions)

---

### 6. Reports & Analytics

* Daily / Weekly / Monthly / Yearly
* Sales, Orders, Customers
* Export support

---

### 7. Soft Deletes (Trash System)

* All important tables use `deleted_at`
* Restore & Force Delete APIs required

---

## 🚀 Advanced System Design

### 🔔 Event & Listener

Used for decoupling business logic.

#### Example:

Event: `OrderPlaced`
Listeners:

* SendOrderConfirmationEmail
* GenerateInvoice
* UpdateAnalytics

---

### ⏳ Queue System

* Driver: Redis / Database
* Used for:

  * Emails
  * Invoice generation
  * Notifications

Command:

```bash
php artisan queue:work
```

---

### ⚡ Caching (Redis)

Used for:

* Product list
* Category tree
* Dashboard stats

Example:

```php
Cache::remember('products', 60, fn() => Product::all());
```

---

### 🔍 Elasticsearch (Search Engine)

Used for:

* Product search
* Autocomplete
* Filtering

Sync via:

* Laravel Scout

---

### 📝 Activity Logs

Track user actions:

* Product created
* Order updated
* Login activity

Use:

* spatie/laravel-activitylog

---

### 📜 Audit Trails

Track DB changes:

* Before & After values
* Who changed it

Use:

* Custom audit table OR package

---

## 🧱 API Design Principles

* RESTful structure
* Use API Resources (Transformers)
* Use Form Request Validation
* Pagination everywhere
* Filtering & search support

---

## 🔐 Security

* Sanctum authentication
* Role & permission middleware
* Rate limiting
* Input validation

---

## 📂 Folder Structure

```
app/
 ├── Models/
 ├── Http/
 │    ├── Controllers/API/
 │    ├── Requests/
 │    └── Resources/
 ├── Events/
 ├── Listeners/
 ├── Jobs/
 ├── Services/
 └── Repositories/
```

---

## 🧪 Testing

* Feature tests for APIs
* Unit tests for services
* Use factories & seeders

---

## 📈 Development Phases

### Phase 1

* Auth + RBAC
* Product System

### Phase 2

* Orders + Cart
* Coupons

### Phase 3

* Offers + Reports
* Analytics + Queue + Events

---

## 🏁 Goal

Build a **modular, scalable, production-ready eCommerce backend**
that can power:

* Web apps (Vue)
* Mobile apps
* Admin dashboards

---
