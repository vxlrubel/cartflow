# 🗄️ Database Schema (MySQL)

## 🔐 Users & RBAC

### users

* id
* name
* email
* password
* role_id
* created_at
* updated_at
* deleted_at

### roles

* id
* name (admin, manager, customer)

### permissions

* id
* name

### role_permission

* role_id
* permission_id

---

## 🛍️ Products

### products

* id
* name
* slug
* description
* price
* sale_price
* category_id
* brand_id
* stock
* sku
* status
* created_at
* updated_at
* deleted_at

### categories

* id
* name
* parent_id
* deleted_at

### brands

* id
* name

### product_images

* id
* product_id
* url

---

## 🎨 Attributes & Variations

### attributes

* id
* name

### attribute_values

* id
* attribute_id
* value

### product_variations

* id
* product_id
* sku
* price
* stock

### variation_attributes

* variation_id
* attribute_value_id

---

## 📦 Orders

### orders

* id
* user_id
* total_amount
* status
* payment_status
* created_at
* deleted_at

### order_items

* id
* order_id
* product_id
* quantity
* price

### payments

* id
* order_id
* method
* transaction_id
* status

---

## 🎟️ Coupons

### coupons

* id
* code
* type (product, category, cart)
* discount_type (fixed, percentage)
* discount_value
* max_usage
* used_count
* expires_at

### coupon_products

* coupon_id
* product_id

### coupon_categories

* coupon_id
* category_id

---

## 🎯 Offers

### offers

* id
* name
* type (bxgy, flash, percentage)
* start_date
* end_date
* status

### offer_rules

* id
* offer_id
* rule_type
* conditions (JSON)

---

## ❤️ Customer

### wishlists

* id
* user_id

### wishlist_items

* wishlist_id
* product_id

### addresses

* id
* user_id
* address
* city
* country

---

## 📊 Analytics

### analytics

* id
* total_sales
* total_orders
* total_customers
* date

---

## 📝 Activity Logs

### activity_logs

* id
* user_id
* action
* description
* subject_type
* subject_id
* created_at

---

## 📜 Audit Trails

### audits

* id
* user_id
* table_name
* record_id
* old_values (JSON)
* new_values (JSON)
* created_at

---

## 🗑️ Soft Deletes

All major tables include:

* deleted_at (nullable timestamp)

---

## 🔗 Relationships Summary

* User → Role (Many to One)
* Product → Category (Many to One)
* Product → Variations (One to Many)
* Order → Items (One to Many)
* Coupon → Product/Category (Many to Many)
* Offer → Rules (One to Many)

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
