import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminLayout from '@/layouts/AdminLayout.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/pages/HomePage.vue'),
  },
  {
    path: '/products',
    name: 'products',
    component: () => import('@/pages/ProductsPage.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/LoginPage.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/customer/login',
    name: 'customer-login',
    component: () => import('@/pages/CustomerLoginPage.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/RegisterPage.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/dashboard',
    component: AdminLayout,
    meta: { requiresAuth: true, roles: ['admin', 'manager'] },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/pages/admin/DashboardPage.vue'),
      },
      {
        path: 'analytics/overview',
        name: 'analytics-overview',
        component: () => import('@/pages/admin/analytics/OverviewPage.vue'),
      },
      {
        path: 'analytics/sales',
        name: 'analytics-sales',
        component: () => import('@/pages/admin/analytics/SalesPage.vue'),
      },
      {
        path: 'analytics/customers',
        name: 'analytics-customers',
        component: () => import('@/pages/admin/analytics/CustomersPage.vue'),
      },
      {
        path: 'analytics/products',
        name: 'analytics-products',
        component: () => import('@/pages/admin/analytics/ProductsPage.vue'),
      },
      {
        path: 'orders',
        name: 'orders',
        component: () => import('@/pages/admin/orders/IndexPage.vue'),
      },
      {
        path: 'orders/pending',
        name: 'orders-pending',
        component: () => import('@/pages/admin/orders/PendingPage.vue'),
      },
      {
        path: 'orders/completed',
        name: 'orders-completed',
        component: () => import('@/pages/admin/orders/CompletedPage.vue'),
      },
      {
        path: 'orders/cancelled',
        name: 'orders-cancelled',
        component: () => import('@/pages/admin/orders/CancelledPage.vue'),
      },
      {
        path: 'orders/returns',
        name: 'orders-returns',
        component: () => import('@/pages/admin/orders/ReturnsPage.vue'),
      },
      {
        path: 'orders/:id',
        name: 'order-detail',
        component: () => import('@/pages/admin/orders/DetailPage.vue'),
      },
      {
        path: 'products',
        name: 'admin-products',
        component: () => import('@/pages/admin/products/IndexPage.vue'),
      },
      {
        path: 'products/create',
        name: 'product-create',
        component: () => import('@/pages/admin/products/CreatePage.vue'),
      },
      {
        path: 'products/edit/:id',
        name: 'product-edit',
        component: () => import('@/pages/admin/products/EditPage.vue'),
      },
      {
        path: 'products/categories',
        name: 'product-categories',
        component: () => import('@/pages/admin/products/CategoriesPage.vue'),
      },
      {
        path: 'products/attributes',
        name: 'product-attributes',
        component: () => import('@/pages/admin/products/AttributesPage.vue'),
      },
      {
        path: 'products/variations',
        name: 'product-variations',
        component: () => import('@/pages/admin/products/VariationsPage.vue'),
      },
      {
        path: 'products/brands',
        name: 'product-brands',
        component: () => import('@/pages/admin/products/BrandsPage.vue'),
      },
      {
        path: 'products/reviews',
        name: 'product-reviews',
        component: () => import('@/pages/admin/products/ReviewsPage.vue'),
      },
      {
        path: 'inventory/stock',
        name: 'inventory-stock',
        component: () => import('@/pages/admin/inventory/StockPage.vue'),
      },
      {
        path: 'inventory/alerts',
        name: 'inventory-alerts',
        component: () => import('@/pages/admin/inventory/AlertsPage.vue'),
      },
      {
        path: 'inventory/sku',
        name: 'inventory-sku',
        component: () => import('@/pages/admin/inventory/SkuPage.vue'),
      },
      {
        path: 'customers',
        name: 'customers',
        component: () => import('@/pages/admin/customers/IndexPage.vue'),
      },
      {
        path: 'customers/create',
        name: 'customer-create',
        component: () => import('@/pages/admin/customers/CreatePage.vue'),
      },
      {
        path: 'customers/edit/:id',
        name: 'customer-edit',
        component: () => import('@/pages/admin/customers/EditPage.vue'),
      },
      {
        path: 'customers/:id',
        name: 'customer-detail',
        component: () => import('@/pages/admin/customers/DetailPage.vue'),
      },
      {
        path: 'customers/groups',
        name: 'customers-groups',
        component: () => import('@/pages/admin/customers/GroupsPage.vue'),
      },
      {
        path: 'customers/activity',
        name: 'customers-activity',
        component: () => import('@/pages/admin/customers/ActivityPage.vue'),
      },
      {
        path: 'marketing/coupons/product',
        name: 'coupons-product',
        component: () => import('@/pages/admin/marketing/ProductCouponsPage.vue'),
      },
      {
        path: 'marketing/coupons/category',
        name: 'coupons-category',
        component: () => import('@/pages/admin/marketing/CategoryCouponsPage.vue'),
      },
      {
        path: 'marketing/coupons/cart',
        name: 'coupons-cart',
        component: () => import('@/pages/admin/marketing/CartCouponsPage.vue'),
      },
      {
        path: 'marketing/coupons/usage',
        name: 'coupons-usage',
        component: () => import('@/pages/admin/marketing/UsageTrackingPage.vue'),
      },
      {
        path: 'marketing/offers/black-friday',
        name: 'offers-black-friday',
        component: () => import('@/pages/admin/marketing/BlackFridayPage.vue'),
      },
      {
        path: 'marketing/offers/buy-x-get-y',
        name: 'offers-buy-x-get-y',
        component: () => import('@/pages/admin/marketing/BuyXGetYPage.vue'),
      },
      {
        path: 'marketing/offers/flash-sale',
        name: 'offers-flash-sale',
        component: () => import('@/pages/admin/marketing/FlashSalePage.vue'),
      },
      {
        path: 'marketing/offers/discount-rules',
        name: 'offers-discount-rules',
        component: () => import('@/pages/admin/marketing/DiscountRulesPage.vue'),
      },
      {
        path: 'marketing/campaigns',
        name: 'marketing-campaigns',
        component: () => import('@/pages/admin/marketing/CampaignsPage.vue'),
      },
      {
        path: 'reports/period',
        name: 'reports-period',
        component: () => import('@/pages/admin/reports/PeriodPage.vue'),
      },
      {
        path: 'reports/revenue',
        name: 'reports-revenue',
        component: () => import('@/pages/admin/reports/RevenuePage.vue'),
      },
      {
        path: 'reports/orders',
        name: 'reports-orders',
        component: () => import('@/pages/admin/reports/OrdersPage.vue'),
      },
      {
        path: 'reports/taxes',
        name: 'reports-taxes',
        component: () => import('@/pages/admin/reports/TaxesPage.vue'),
      },
      {
        path: 'reports/export',
        name: 'reports-export',
        component: () => import('@/pages/admin/reports/ExportPage.vue'),
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('@/pages/admin/users/IndexPage.vue'),
      },
      {
        path: 'users/roles',
        name: 'users-roles',
        component: () => import('@/pages/admin/users/RolesPage.vue'),
      },
      {
        path: 'users/permissions',
        name: 'users-permissions',
        component: () => import('@/pages/admin/users/PermissionsPage.vue'),
      },
      {
        path: 'settings/general',
        name: 'settings-general',
        component: () => import('@/pages/admin/settings/GeneralPage.vue'),
      },
      {
        path: 'settings/payment',
        name: 'settings-payment',
        component: () => import('@/pages/admin/settings/PaymentPage.vue'),
      },
      {
        path: 'settings/shipping',
        name: 'settings-shipping',
        component: () => import('@/pages/admin/settings/ShippingPage.vue'),
      },
      {
        path: 'settings/tax',
        name: 'settings-tax',
        component: () => import('@/pages/admin/settings/TaxPage.vue'),
      },
      {
        path: 'settings/currency',
        name: 'settings-currency',
        component: () => import('@/pages/admin/settings/CurrencyPage.vue'),
      },
      {
        path: 'settings/store',
        name: 'settings-store',
        component: () => import('@/pages/admin/settings/StoreConfigPage.vue'),
      },
      {
        path: 'media',
        name: 'media',
        component: () => import('@/pages/admin/media/UploadsPage.vue'),
      },
      {
        path: 'trash/products',
        name: 'trash-products',
        component: () => import('@/pages/admin/trash/ProductsPage.vue'),
      },
      {
        path: 'trash/orders',
        name: 'trash-orders',
        component: () => import('@/pages/admin/trash/OrdersPage.vue'),
      },
      {
        path: 'trash/customers',
        name: 'trash-customers',
        component: () => import('@/pages/admin/trash/CustomersPage.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { el: '#app', top: 0 }
  },
})

router.beforeEach(async (to, _from) => {
  const authStore = useAuthStore()

  if (authStore.token && !authStore.user) {
    await authStore.fetchUser()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    if (to.path === '/customer/login') {
      return true
    }
    return '/customer/login'
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    if (authStore.isAdminOrManager) {
      return '/dashboard'
    }
    return '/'
  }

  if (to.meta.roles && authStore.user) {
    const allowedRoles = to.meta.roles
    if (!allowedRoles.includes(authStore.user.role)) {
      return '/'
    }
  }

  return true
})

export default router
