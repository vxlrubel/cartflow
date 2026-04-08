<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-bold text-gray-900">Admin Dashboard</h1>
          </div>
          <div class="flex items-center">
            <span class="text-gray-600 mr-4">{{ authStore.user?.name }}</span>
            <button
              @click="handleLogout"
              class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Welcome, {{ authStore.user?.name }}!</h2>
        <p class="text-gray-600">Role: <span class="font-semibold">{{ authStore.user?.role }}</span></p>
        <p class="text-gray-600 mt-2">This is your admin dashboard.</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
}
</script>
