import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminLayout from '@/layouts/AdminLayout.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/pages/HomePage.vue'),
    meta: { title: 'Home' },
  },
  {
    path: '/products',
    name: 'products',
    component: () => import('@/pages/ProductsPage.vue'),
    meta: { title: 'Products' },
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/LoginPage.vue'),
    meta: { title: 'Login', guestOnly: true },
  },
  {
    path: '/customer/login',
    name: 'customer-login',
    component: () => import('@/pages/CustomerLoginPage.vue'),
    meta: { title: 'Customer Login', guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/RegisterPage.vue'),
    meta: { title: 'Register', guestOnly: true },
  },
  {
    path: '/dashboard',
    component: AdminLayout,
    meta: { requiresAuth: true, roles: ['admin', 'manager'], title: 'Dashboard' },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/pages/admin/DashboardPage.vue'),
        meta: { title: 'Dashboard' },
      },
      {
        path: 'structure/list',
        name: 'dashboard-structure',
        component: () => import('@/pages/admin/structure/ListPage.vue'),
        meta: { title: 'List Structure' },
      },
      {
        path: 'analytics/overview',
        name: 'analytics-overview',
        component: () => import('@/pages/admin/analytics/OverviewPage.vue'),
        meta: { title: 'Analytics Overview' },
      },
      {
        path: 'analytics/sales',
        name: 'analytics-sales',
        component: () => import('@/pages/admin/analytics/SalesPage.vue'),
        meta: { title: 'Sales Report' },
      },
      {
        path: 'analytics/customers',
        name: 'analytics-customers',
        component: () => import('@/pages/admin/analytics/CustomersPage.vue'),
        meta: { title: 'Customer Insights' },
      },
      {
        path: 'analytics/products',
        name: 'analytics-products',
        component: () => import('@/pages/admin/analytics/ProductsPage.vue'),
        meta: { title: 'Product Performance' },
      },
      {
        path: 'orders',
        name: 'orders',
        component: () => import('@/pages/admin/orders/IndexPage.vue'),
        meta: { title: 'Orders' },
      },
      {
        path: 'orders/pending',
        name: 'orders-pending',
        component: () => import('@/pages/admin/orders/PendingPage.vue'),
        meta: { title: 'Pending Orders' },
      },
      {
        path: 'orders/completed',
        name: 'orders-completed',
        component: () => import('@/pages/admin/orders/CompletedPage.vue'),
        meta: { title: 'Completed Orders' },
      },
      {
        path: 'orders/cancelled',
        name: 'orders-cancelled',
        component: () => import('@/pages/admin/orders/CancelledPage.vue'),
        meta: { title: 'Cancelled Orders' },
      },
      {
        path: 'orders/returns',
        name: 'orders-returns',
        component: () => import('@/pages/admin/orders/ReturnsPage.vue'),
        meta: { title: 'Returns / Refunds' },
      },
      {
        path: 'orders/:id',
        name: 'order-detail',
        component: () => import('@/pages/admin/orders/DetailPage.vue'),
        meta: { title: 'Order Detail' },
      },
      {
        path: 'products',
        name: 'admin-products',
        component: () => import('@/pages/admin/products/IndexPage.vue'),
        meta: { title: 'Products' },
      },
      {
        path: 'products/create',
        name: 'product-create',
        component: () => import('@/pages/admin/products/CreatePage.vue'),
        meta: { title: 'Add Product' },
      },
      {
        path: 'products/edit/:id',
        name: 'product-edit',
        component: () => import('@/pages/admin/products/EditPage.vue'),
        meta: { title: 'Edit Product' },
      },
      {
        path: 'products/categories',
        name: 'product-categories',
        component: () => import('@/pages/admin/products/CategoriesPage.vue'),
        meta: { title: 'Categories' },
      },
      {
        path: 'products/attributes',
        name: 'product-attributes',
        component: () => import('@/pages/admin/products/AttributesPage.vue'),
        meta: { title: 'Attributes' },
      },
      {
        path: 'products/variations',
        name: 'product-variations',
        component: () => import('@/pages/admin/products/VariationsPage.vue'),
        meta: { title: 'Variations' },
      },
      {
        path: 'products/brands',
        name: 'product-brands',
        component: () => import('@/pages/admin/products/BrandsPage.vue'),
        meta: { title: 'Brands' },
      },
      {
        path: 'products/reviews',
        name: 'product-reviews',
        component: () => import('@/pages/admin/products/ReviewsPage.vue'),
        meta: { title: 'Reviews' },
      },
      {
        path: 'inventory/stock',
        name: 'inventory-stock',
        component: () => import('@/pages/admin/inventory/StockPage.vue'),
        meta: { title: 'Stock Management' },
      },
      {
        path: 'inventory/alerts',
        name: 'inventory-alerts',
        component: () => import('@/pages/admin/inventory/AlertsPage.vue'),
        meta: { title: 'Low Stock Alerts' },
      },
      {
        path: 'inventory/sku',
        name: 'inventory-sku',
        component: () => import('@/pages/admin/inventory/SkuPage.vue'),
        meta: { title: 'SKU Management' },
      },
      {
        path: 'customers',
        name: 'customers',
        component: () => import('@/pages/admin/customers/IndexPage.vue'),
        meta: { title: 'Customers' },
      },
      {
        path: 'customers/create',
        name: 'customer-create',
        component: () => import('@/pages/admin/customers/CreatePage.vue'),
        meta: { title: 'Add Customer' },
      },
      {
        path: 'customers/edit/:id',
        name: 'customer-edit',
        component: () => import('@/pages/admin/customers/EditPage.vue'),
        meta: { title: 'Edit Customer' },
      },
      {
        path: 'customers/:id',
        name: 'customer-detail',
        component: () => import('@/pages/admin/customers/DetailPage.vue'),
        meta: { title: 'Customer Detail' },
      },
      {
        path: 'customers/groups',
        name: 'customers-groups',
        component: () => import('@/pages/admin/customers/GroupsPage.vue'),
        meta: { title: 'Customer Groups' },
      },
      {
        path: 'customers/activity',
        name: 'customers-activity',
        component: () => import('@/pages/admin/customers/ActivityPage.vue'),
        meta: { title: 'Activity Logs' },
      },
      {
        path: 'marketing/coupons/product',
        name: 'coupons-product',
        component: () => import('@/pages/admin/marketing/ProductCouponsPage.vue'),
        meta: { title: 'Product Coupons' },
      },
      {
        path: 'marketing/coupons/category',
        name: 'coupons-category',
        component: () => import('@/pages/admin/marketing/CategoryCouponsPage.vue'),
        meta: { title: 'Category Coupons' },
      },
      {
        path: 'marketing/coupons/cart',
        name: 'coupons-cart',
        component: () => import('@/pages/admin/marketing/CartCouponsPage.vue'),
        meta: { title: 'Cart Coupons' },
      },
      {
        path: 'marketing/coupons/usage',
        name: 'coupons-usage',
        component: () => import('@/pages/admin/marketing/UsageTrackingPage.vue'),
        meta: { title: 'Usage Tracking' },
      },
      {
        path: 'marketing/offers/black-friday',
        name: 'offers-black-friday',
        component: () => import('@/pages/admin/marketing/BlackFridayPage.vue'),
        meta: { title: 'Black Friday Deals' },
      },
      {
        path: 'marketing/offers/buy-x-get-y',
        name: 'offers-buy-x-get-y',
        component: () => import('@/pages/admin/marketing/BuyXGetYPage.vue'),
        meta: { title: 'Buy X Get Y' },
      },
      {
        path: 'marketing/offers/flash-sale',
        name: 'offers-flash-sale',
        component: () => import('@/pages/admin/marketing/FlashSalePage.vue'),
        meta: { title: 'Flash Sale' },
      },
      {
        path: 'marketing/offers/discount-rules',
        name: 'offers-discount-rules',
        component: () => import('@/pages/admin/marketing/DiscountRulesPage.vue'),
        meta: { title: 'Discount Rules' },
      },
      {
        path: 'marketing/campaigns',
        name: 'marketing-campaigns',
        component: () => import('@/pages/admin/marketing/CampaignsPage.vue'),
        meta: { title: 'Email Campaigns' },
      },
      {
        path: 'reports/period',
        name: 'reports-period',
        component: () => import('@/pages/admin/reports/PeriodPage.vue'),
        meta: { title: 'Reports' },
      },
      {
        path: 'reports/revenue',
        name: 'reports-revenue',
        component: () => import('@/pages/admin/reports/RevenuePage.vue'),
        meta: { title: 'Revenue Report' },
      },
      {
        path: 'reports/orders',
        name: 'reports-orders',
        component: () => import('@/pages/admin/reports/OrdersPage.vue'),
        meta: { title: 'Orders Report' },
      },
      {
        path: 'reports/taxes',
        name: 'reports-taxes',
        component: () => import('@/pages/admin/reports/TaxesPage.vue'),
        meta: { title: 'Taxes Report' },
      },
      {
        path: 'reports/export',
        name: 'reports-export',
        component: () => import('@/pages/admin/reports/ExportPage.vue'),
        meta: { title: 'Export Report' },
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('@/pages/admin/users/IndexPage.vue'),
        meta: { title: 'Users' },
      },
      {
        path: 'users/roles',
        name: 'users-roles',
        component: () => import('@/pages/admin/users/RolesPage.vue'),
        meta: { title: 'Roles' },
      },
      {
        path: 'users/permissions',
        name: 'users-permissions',
        component: () => import('@/pages/admin/users/PermissionsPage.vue'),
        meta: { title: 'Permissions' },
      },
      {
        path: 'settings/general',
        name: 'settings-general',
        component: () => import('@/pages/admin/settings/GeneralPage.vue'),
        meta: { title: 'General Settings' },
      },
      {
        path: 'settings/payment',
        name: 'settings-payment',
        component: () => import('@/pages/admin/settings/PaymentPage.vue'),
        meta: { title: 'Payment Gateways' },
      },
      {
        path: 'settings/shipping',
        name: 'settings-shipping',
        component: () => import('@/pages/admin/settings/ShippingPage.vue'),
        meta: { title: 'Shipping Methods' },
      },
      {
        path: 'settings/tax',
        name: 'settings-tax',
        component: () => import('@/pages/admin/settings/TaxPage.vue'),
        meta: { title: 'Tax Rules' },
      },
      {
        path: 'settings/currency',
        name: 'settings-currency',
        component: () => import('@/pages/admin/settings/CurrencyPage.vue'),
        meta: { title: 'Currency' },
      },
      {
        path: 'settings/store',
        name: 'settings-store',
        component: () => import('@/pages/admin/settings/StoreConfigPage.vue'),
        meta: { title: 'Store Config' },
      },
      {
        path: 'media',
        name: 'media',
        component: () => import('@/pages/admin/media/UploadsPage.vue'),
        meta: { title: 'Media Uploads' },
      },
      {
        path: 'trash/products',
        name: 'trash-products',
        component: () => import('@/pages/admin/trash/ProductsPage.vue'),
        meta: { title: 'Trash Products' },
      },
      {
        path: 'trash/orders',
        name: 'trash-orders',
        component: () => import('@/pages/admin/trash/OrdersPage.vue'),
        meta: { title: 'Trash Orders' },
      },
      {
        path: 'trash/customers',
        name: 'trash-customers',
        component: () => import('@/pages/admin/trash/CustomersPage.vue'),
        meta: { title: 'Trash Customers' },
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

router.afterEach((to) => {
  const pageTitle = to.meta.title || 'CartFlow'
  document.title = `${pageTitle} | CartFlow`
})

export default router
