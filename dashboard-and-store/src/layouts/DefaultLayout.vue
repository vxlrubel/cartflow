<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm">
      <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex-shrink-0">
            <router-link to="/" class="text-2xl font-bold text-theme-600"> CartFlow </router-link>
          </div>
          <div class="flex items-center space-x-4">
            <template v-if="authStore.isAuthenticated">
              <router-link
                to="/dashboard"
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                Dashboard
              </router-link>
            </template>
            <router-link
              to="/products"
              class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            >
              Products
            </router-link>
            <template v-if="!authStore.isAuthenticated">
              <router-link
                to="/login"
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                Login
              </router-link>
              <router-link
                to="/customer/login"
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                Customer Login
              </router-link>
              <router-link
                to="/register"
                class="bg-theme-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-theme-700"
              >
                Register
              </router-link>
            </template>
            <template v-else>
              <button
                @click="handleLogout"
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                Logout
              </button>
            </template>
          </div>
        </div>
      </nav>
    </header>
    <main>
      <slot />
    </main>
    <footer class="bg-white border-t mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <p class="text-center text-gray-500 text-sm">&copy; 2026 CartFlow. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const handleLogout = async () => {
  await authStore.logout()
}
</script>
