import { defineStore } from 'pinia'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdminOrManager: (state) => state.user && ['admin', 'manager'].includes(state.user.role),
    isCustomer: (state) => state.user && state.user.role === 'customer',
    userRole: (state) => state.user?.role || null,
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post(API_ENDPOINTS.auth.login, credentials)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post(API_ENDPOINTS.auth.register, userData)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.post(API_ENDPOINTS.auth.logout)
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.clearAuth()
        router.push('/')
      }
    },

    async fetchUser() {
      if (!this.token) return null
      try {
        const response = await api.get(API_ENDPOINTS.auth.me)
        this.user = response.data
        return this.user
      } catch (error) {
        this.clearAuth()
        return null
      }
    },

    clearAuth() {
      this.user = null
      this.token = null
      this.error = null
      localStorage.removeItem('token')
    },

    redirectBasedOnRole() {
      if (this.isAdminOrManager) {
        router.push('/dashboard')
      } else if (this.isCustomer) {
        router.push('/')
      }
    },
  },
})
