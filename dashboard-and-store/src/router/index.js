import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/pages/HomePage.vue'),
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
    name: 'dashboard',
    component: () => import('@/pages/DashboardPage.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'manager'] },
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (authStore.token && !authStore.user) {
    await authStore.fetchUser()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    if (to.path === '/customer/login') {
      return next()
    }
    return next('/customer/login')
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    if (authStore.isAdminOrManager) {
      return next('/dashboard')
    }
    return next('/')
  }

  if (to.meta.roles && authStore.user) {
    const allowedRoles = to.meta.roles
    if (!allowedRoles.includes(authStore.user.role)) {
      return next('/')
    }
  }

  next()
})

export default router
