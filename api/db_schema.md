# 🗄️ Database Schema (MySQL)

## 🔐 Users & RBAC

### users
* id (PK)
* name
* email (unique)
* password
* role_id (FK → roles)
* created_at
* updated_at
* deleted_at (soft delete)

### roles
* id (PK)
* name (admin, manager, customer)
* created_at
* updated_at

### permissions
* id (PK)
* name
* created_at
* updated_at

### role_permission (pivot)
* role_id (FK → roles)
* permission_id (FK → permissions)

---

## 🛍️ Products

### products
* id (PK)
* name
* slug (unique)
* description
* price (decimal)
* sale_price (decimal, nullable)
* category_id (FK → categories)
* brand_id (FK → brands)
* stock (integer)
* sku (unique)
* status (enum: active/inactive)
* low_stock_threshold (integer, default: 10)
* alert_dismissed (boolean, default: false)
* created_at
* updated_at
* deleted_at (soft delete)

### categories
* id (PK)
* name
* parent_id (FK → categories, nullable)
* created_at
* updated_at
* deleted_at (soft delete)

### brands
* id (PK)
* name
* created_at
* updated_at

### product_images
* id (PK)
* product_id (FK → products)
* url
* created_at
* updated_at

### product_categories (pivot)
* product_id (FK → products)
* category_id (FK → categories)

---

## 🎨 Attributes & Variations

### attributes
* id (PK)
* name
* created_at
* updated_at

### attribute_values
* id (PK)
* attribute_id (FK → attributes)
* value
* created_at
* updated_at

### product_variations
* id (PK)
* product_id (FK → products)
* sku (unique)
* price (decimal)
* stock
* created_at
* updated_at

### variation_attributes (pivot)
* id (PK)
* variation_id (FK → product_variations)
* attribute_value_id (FK → attribute_values)

---

## ⭐ Reviews

### reviews
* id (PK)
* product_id (FK → products)
* user_id (FK → users)
* rating (integer, 1-5)
* comment (text, nullable)
* status (enum: pending/approved/rejected)
* created_at
* updated_at

---

## 📦 Orders

### orders
* id (PK)
* user_id (FK → users)
* total_amount (decimal)
* status (enum: pending/paid/shipped/delivered/cancelled)
* payment_status (enum: pending/paid/failed/refunded)
* created_at
* updated_at
* deleted_at (soft delete)

### order_items
* id (PK)
* order_id (FK → orders)
* product_id (FK → products)
* quantity (integer)
* price (decimal)
* created_at
* updated_at

### payments
* id (PK)
* order_id (FK → orders)
* method
* transaction_id
* status (enum: pending/completed/failed)
* created_at
* updated_at

---

## 🎟️ Coupons

### coupons
* id (PK)
* code (unique)
* type (enum: product/category/cart)
* discount_type (enum: fixed/percentage)
* discount_value
* max_usage
* used_count
* expires_at (nullable)
* created_at
* updated_at
* deleted_at (soft delete)

### coupon_products (pivot)
* coupon_id (FK → coupons)
* product_id (FK → products)

### coupon_categories (pivot)
* coupon_id (FK → coupons)
* category_id (FK → categories)

### coupon_usages
* id (PK)
* coupon_id (FK → coupons)
* user_id (FK → users)
* order_id (FK → orders, nullable)
* discount_applied (decimal)
* order_total (decimal)
* created_at
* updated_at

---

## 🎯 Offers

### offers
* id (PK)
* name
* type (enum: black_friday/bxgy/flash/percentage)
* start_date
* end_date
* status (enum: active/inactive)
* created_at
* updated_at
* deleted_at (soft delete)

### offer_rules
* id (PK)
* offer_id (FK → offers)
* rule_type
* conditions (JSON)
* created_at
* updated_at

---

## 📧 Email Campaigns

### email_campaigns
* id (PK)
* name
* subject
* content (text)
* status (enum: draft/scheduled/sent)
* scheduled_at (nullable)
* sent_at (nullable)
* created_by (FK → users)
* created_at
* updated_at
* deleted_at (soft delete)

---

## ❤️ Customer

### wishlists
* id (PK)
* user_id (FK → users)
* created_at
* updated_at

### wishlist_items
* id (PK)
* wishlist_id (FK → wishlists)
* product_id (FK → products)
* created_at
* updated_at

### addresses
* id (PK)
* user_id (FK → users)
* address
* city
* country
* created_at
* updated_at

---

## 📊 Analytics

### analytics
* id (PK)
* total_sales
* total_orders
* total_customers
* date
* created_at
* updated_at

---

## 📝 Activity Logs

### activity_logs
* id (PK)
* user_id (FK → users)
* action
* description
* subject_type
* subject_id
* created_at
* updated_at

---

## 📜 Audit Trails

### audits
* id (PK)
* user_id (FK → users)
* table_name
* record_id
* old_values (JSON)
* new_values (JSON)
* created_at
* updated_at

---

## 🗑️ Soft Deletes

All major tables include:

* deleted_at (nullable timestamp)

---

## 🔗 API Routes Summary

| Feature | Endpoint | Controller |
|---------|----------|-----------|
| Auth | /auth/register, /auth/login, /auth/logout, /auth/me | AuthController |
| Users | /users, /users/{id}, /users/{id}/restore, /users/{id}/force | UserController |
| Roles | /roles, /roles/{id}/assign-permission | RoleController |
| Permissions | /permissions | PermissionController |
| Products | /products, /products/{id}, /trash/products, /products/bulk-* | ProductController |
| Categories | /categories, /categories/{id}/restore | CategoryController |
| Brands | /brands | BrandController |
| Attributes | /attributes, /attributes/{id}/values | AttributeController |
| Variations | /variations, /variations/{id}, /products/{id}/variations | AttributeController |
| Reviews | /reviews, /reviews/{id}, /reviews/{id}/status | ReviewController |
| Orders | /orders, /orders/{id}/status, /orders/{id}/payment-status, /trash/orders | OrderController |
| Payments | /payments | PaymentController |
| Coupons | /coupons, /coupons/{id}, /trash/coupons, /coupons/{id}/restore, /coupons/{id}/force, /coupons/apply, /coupons/usage | CouponController |
| Offers | /offers, /offers/{id}, /trash/offers, /offers/{id}/restore, /offers/{id}/force | OfferController |
| Email Campaigns | /email-campaigns, /email-campaigns/{id}, /email-campaigns/{id}/send, /trash/email-campaigns | EmailCampaignController |
| Analytics | /analytics/overview, /analytics/revenue, /analytics/products | AnalyticsController |
| Reports | /reports/sales, /reports/orders, /reports/customers | AnalyticsController |
| Wishlist | /wishlist | WishlistController |
| Addresses | /addresses | AddressController |
| Activity Logs | /activity-logs | ActivityLogController |
| Audits | /audits | AuditController |
| Inventory | /inventory, /inventory/{id}, /inventory/bulk-update, /inventory/alerts | InventoryController |
| SKU | /inventory/skus, /inventory/skus/{id}, /inventory/skus/generate | InventoryController |

---

## ⚡ Notes

* Use indexes on:
  * `user_id`
  * `product_id`
  * `order_id`
* Use JSON fields for flexible rules
* Use foreign keys for integrity
* Use soft deletes everywhere

---