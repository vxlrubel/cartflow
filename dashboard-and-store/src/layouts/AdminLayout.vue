<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import MenuItems from '@/components/admin/MenuItems.vue'

const authStore = useAuthStore()
const sidebarOpen = ref(false)
const currentTime = ref('')

const updateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('en-US', {
    hour12: true,
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

let timeInterval
onMounted(() => {
  updateTime()
  timeInterval = setInterval(updateTime, 1000)
})

onUnmounted(() => {
  clearInterval(timeInterval)
})

const handleLogout = async () => {
  await authStore.logout()
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-neutral-200 transform transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        'lg:translate-x-0'
      ]"
    >
      <div class="flex items-center justify-between h-16 px-4 border-b border-neutral-200">
        <router-link to="/dashboard" class="text-xl font-bold text-indigo-600">
          CartFlow Admin
        </router-link>
        <button @click="sidebarOpen = false" class="lg:hidden">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <nav class="overflow-y-auto h-[calc(100vh-4rem)] py-4">
        <MenuItems/>
      </nav>
    </aside>

    <div class="lg:pl-64">
      <header class="bg-white border-b border-neutral-200">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex items-center">
              <button @click="sidebarOpen = true" class="lg:hidden p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
              <span class="text-sm font-medium text-gray-700">{{ currentTime }}</span>
            </div>
            <div class="flex items-center space-x-4">
              <span class="text-gray-700">{{ authStore.user?.name }}</span>
              <span class="text-xs px-2 py-1 bg-indigo-100 text-indigo-600 rounded">{{ authStore.user?.role }}</span>
              <button
                @click="handleLogout"
                class="text-gray-600 hover:text-gray-900 text-sm font-medium"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>
      <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <router-view />
      </main>
    </div>
  </div>
</template>
