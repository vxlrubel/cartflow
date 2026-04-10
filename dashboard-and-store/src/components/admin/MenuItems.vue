
<script setup>
import { reactive, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const expandedCategories = reactive({
  Analytics: false,
  Orders: false,
  Products: false,
  Inventory: false,
  Customers: false,
  Marketing: false,
  Reports: false,
  'Users & Roles': false,
  Settings: false,
  Media: false,
  Trash: false,
})

const menuItems = {
  Analytics: [
    { label: 'Overview', path: '/dashboard/analytics/overview' },
    { label: 'Sales Reports', path: '/dashboard/analytics/sales' },
    { label: 'Customer Insights', path: '/dashboard/analytics/customers' },
    { label: 'Product Performance', path: '/dashboard/analytics/products' },
  ],
  Orders: [
    { label: 'All Orders', path: '/dashboard/orders' },
    { label: 'Pending', path: '/dashboard/orders/pending' },
    { label: 'Completed', path: '/dashboard/orders/completed' },
    { label: 'Cancelled', path: '/dashboard/orders/cancelled' },
    { label: 'Returns / Refunds', path: '/dashboard/orders/returns' },
  ],
  Products: [
    { label: 'All Products', path: '/dashboard/products' },
    { label: 'Add Product', path: '/dashboard/products/create' },
    { label: 'Categories', path: '/dashboard/products/categories' },
    { label: 'Attributes', path: '/dashboard/products/attributes' },
    { label: 'Variations', path: '/dashboard/products/variations' },
    { label: 'Brands', path: '/dashboard/products/brands' },
    { label: 'Reviews', path: '/dashboard/products/reviews' },
  ],
  Inventory: [
    { label: 'Stock Management', path: '/dashboard/inventory/stock' },
    { label: 'Low Stock Alerts', path: '/dashboard/inventory/alerts' },
    { label: 'SKU Management', path: '/dashboard/inventory/sku' },
  ],
  Customers: [
    { label: 'All Customers', path: '/dashboard/customers' },
    { label: 'Groups / Segments', path: '/dashboard/customers/groups' },
    { label: 'Activity Logs', path: '/dashboard/customers/activity' },
  ],
  Marketing: [
    { label: 'Product Coupons', path: '/dashboard/marketing/coupons/product' },
    { label: 'Category Coupons', path: '/dashboard/marketing/coupons/category' },
    { label: 'Cart Coupons', path: '/dashboard/marketing/coupons/cart' },
    { label: 'Usage Tracking', path: '/dashboard/marketing/coupons/usage' },
    { label: 'Black Friday Deals', path: '/dashboard/marketing/offers/black-friday' },
    { label: 'Buy X Get Y', path: '/dashboard/marketing/offers/buy-x-get-y' },
    { label: 'Flash Sale', path: '/dashboard/marketing/offers/flash-sale' },
    { label: 'Discount Rules', path: '/dashboard/marketing/offers/discount-rules' },
    { label: 'Email Campaigns', path: '/dashboard/marketing/campaigns' },
  ],
  Reports: [
    { label: 'Daily / Weekly / Monthly / Yearly', path: '/dashboard/reports/period' },
    { label: 'Revenue', path: '/dashboard/reports/revenue' },
    { label: 'Orders', path: '/dashboard/reports/orders' },
    { label: 'Taxes', path: '/dashboard/reports/taxes' },
    { label: 'Export (CSV/PDF)', path: '/dashboard/reports/export' },
  ],
  'Users & Roles': [
    { label: 'Users', path: '/dashboard/users' },
    { label: 'Roles', path: '/dashboard/users/roles' },
    { label: 'Permissions', path: '/dashboard/users/permissions' },
  ],
  Settings: [
    { label: 'General', path: '/dashboard/settings/general' },
    { label: 'Payment Gateways', path: '/dashboard/settings/payment' },
    { label: 'Shipping Methods', path: '/dashboard/settings/shipping' },
    { label: 'Tax Rules', path: '/dashboard/settings/tax' },
    { label: 'Currency', path: '/dashboard/settings/currency' },
    { label: 'Store Config', path: '/dashboard/settings/store' },
  ],
  Media: [
    { label: 'Uploads', path: '/dashboard/media' },
  ],
  Trash: [
    { label: 'Products', path: '/dashboard/trash/products' },
    { label: 'Orders', path: '/dashboard/trash/orders' },
    { label: 'Customers', path: '/dashboard/trash/customers' },
  ],
}

const toggleCategory = (category) => {
  expandedCategories[category] = !expandedCategories[category]
}

const isActive = (path) => {
  return route.path === path
}

const isParentActive = (items) => {
  return items.some(item => item.path === route.path)
}

watch(
  () => route.path,
  () => {
    for (const [category, items] of Object.entries(menuItems)) {
      if (items.some(item => item.path === route.path)) {
        expandedCategories[category] = true
        break
      }
    }
  },
  { immediate: true }
)
</script>

<template>
  <div v-for="(items, category) in menuItems" :key="category" class="mb-2">
    <button
      @click="toggleCategory(category)"
      class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium"
      :class="isParentActive(items) ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50'"
    >
      <span>{{ category }}</span>
      <svg
        :class="['w-4 h-4 transition-transform', expandedCategories[category] ? 'rotate-180' : '']"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <div v-show="expandedCategories[category]" class="mt-1">
      <router-link
        v-for="item in items"
        :key="item.path"
        :to="item.path"
        class="block pl-8 pr-4 py-2 text-sm border-l-2"
        :class="isActive(item.path)
          ? 'text-indigo-600 bg-indigo-50 border-indigo-600 font-medium'
          : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600 border-transparent'"
      >
        {{ item.label }}
      </router-link>
    </div>
  </div>
</template>

<style scoped>

</style>
