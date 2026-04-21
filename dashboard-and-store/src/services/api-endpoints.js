export const API_ENDPOINTS = {
  auth: {
    register: '/auth/register',
    login: '/auth/login',
    logout: '/auth/logout',
    me: '/auth/me',
  },

  users: {
    list: '/users',
    create: '/users',
    single: (id) => `/users/${id}`,
    update: (id) => `/users/${id}`,
    delete: (id) => `/users/${id}`,
    restore: (id) => `/users/${id}/restore`,
    forceDelete: (id) => `/users/${id}/force`,
  },

  roles: {
    list: '/roles',
    create: '/roles',
    single: (id) => `/roles/${id}`,
    assignPermission: (id) => `/roles/${id}/assign-permission`,
  },

  permissions: {
    list: '/permissions',
    create: '/permissions',
  },

  products: {
    list: '/products',
    create: '/products',
    single: (id) => `/products/${id}`,
    update: (id) => `/products/${id}`,
    delete: (id) => `/products/${id}`,
    restore: (id) => `/products/${id}/restore`,
    forceDelete: (id) => `/products/${id}/force`,
    trash: '/trash/products',
    createVariation: (productId) => `/products/${productId}/variations`,
  },

  reviews: {
    list: '/reviews',
    create: '/reviews',
    single: (id) => `/reviews/${id}`,
    update: (id) => `/reviews/${id}`,
    delete: (id) => `/reviews/${id}`,
    updateStatus: (id) => `/reviews/${id}/status`,
  },

  categories: {
    list: '/categories',
    create: '/categories',
    single: (id) => `/categories/${id}`,
    update: (id) => `/categories/${id}`,
    delete: (id) => `/categories/${id}`,
    restore: (id) => `/categories/${id}/restore`,
  },

  brands: {
    list: '/brands',
    create: '/brands',
    single: (id) => `/brands/${id}`,
    update: (id) => `/brands/${id}`,
    delete: (id) => `/brands/${id}`,
  },

  attributes: {
    list: '/attributes',
    create: '/attributes',
    addValue: (id) => `/attributes/${id}/values`,
    variations: '/variations',
    updateVariation: (id) => `/variations/${id}`,
    deleteVariation: (id) => `/variations/${id}`,
  },

  orders: {
    list: '/orders',
    create: '/orders',
    single: (id) => `/orders/${id}`,
    update: (id) => `/orders/${id}`,
    updateStatus: (id) => `/orders/${id}/status`,
    delete: (id) => `/orders/${id}`,
    trash: '/trash/orders',
    restore: (id) => `/trash/orders/${id}/restore`,
  },

  payments: {
    list: '/payments',
    create: '/payments',
    single: (id) => `/payments/${id}`,
    update: (id) => `/payments/${id}`,
    delete: (id) => `/payments/${id}`,
  },

  coupons: {
    list: '/coupons',
    create: '/coupons',
    single: (id) => `/coupons/${id}`,
    update: (id) => `/coupons/${id}`,
    delete: (id) => `/coupons/${id}`,
    apply: '/coupons/apply',
  },

  offers: {
    list: '/offers',
    create: '/offers',
    single: (id) => `/offers/${id}`,
    update: (id) => `/offers/${id}`,
    delete: (id) => `/offers/${id}`,
  },

  analytics: {
    overview: '/analytics/overview',
    revenue: '/analytics/revenue',
    products: '/analytics/products',
  },

  reports: {
    sales: '/reports/sales',
    orders: '/reports/orders',
    customers: '/reports/customers',
  },

  wishlist: {
    list: '/wishlist',
    create: '/wishlist',
    single: (id) => `/wishlist/${id}`,
    delete: (id) => `/wishlist/${id}`,
  },

  addresses: {
    list: '/addresses',
    create: '/addresses',
    single: (id) => `/addresses/${id}`,
    update: (id) => `/addresses/${id}`,
    delete: (id) => `/addresses/${id}`,
  },

  customers: {
    list: '/customers',
    create: '/customers',
    single: (id) => `/customers/${id}`,
    update: (id) => `/customers/${id}`,
    delete: (id) => `/customers/${id}`,
    restore: (id) => `/customers/${id}/restore`,
    forceDelete: (id) => `/customers/${id}/force`,
    bulkSoftDelete: '/customers/bulk-soft-delete',
    bulkActive: '/customers/bulk-active',
    bulkInactive: '/customers/bulk-inactive',
  },

  customerGroups: {
    list: '/customer-groups',
    create: '/customer-groups',
    single: (id) => `/customer-groups/${id}`,
    update: (id) => `/customer-groups/${id}`,
    delete: (id) => `/customer-groups/${id}`,
    addCustomers: (id) => `/customer-groups/${id}/customers`,
    removeCustomers: (id) => `/customer-groups/${id}/customers`,
  },

  activityLogs: {
    list: '/activity-logs',
  },

  audits: {
    list: '/audits',
  },

  inventory: {
    list: '/inventory',
    update: (id) => `/inventory/${id}`,
    bulkUpdate: '/inventory/bulk-update',
    alerts: '/inventory/alerts',
    alertsDismiss: (id) => `/inventory/alerts/${id}/dismiss`,
    skus: '/inventory/skus',
    updateSku: (id) => `/inventory/skus/${id}`,
    generateSku: '/inventory/skus/generate',
  },

  productsInventory: {
    single: (id) => `/products/${id}/inventory`,
    update: (id) => `/products/${id}/inventory`,
  },
}

export default API_ENDPOINTS
