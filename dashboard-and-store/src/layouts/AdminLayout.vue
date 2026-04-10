<template>
  <div class="min-h-screen bg-gray-100">
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r transform transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        'lg:translate-x-0'
      ]"
    >
      <div class="flex items-center justify-between h-16 px-4 border-b">
        <router-link to="/dashboard" class="text-xl font-bold text-indigo-600">
          CartFlow Admin
        </router-link>
        <button @click="sidebarOpen = false" class="lg:hidden">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <nav class="overflow-y-auto h-[calc(100vh-4rem)] py-4">
        <div v-for="(items, category) in menuItems" :key="category" class="mb-2">
          <button
            @click="toggleCategory(category)"
            class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
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
              class="block pl-8 pr-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-indigo-600"
              :class="{ 'text-indigo-600 bg-indigo-50': isActive(item.path) }"
            >
              {{ item.label }}
            </router-link>
          </div>
        </div>
      </nav>
    </aside>

    <div class="lg:pl-64">
      <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex items-center">
              <button @click="sidebarOpen = true" class="lg:hidden p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
            </div>
            <div class="flex items-center space-x-4">
              <span class="text-gray-700">{{ authStore.user?.name }}</span>
              <span class="text-xs px-2 py-1 bg-indigo-100 text-indigo-600 rounded">{{ authStore.user?.role }}</span>
              <button
                @click="handleLogout"
                class="text-gray-600 hover:text-gray-900 text-sm font-medium"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>
      <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)

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
  return route.path.startsWith(path.split('/').slice(0, 3).join('/'))
}

const handleLogout = async () => {
  await authStore.logout()
}
</script>