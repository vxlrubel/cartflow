<script setup>
import { reactive, watch, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import DashboardSquire from '@/components/icons/DashboardSquire.vue'
import OrdersIcon from '@/components/icons/OrdersIcon.vue'
import ShoppingCart from '@/components/icons/ShoppingCart.vue'
import AnalyticsIcon from '@/components/icons/AnalyticsIcon.vue'
import InventoryIcon from '@/components/icons/InventoryIcon.vue'
import TargetUser from '@/components/icons/TargetUser.vue'
import CampaignIcon from '@/components/icons/CampaignIcon.vue'
import ReportIcon from '@/components/icons/ReportIcon.vue'
import UserIcon from '@/components/icons/UserIcon.vue'
import SettingsIcon from '@/components/icons/SettingsIcon.vue'
import GalleryIcon from '@/components/icons/GalleryIcon.vue'
import TrashIcon from '@/components/icons/TrashIcon.vue'
import ChevronDown from '@/components/icons/ChevronDown.vue'

const route = useRoute()

const menuItems = [
  {
    category: 'Analytics',
    icon    : AnalyticsIcon,
    children: [
      { label: 'Overview', path: '/dashboard/analytics/overview' },
      { label: 'Sales Reports', path: '/dashboard/analytics/sales' },
      { label: 'Customer Insights', path: '/dashboard/analytics/customers' },
      { label: 'Product Performance', path: '/dashboard/analytics/products' },
    ]
  },
  {
    category: 'Orders',
    icon    : OrdersIcon,
    children: [
      { label: 'All Orders', path: '/dashboard/orders' },
      { label: 'Pending', path: '/dashboard/orders/pending' },
      { label: 'Completed', path: '/dashboard/orders/completed' },
      { label: 'Cancelled', path: '/dashboard/orders/cancelled' },
      { label: 'Returns / Refunds', path: '/dashboard/orders/returns' },
    ]
  },
  {
    category: 'Products',
    icon    : ShoppingCart,
    children: [
      { label: 'All Products', path: '/dashboard/products' },
      { label: 'Add Product', path: '/dashboard/products/create' },
      { label: 'Categories', path: '/dashboard/products/categories' },
      { label: 'Attributes', path: '/dashboard/products/attributes' },
      { label: 'Variations', path: '/dashboard/products/variations' },
      { label: 'Brands', path: '/dashboard/products/brands' },
      { label: 'Reviews', path: '/dashboard/products/reviews' },
    ]
  },
  {
    category: 'Inventory',
    icon    : InventoryIcon,
    children: [
      { label: 'Stock Management', path: '/dashboard/inventory/stock' },
      { label: 'Low Stock Alerts', path: '/dashboard/inventory/alerts' },
      { label: 'SKU Management', path: '/dashboard/inventory/sku' },
    ]
  },
  {
    category: 'Customers',
    icon    : TargetUser,
    children: [
      { label: 'All Customers', path: '/dashboard/customers' },
      { label: 'Groups / Segments', path: '/dashboard/customers/groups' },
      { label: 'Activity Logs', path: '/dashboard/customers/activity' },
    ]
  },
  {
    category: 'Marketing',
    icon    : CampaignIcon,
    children: [
      { label: 'Product Coupons', path: '/dashboard/marketing/coupons/product' },
      { label: 'Category Coupons', path: '/dashboard/marketing/coupons/category' },
      { label: 'Cart Coupons', path: '/dashboard/marketing/coupons/cart' },
      { label: 'Usage Tracking', path: '/dashboard/marketing/coupons/usage' },
      { label: 'Black Friday Deals', path: '/dashboard/marketing/offers/black-friday' },
      { label: 'Buy X Get Y', path: '/dashboard/marketing/offers/buy-x-get-y' },
      { label: 'Flash Sale', path: '/dashboard/marketing/offers/flash-sale' },
      { label: 'Discount Rules', path: '/dashboard/marketing/offers/discount-rules' },
      { label: 'Email Campaigns', path: '/dashboard/marketing/campaigns' },
    ]
  },
  {
    category: 'Reports',
    icon    : ReportIcon,
    children: [
      { label: 'Daily / Weekly / Monthly / Yearly', path: '/dashboard/reports/period' },
      { label: 'Revenue', path: '/dashboard/reports/revenue' },
      { label: 'Orders', path: '/dashboard/reports/orders' },
      { label: 'Taxes', path: '/dashboard/reports/taxes' },
      { label: 'Export (CSV/PDF)', path: '/dashboard/reports/export' },
    ]
  },
  {
    category: 'Users & Roles',
    icon    : UserIcon,
    children: [
      { label: 'Users', path: '/dashboard/users' },
      { label: 'Roles', path: '/dashboard/users/roles' },
      { label: 'Permissions', path: '/dashboard/users/permissions' },
    ]
  },
  {
    category: 'Settings',
    icon    : SettingsIcon,
    children: [
      { label: 'General', path: '/dashboard/settings/general' },
      { label: 'Payment Gateways', path: '/dashboard/settings/payment' },
      { label: 'Shipping Methods', path: '/dashboard/settings/shipping' },
      { label: 'Tax Rules', path: '/dashboard/settings/tax' },
      { label: 'Currency', path: '/dashboard/settings/currency' },
      { label: 'Store Config', path: '/dashboard/settings/store' },
    ]
  },
  {
    category: 'Media',
    icon    : GalleryIcon,
    children: [
      { label: 'Uploads', path: '/dashboard/media' }
    ]
  },
  {
    category: 'Trash',
    icon    : TrashIcon,
    children: [
      { label: 'Products', path: '/dashboard/trash/products' },
      { label: 'Orders', path: '/dashboard/trash/orders' },
      { label: 'Customers', path: '/dashboard/trash/customers' },
    ]
  },
]

const expandedCategories = reactive(
  Object.fromEntries(menuItems.map(item => [item.category, false]))
)


const toggleCategory = (category) => {
  expandedCategories[category] = !expandedCategories[category]
}

const isActive = (path) => {
  return route.path === path
}

const isParentActive = (children) => {
  return children.some((item) => item.path === route.path)
}

watch(
  () => route.path,
  () => {
    for (const { category, children } of menuItems) {
      if (children.some((item) => item.path === route.path)) {
        expandedCategories[category] = true
        break
      }
    }
  },
  { immediate: true },
)
</script>

<template>

  <div>
    <div class="mb-2">
      <router-link to="/dashboard"
        :class="
          isActive('/dashboard') ? 'text-theme-600 bg-theme-50' : 'text-gray-700 hover:bg-gray-50'
        "
        class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium cursor-pointer">
        <span class="flex items-center">
          <span>
            <DashboardSquire size="20" class="mr-1"/>
          </span>
          <span>Dashboard</span>
        </span>
      </router-link>
    </div>
    <div v-for="{category, icon, children} in menuItems" :key="category" class="mb-2">
      <button
        @click="toggleCategory(category)"
        class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium cursor-pointer"
        :class="
          isParentActive(children) ? 'text-theme-600 bg-theme-50' : 'text-gray-700 hover:bg-gray-50'
        "
      >
        <span class="flex-1 flex items-center">
          <span>
            <component :is="icon" size="20" class="mr-1"/>
          </span>
          <span>{{ category }}</span>
        </span>
        <ChevronDown :class="['transition-transform duration-300 ease-in-out text-gray-600', expandedCategories[category] ? 'rotate-180' : '']" size="16" />
      </button>
      <div class="overflow-hidden transition-all duration-300 ease-in-out"
        :class="expandedCategories[category] ? 'max-h-96 opacity-100 mt-1' : 'max-h-0 opacity-0 mt-0'">
        <router-link
          v-for="item in children"
          :key="item.path"
          :to="item.path"
          class="block pl-8 pr-4 py-2 text-sm border-l-3"
          :class="
            isActive(item.path)
              ? 'text-theme-600 bg-theme-50 border-theme-600 font-medium'
              : 'text-gray-600 hover:bg-gray-50 hover:text-theme-600 border-transparent'
          "
        >
          {{ item.label }}
        </router-link>
      </div>
    </div>
  </div>
</template>
