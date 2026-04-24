# 🔌 API_ENDPOINT.md — E-Commerce API Endpoints

## 📌 Base URL

```
/api/v1
```

---

## 🔐 Authentication

| Method | Endpoint       | Description      |
| ------ | -------------- | ---------------- |
| POST   | /auth/register | Register user    |
| POST   | /auth/login    | Login            |
| POST   | /auth/logout   | Logout           |
| GET    | /auth/me       | Get current user |

---

## 👤 Users (Admin Only)

| Method | Endpoint            | Description      |
| ------ | ------------------- | ---------------- |
| GET    | /users              | List users       |
| POST   | /users              | Create user      |
| GET    | /users/{id}         | User details     |
| PUT    | /users/{id}         | Update user      |
| DELETE | /users/{id}         | Soft delete      |
| POST   | /users/{id}/restore | Restore user     |
| DELETE | /users/{id}/force   | Permanent delete |

---

## 🔐 Roles & Permissions

| Method | Endpoint                      | Description       |
| ------ | ----------------------------- | ----------------- |
| GET    | /roles                        | List roles        |
| POST   | /roles                        | Create role       |
| GET    | /permissions                  | List permissions  |
| POST   | /roles/{id}/assign-permission | Assign permission |

---

## 🛍️ Products

| Method | Endpoint               | Description      |
| ------ | ---------------------- | ---------------- |
| GET    | /products              | List products    |
| POST   | /products              | Create product   |
| GET    | /products/{id}         | Product details  |
| PUT    | /products/{id}         | Update           |
| DELETE | /products/{id}         | Soft delete      |
| POST   | /products/{id}/restore | Restore          |
| DELETE | /products/{id}/force   | Permanent delete |

---

## 📂 Categories

| Method | Endpoint                 | Description |
| ------ | ------------------------ | ----------- |
| GET    | /categories              | List        |
| POST   | /categories              | Create      |
| PUT    | /categories/{id}         | Update      |
| DELETE | /categories/{id}         | Soft delete |
| POST   | /categories/{id}/restore | Restore     |

---

## 🏷️ Brands

| Method | Endpoint     | Description |
| ------ | ------------ | ----------- |
| GET    | /brands      | List        |
| POST   | /brands      | Create      |
| PUT    | /brands/{id} | Update      |
| DELETE | /brands/{id} | Delete      |

---

## 🎨 Attributes & Variations

### Attributes

| Method | Endpoint    |
| ------ | ----------- |
| GET    | /attributes |
| POST   | /attributes |

### Attribute Values

| Method | Endpoint                |
| ------ | ----------------------- |
| POST   | /attributes/{id}/values |

### Variations

| Method | Endpoint                  |
| ------ | ------------------------- |
| POST   | /products/{id}/variations |
| PUT    | /variations/{id}          |
| DELETE | /variations/{id}          |

---

## 📦 Orders

| Method | Endpoint            | Description   |
| ------ | ------------------- | ------------- |
| GET    | /orders             | List orders   |
| GET    | /orders/{id}        | Details       |
| PUT    | /orders/{id}/status | Update status |
| POST   | /orders             | Create order  |

---

## 💳 Payments

| Method | Endpoint       |
| ------ | -------------- |
| POST   | /payments      |
| GET    | /payments/{id} |

---

## 🎟️ Coupons

| Method | Endpoint       |
| ------ | -------------- |
| GET    | /coupons       |
| POST   | /coupons       |
| GET    | /coupons/{id}  |
| PUT    | /coupons/{id}  |
| DELETE | /coupons/{id}  |
| POST   | /coupons/apply |
| GET    | /trash/coupons |
| POST   | /coupons/{id}/restore |
| DELETE | /coupons/{id}/force |
| GET    | /coupons/usage |

---

## 🎯 Offers / Promotions

| Method | Endpoint     |
| ------ | ------------ |
| GET    | /offers      |
| POST   | /offers      |
| GET    | /offers/{id} |
| PUT    | /offers/{id} |
| DELETE | /offers/{id} |
| GET    | /trash/offers |
| POST   | /offers/{id}/restore |
| DELETE | /offers/{id}/force |

---

## 📧 Email Campaigns

| Method | Endpoint                    |
| ------ | ------------------------- |
| GET    | /email-campaigns           |
| POST   | /email-campaigns           |
| GET    | /email-campaigns/{id}    |
| PUT    | /email-campaigns/{id}    |
| DELETE | /email-campaigns/{id}    |
| POST   | /email-campaigns/{id}/send |
| GET    | /trash/email-campaigns  |
| POST   | /email-campaigns/{id}/restore |
| DELETE | /email-campaigns/{id}/force |

---

## 📊 Reports & Analytics

### Reports

| Method | Endpoint                    |
| ------ | --------------------------- |
| GET    | /reports/sales?type=daily   |
| GET    | /reports/sales?type=monthly |
| GET    | /reports/orders             |
| GET    | /reports/customers          |

### Dashboard Analytics

| Method | Endpoint            |
| ------ | ------------------- |
| GET    | /analytics/overview |
| GET    | /analytics/revenue  |
| GET    | /analytics/products |

---

## ❤️ Wishlist

| Method | Endpoint       |
| ------ | -------------- |
| GET    | /wishlist      |
| POST   | /wishlist      |
| DELETE | /wishlist/{id} |

---

## 📍 Addresses

| Method | Endpoint        |
| ------ | --------------- |
| GET    | /addresses      |
| POST   | /addresses      |
| PUT    | /addresses/{id} |
| DELETE | /addresses/{id} |

---

## 🗑️ Trash (Soft Deletes)

### Products

| Method | Endpoint                     |
| ------ | ---------------------------- |
| GET    | /trash/products              |
| POST   | /trash/products/{id}/restore |
| DELETE | /trash/products/{id}/force   |

### Orders

| Method | Endpoint                   |
| ------ | -------------------------- |
| GET    | /trash/orders              |
| POST   | /trash/orders/{id}/restore |

---

## 📝 Activity Logs

| Method | Endpoint       |
| ------ | -------------- |
| GET    | /activity-logs |

---

## 📜 Audit Trails

| Method | Endpoint |
| ------ | -------- |
| GET    | /audits  |

---

## ⚙️ Settings

| Method | Endpoint  |
| ------ | --------- |
| GET    | /settings |
| PUT    | /settings |

---

## 📁 Media Upload

| Method | Endpoint |
| ------ | -------- |
| POST   | /upload  |

---

## 🔍 Search (Elasticsearch)

| Method | Endpoint                  |
| ------ | ------------------------- |
| GET    | /search?q=keyword         |
| GET    | /search/suggestions?q=key |

---

## ⚡ System Endpoints (Advanced)

### Queue Monitoring

| Method | Endpoint    |
| ------ | ----------- |
| GET    | /queue/jobs |

### Cache Control

| Method | Endpoint     |
| ------ | ------------ |
| POST   | /cache/clear |

---

## 🧠 Notes

* All endpoints must be protected using:

  * Sanctum auth
  * Role middleware
* Use:

  * Pagination → `?page=1`
  * Filtering → `?category=1`
  * Search → `?q=phone`

---

## 🏁 Goal

This API is designed to power:

* Vue Admin Dashboard
* Vue Storefront
* Mobile Apps

---
