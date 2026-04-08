const API_VERSION = '/api/v1';

export const API_ENDPOINTS = {
  auth: {
    register: `${API_VERSION}/auth/register`,
    login: `${API_VERSION}/auth/login`,
    logout: `${API_VERSION}/auth/logout`,
    me: `${API_VERSION}/auth/me`,
  },

  users: {
    list: `${API_VERSION}/users`,
    create: `${API_VERSION}/users`,
    single: (id) => `${API_VERSION}/users/${id}`,
    update: (id) => `${API_VERSION}/users/${id}`,
    delete: (id) => `${API_VERSION}/users/${id}`,
    restore: (id) => `${API_VERSION}/users/${id}/restore`,
    forceDelete: (id) => `${API_VERSION}/users/${id}/force`,
  },

  roles: {
    list: `${API_VERSION}/roles`,
    create: `${API_VERSION}/roles`,
    single: (id) => `${API_VERSION}/roles/${id}`,
    assignPermission: (id) => `${API_VERSION}/roles/${id}/assign-permission`,
  },

  permissions: {
    list: `${API_VERSION}/permissions`,
    create: `${API_VERSION}/permissions`,
  },

  products: {
    list: `${API_VERSION}/products`,
    create: `${API_VERSION}/products`,
    single: (id) => `${API_VERSION}/products/${id}`,
    update: (id) => `${API_VERSION}/products/${id}`,
    delete: (id) => `${API_VERSION}/products/${id}`,
    restore: (id) => `${API_VERSION}/products/${id}/restore`,
    forceDelete: (id) => `${API_VERSION}/products/${id}/force`,
    trash: `${API_VERSION}/trash/products`,
    createVariation: (productId) => `${API_VERSION}/products/${productId}/variations`,
  },

  categories: {
    list: `${API_VERSION}/categories`,
    create: `${API_VERSION}/categories`,
    single: (id) => `${API_VERSION}/categories/${id}`,
    update: (id) => `${API_VERSION}/categories/${id}`,
    delete: (id) => `${API_VERSION}/categories/${id}`,
    restore: (id) => `${API_VERSION}/categories/${id}/restore`,
  },

  brands: {
    list: `${API_VERSION}/brands`,
    create: `${API_VERSION}/brands`,
    single: (id) => `${API_VERSION}/brands/${id}`,
    update: (id) => `${API_VERSION}/brands/${id}`,
    delete: (id) => `${API_VERSION}/brands/${id}`,
  },

  attributes: {
    list: `${API_VERSION}/attributes`,
    create: `${API_VERSION}/attributes`,
    addValue: (id) => `${API_VERSION}/attributes/${id}/values`,
    updateVariation: (id) => `${API_VERSION}/variations/${id}`,
    deleteVariation: (id) => `${API_VERSION}/variations/${id}`,
  },

  orders: {
    list: `${API_VERSION}/orders`,
    create: `${API_VERSION}/orders`,
    single: (id) => `${API_VERSION}/orders/${id}`,
    update: (id) => `${API_VERSION}/orders/${id}`,
    updateStatus: (id) => `${API_VERSION}/orders/${id}/status`,
    delete: (id) => `${API_VERSION}/orders/${id}`,
    trash: `${API_VERSION}/trash/orders`,
    restore: (id) => `${API_VERSION}/trash/orders/${id}/restore`,
  },

  payments: {
    list: `${API_VERSION}/payments`,
    create: `${API_VERSION}/payments`,
    single: (id) => `${API_VERSION}/payments/${id}`,
    update: (id) => `${API_VERSION}/payments/${id}`,
    delete: (id) => `${API_VERSION}/payments/${id}`,
  },

  coupons: {
    list: `${API_VERSION}/coupons`,
    create: `${API_VERSION}/coupons`,
    single: (id) => `${API_VERSION}/coupons/${id}`,
    update: (id) => `${API_VERSION}/coupons/${id}`,
    delete: (id) => `${API_VERSION}/coupons/${id}`,
    apply: `${API_VERSION}/coupons/apply`,
  },

  offers: {
    list: `${API_VERSION}/offers`,
    create: `${API_VERSION}/offers`,
    single: (id) => `${API_VERSION}/offers/${id}`,
    update: (id) => `${API_VERSION}/offers/${id}`,
    delete: (id) => `${API_VERSION}/offers/${id}`,
  },

  analytics: {
    overview: `${API_VERSION}/analytics/overview`,
    revenue: `${API_VERSION}/analytics/revenue`,
    products: `${API_VERSION}/analytics/products`,
  },

  reports: {
    sales: `${API_VERSION}/reports/sales`,
    orders: `${API_VERSION}/reports/orders`,
    customers: `${API_VERSION}/reports/customers`,
  },

  wishlist: {
    list: `${API_VERSION}/wishlist`,
    create: `${API_VERSION}/wishlist`,
    single: (id) => `${API_VERSION}/wishlist/${id}`,
    delete: (id) => `${API_VERSION}/wishlist/${id}`,
  },

  addresses: {
    list: `${API_VERSION}/addresses`,
    create: `${API_VERSION}/addresses`,
    single: (id) => `${API_VERSION}/addresses/${id}`,
    update: (id) => `${API_VERSION}/addresses/${id}`,
    delete: (id) => `${API_VERSION}/addresses/${id}`,
  },

  activityLogs: {
    list: `${API_VERSION}/activity-logs`,
  },

  audits: {
    list: `${API_VERSION}/audits`,
  },
};

export default API_ENDPOINTS;
