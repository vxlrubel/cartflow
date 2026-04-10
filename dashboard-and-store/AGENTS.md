# Project Overview

This project is a modern Vue 3 application built using Vite, designed for scalability and maintainability. It supports role-based dashboards for admin, manager, and customer users.

## Frontend
- Vue 3 (Composition API)
- Vue Router
- EPinia (State Management)

## Build Tool
- Vite

## Testing
- Vitest (Unit Testing)
- Cypress (E2E Testing)
- Vue Test Utils
- jsdom

## Code Quality
- ESLint
- Prettier
- Oxlint


# Vue Component Structure (STRICT RULE)

All Vue Single File Components (SFC) MUST follow this exact order:

## Required Order
```
01. <script setup>
02. <template>
03. <style scoped> (optional)
```

## Standard Component Template

```vue
<script setup>
// imports
// props
// emits
// state
// computed
// methods
// lifecycle hooks
</script>

<template>
  <!-- UI Markup -->
</template>

<style scoped>
/* Styles (only if needed) */
</style>
```

# Rules

```
- ALWAYS use <script setup>
- NEVER use Options API (data, methods, etc.)
- <script> MUST be at the TOP
- <template> MUST be in the MIDDLE
- <style> MUST be at the BOTTOM
- Use scoped styles by default
- Do NOT include <style> if not needed
```

# Project Structure

src/
│
├── assets/            # Static files (images, styles)
├── components/        # Reusable components
├── layouts/           # Layouts (Admin, Auth, Store)
├── pages/             # Route-based pages
├── router/            # Route definitions
├── stores/            # Pinia stores
├── services/          # API services
├── composables/       # Reusable logic (hooks)
├── utils/             # Helper functions
└── App.vue            # Root component


# API ENDPOINTS

Read the api end point from the below source.
src/services/api-endpoints.js

# API URL
Read the **.env** file for api url and use the api url form this file.

# 1. DASHBOARD MENU (ROLE-BASED)

If the user’s role is admin or manager, they are allowed to access the main dashboard. If the role is customer, they will have a separate “My Account” dashboard on the frontend store.

## Admin (Full Access)

Dashboard
├── Analytics
│    ├── Overview
│    ├── Sales Reports
│    ├── Customer Insights
│    └── Product Performance
│
├── Orders
│    ├── All Orders
│    ├── Pending
│    ├── Completed
│    ├── Cancelled
│    └── Returns / Refunds
│
├── Products
│    ├── All Products
│    ├── Add Product
│    ├── Categories
│    ├── Attributes (Size, Color)
│    ├── Variations
│    ├── Brands
│    └── Reviews
│
├── Inventory
│    ├── Stock Management
│    ├── Low Stock Alerts
│    └── SKU Management
│
├── Customers
│    ├── All Customers
│    ├── Groups / Segments
│    └── Activity Logs
│
├── Marketing
│    ├── Coupons
│    │    ├── Product Coupons
│    │    ├── Category Coupons
│    │    ├── Cart Coupons
│    │    └── Usage Tracking
│    │
│    ├── Offers / Promotions
│    │    ├── Black Friday Deals
│    │    ├── Buy X Get Y
│    │    ├── Flash Sale
│    │    └── Discount Rules
│    │
│    └── Email Campaigns
│
├── Reports
│    ├── Daily / Weekly / Monthly / Yearly
│    ├── Revenue
│    ├── Orders
│    ├── Taxes
│    └── Export (CSV/PDF)
│
├── Users & Roles (RBAC)
│    ├── Users
│    ├── Roles (Admin, Manager, Customer)
│    └── Permissions
│
├── Settings
│    ├── General
│    ├── Payment Gateways
│    ├── Shipping Methods
│    ├── Tax Rules
│    ├── Currency
│    └── Store Config
│
├── Media
│    └── Uploads
│
├── Trash (Soft Deletes)
│    ├── Products
│    ├── Orders
│    ├── Customers
│    └── Restore / Permanently Delete

## Manager (Limited Access)

Dashboard
├── Analytics (Limited)
├── Orders (View + Update Status)
├── Products (CRUD ✅)
├── Inventory
├── Coupons (Limited)
└── Reports (View only)

## Customer

My Account
├── Profile
├── Orders
├── Wishlist
└── Addresses