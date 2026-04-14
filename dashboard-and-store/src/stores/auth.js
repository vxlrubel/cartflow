import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdminOrManager = computed(
    () => user.value && ['admin', 'manager'].includes(user.value.role),
  )
  const isCustomer = computed(() => user.value && user.value.role === 'customer')
  const userRole = computed(() => user.value?.role || null)

  async function login(credentials) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.auth.login, credentials)
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('token', token.value)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function register(userData) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.auth.register, userData)
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('token', token.value)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await api.post(API_ENDPOINTS.auth.logout)
      }
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      clearAuth()
      router.push('/')
    }
  }

  async function fetchUser() {
    if (!token.value) return null
    try {
      const response = await api.get(API_ENDPOINTS.auth.me)
      user.value = response.data
      return user.value
    } catch (err) {
      clearAuth()
      return null
    }
  }

  function clearAuth() {
    user.value = null
    token.value = null
    error.value = null
    localStorage.removeItem('token')
  }

  function redirectBasedOnRole() {
    if (isAdminOrManager.value) {
      router.push('/dashboard')
    } else if (isCustomer.value) {
      router.push('/')
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdminOrManager,
    isCustomer,
    userRole,
    login,
    register,
    logout,
    fetchUser,
    clearAuth,
    redirectBasedOnRole,
  }
})
