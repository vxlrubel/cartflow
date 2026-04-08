import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

const { mockPush, mockApiPost, mockApiGet } = vi.hoisted(() => ({
  mockPush: vi.fn(),
  mockApiPost: vi.fn(),
  mockApiGet: vi.fn(),
}))

vi.mock('@/services/api', () => ({
  default: {
    post: mockApiPost,
    get: mockApiGet,
  },
}))

vi.mock('vue-router', () => ({
  createRouter: vi.fn(() => ({
    push: mockPush,
    install: vi.fn(),
    beforeEach: vi.fn(),
  })),
  createWebHistory: vi.fn(() => ({})),
  default: {
    push: mockPush,
  },
}))

import { useAuthStore } from '../auth'

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    vi.clearAllMocks()
  })

  describe('initial state', () => {
    it('should have null user initially', () => {
      const store = useAuthStore()
      expect(store.user).toBeNull()
    })

    it('should have null token initially when no localStorage', () => {
      const store = useAuthStore()
      expect(store.token).toBeNull()
    })

    it('should have false loading initially', () => {
      const store = useAuthStore()
      expect(store.loading).toBeFalsy()
    })

    it('should have null error initially', () => {
      const store = useAuthStore()
      expect(store.error).toBeNull()
    })
  })

  describe('getters', () => {
    it('isAuthenticated should return false when no token', () => {
      const store = useAuthStore()
      expect(store.isAuthenticated).toBeFalsy()
    })

    it('isAuthenticated should return true when token exists', () => {
      const store = useAuthStore()
      store.token = 'fake-token'
      expect(store.isAuthenticated).toBeTruthy()
    })

    it('isAdminOrManager should return true for admin role', () => {
      const store = useAuthStore()
      store.user = { role: 'admin' }
      expect(store.isAdminOrManager).toBeTruthy()
    })

    it('isAdminOrManager should return true for manager role', () => {
      const store = useAuthStore()
      store.user = { role: 'manager' }
      expect(store.isAdminOrManager).toBeTruthy()
    })

    it('isAdminOrManager should return false for customer role', () => {
      const store = useAuthStore()
      store.user = { role: 'customer' }
      expect(store.isAdminOrManager).toBeFalsy()
    })

    it('isCustomer should return true for customer role', () => {
      const store = useAuthStore()
      store.user = { role: 'customer' }
      expect(store.isCustomer).toBeTruthy()
    })

    it('isCustomer should return false for admin role', () => {
      const store = useAuthStore()
      store.user = { role: 'admin' }
      expect(store.isCustomer).toBeFalsy()
    })

    it('userRole should return the user role', () => {
      const store = useAuthStore()
      store.user = { role: 'admin' }
      expect(store.userRole).toBe('admin')
    })

    it('userRole should return null when no user', () => {
      const store = useAuthStore()
      expect(store.userRole).toBeNull()
    })
  })

  describe('login', () => {
    it('should login successfully with valid credentials', async () => {
      const mockResponse = {
        data: {
          user: { id: 1, name: 'Admin User', email: 'admin@test.com', role: 'admin' },
          token: 'fake-jwt-token',
        },
      }
      mockApiPost.mockResolvedValue(mockResponse)

      const store = useAuthStore()
      const result = await store.login({ email: 'admin@test.com', password: 'password' })

      expect(mockApiPost).toHaveBeenCalledWith('/auth/login', { email: 'admin@test.com', password: 'password' })
      expect(store.user).toEqual(mockResponse.data.user)
      expect(store.token).toBe('fake-jwt-token')
      expect(store.loading).toBeFalsy()
      expect(store.error).toBeNull()
      expect(result).toEqual(mockResponse.data)
    })

    it('should set error on login failure', async () => {
      const errorResponse = { response: { data: { message: 'Invalid credentials' } } }
      mockApiPost.mockRejectedValue(errorResponse)

      const store = useAuthStore()

      await expect(store.login({ email: 'wrong@test.com', password: 'wrong' })).rejects.toThrow()
      expect(store.error).toBe('Invalid credentials')
      expect(store.loading).toBeFalsy()
    })

    it('should set generic error on login failure without message', async () => {
      mockApiPost.mockRejectedValue(new Error('Network error'))

      const store = useAuthStore()

      await expect(store.login({ email: 'test@test.com', password: 'test' })).rejects.toThrow()
      expect(store.error).toBe('Login failed')
    })

    it('should set loading to true during login', async () => {
      const mockResponse = { data: { user: {}, token: 'token' } }
      mockApiPost.mockImplementation(() => new Promise((resolve) => setTimeout(() => resolve(mockResponse), 100)))

      const store = useAuthStore()
      const loginPromise = store.login({ email: 'test@test.com', password: 'password' })

      expect(store.loading).toBeTruthy()
      await loginPromise
      expect(store.loading).toBeFalsy()
    })

    it('should store token in localStorage on successful login', async () => {
      const mockResponse = {
        data: {
          user: { id: 1, name: 'User', role: 'customer' },
          token: 'jwt-token-123',
        },
      }
      mockApiPost.mockResolvedValue(mockResponse)

      const store = useAuthStore()
      await store.login({ email: 'test@test.com', password: 'password' })

      expect(localStorage.getItem('token')).toBe('jwt-token-123')
    })
  })

  describe('register', () => {
    it('should register successfully with valid data', async () => {
      const mockResponse = {
        data: {
          user: { id: 2, name: 'New User', email: 'new@test.com', role: 'customer' },
          token: 'new-token',
        },
      }
      mockApiPost.mockResolvedValue(mockResponse)

      const store = useAuthStore()
      const result = await store.register({
        name: 'New User',
        email: 'new@test.com',
        password: 'password123',
        password_confirmation: 'password123',
      })

      expect(mockApiPost).toHaveBeenCalledWith('/auth/register', {
        name: 'New User',
        email: 'new@test.com',
        password: 'password123',
        password_confirmation: 'password123',
      })
      expect(store.user).toEqual(mockResponse.data.user)
      expect(store.token).toBe('new-token')
    })

    it('should set error on registration failure', async () => {
      const errorResponse = { response: { data: { message: 'Email already exists' } } }
      mockApiPost.mockRejectedValue(errorResponse)

      const store = useAuthStore()

      await expect(store.register({ email: 'existing@test.com', password: 'password' })).rejects.toThrow()
      expect(store.error).toBe('Email already exists')
    })
  })

  describe('logout', () => {
    it('should call logout API when authenticated', async () => {
      mockApiPost.mockResolvedValue({ data: { message: 'Logged out' } })

      const store = useAuthStore()
      store.token = 'some-token'
      await store.logout()

      expect(mockApiPost).toHaveBeenCalledWith('/auth/logout')
    })

    it('should clear auth state on logout', async () => {
      mockApiPost.mockResolvedValue({ data: {} })

      const store = useAuthStore()
      store.token = 'token'
      store.user = { name: 'User' }
      await store.logout()

      expect(store.token).toBeNull()
      expect(store.user).toBeNull()
      expect(store.error).toBeNull()
    })

    it('should remove token from localStorage on logout', async () => {
      mockApiPost.mockResolvedValue({ data: {} })
      localStorage.setItem('token', 'token')

      const store = useAuthStore()
      await store.logout()

      expect(localStorage.getItem('token')).toBeNull()
    })

    it('should not call logout API when not authenticated', async () => {
      const store = useAuthStore()
      await store.logout()

      expect(mockApiPost).not.toHaveBeenCalled()
    })

    it('should redirect to home page after logout', async () => {
      mockApiPost.mockResolvedValue({ data: {} })

      const store = useAuthStore()
      await store.logout()

      expect(mockPush).toHaveBeenCalledWith('/')
    })
  })

  describe('fetchUser', () => {
    it('should fetch user data successfully', async () => {
      const mockUser = { id: 1, name: 'User', email: 'user@test.com', role: 'customer' }
      mockApiGet.mockResolvedValue({ data: mockUser })

      const store = useAuthStore()
      store.token = 'valid-token'
      const result = await store.fetchUser()

      expect(mockApiGet).toHaveBeenCalledWith('/auth/me')
      expect(store.user).toEqual(mockUser)
      expect(result).toEqual(mockUser)
    })

    it('should return null when no token', async () => {
      const store = useAuthStore()
      store.token = null
      const result = await store.fetchUser()

      expect(mockApiGet).not.toHaveBeenCalled()
      expect(result).toBeNull()
    })

    it('should clear auth on fetch user failure', async () => {
      mockApiGet.mockRejectedValue(new Error('Unauthorized'))

      const store = useAuthStore()
      store.token = 'invalid-token'
      await store.fetchUser()

      expect(store.token).toBeNull()
      expect(store.user).toBeNull()
    })
  })

  describe('clearAuth', () => {
    it('should clear all auth state', () => {
      const store = useAuthStore()
      store.token = 'token'
      store.user = { name: 'User' }
      store.error = 'Some error'
      localStorage.setItem('token', 'token')

      store.clearAuth()

      expect(store.token).toBeNull()
      expect(store.user).toBeNull()
      expect(store.error).toBeNull()
      expect(localStorage.getItem('token')).toBeNull()
    })
  })

  describe('redirectBasedOnRole', () => {
    it('should redirect admin to dashboard', () => {
      const store = useAuthStore()
      store.user = { role: 'admin' }
      store.redirectBasedOnRole()

      expect(mockPush).toHaveBeenCalledWith('/dashboard')
    })

    it('should redirect manager to dashboard', () => {
      const store = useAuthStore()
      store.user = { role: 'manager' }
      store.redirectBasedOnRole()

      expect(mockPush).toHaveBeenCalledWith('/dashboard')
    })

    it('should redirect customer to home', () => {
      const store = useAuthStore()
      store.user = { role: 'customer' }
      store.redirectBasedOnRole()

      expect(mockPush).toHaveBeenCalledWith('/')
    })
  })
})
