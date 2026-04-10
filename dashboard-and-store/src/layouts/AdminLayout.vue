<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import MenuItems from '@/components/admin/MenuItems.vue'

const authStore = useAuthStore()
const sidebarOpen = ref(false)
const currentTime = ref('')
const toggleAdminOptions = ref(false)

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
            <div class="flex items-center gap-2 relative h-full">
                <span>{{ authStore.user?.name }}</span>
                <button type="button" class="rounded-full h-7 w-7 bg-neutral-400 cursor-pointer" @click="toggleAdminOptions = !toggleAdminOptions"></button>
                <div class="absolute top-full right-0 w-35 px-3 py-2 bg-gray-200 border border-gray-300 rounded text-xs text-gray-700" v-show="toggleAdminOptions">
                  <a href="#" class="flex items-center gap-1 mb-2 text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                      <path d="M202.87-111.87q-37.78 0-64.39-26.61t-26.61-64.39v-554.26q0-37.78 26.61-64.39t64.39-26.61h356.76l-91 91H202.87v554.26h554.26v-266.28l91-91v357.28q0 37.78-26.61 64.39t-64.39 26.61H202.87ZM480-480ZM356.17-356.17v-175.5l363.42-363.18q13.67-13.67 30.58-20.39 16.92-6.72 34.07-6.72 17.91 0 34.44 6.72 16.54 6.72 30.21 20.39l45.96 46.72q12.91 13.67 19.63 30.23 6.72 16.56 6.72 33.69 0 17.12-6.3 33.74-6.31 16.61-20.05 30.36L531.67-356.17h-175.5Zm481.72-428.07L785-836.89l52.89 52.65Zm-395.5 341.85h52.65l229.85-229.85-26.24-26.33-27.17-26.32-229.09 228.85v53.65Zm256.26-256.18-27.17-26.32 27.17 26.32 26.24 26.33-26.24-26.33Z"/>
                    </svg>
                    Edit Profile
                  </a>
                  <a href="#" class="flex items-center gap-1 mb-2 text-gray-600 hover:text-gray-900 capitalize">
                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                      <path d="M151.87-71.87v-240q0-37.78 26.61-64.39t64.39-26.61h474.26q37.78 0 64.39 26.61t26.61 64.39v240H151.87Zm91-164.78h474.26v-75.22H242.87v75.22ZM480-402.87l-207.65-275.7q0-86.82 60.53-148.19 60.53-61.37 147.12-61.37t147.12 61.37q60.53 61.37 60.53 148.19L480-402.87Zm0-130.65 114.98-154.61q-2.63-45.7-35.72-77.35-33.09-31.65-79.26-31.65t-79.26 31.65q-33.09 31.65-35.72 77.35L480-533.52Zm0-131.81Z"/>
                    </svg>
                    {{ authStore.user?.role }}
                  </a>
                  <router-link to="/dashboard/settings/general" class="flex items-center gap-1 mb-2 text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                      <path d="M363.07-71.87 346.59-202.5q-11.09-4.28-21.04-10.33-9.94-6.04-19.51-12.84l-121.39 51.19L67.48-377.39l104.91-79.44q-.76-6.04-.76-11.58v-23.18q0-5.54.76-11.58L67.48-582.61l117.17-202.43 121.87 50.95q9.57-6.8 19.65-12.73 10.09-5.92 20.42-10.2l16.48-131.11h233.86l16.48 131.11q11.09 4.28 21.04 10.2 9.94 5.93 19.51 12.73l121.39-50.95 117.17 202.43-105.15 79.44q.76 6.04.76 11.58V-480q0 6.04-.12 11.59-.12 5.54-1.64 11.58l105.15 79.44-117.41 202.91-120.63-51.19q-9.57 6.8-19.65 12.84-10.09 6.05-20.42 10.33L596.93-71.87H363.07Zm79.56-91h73.5l14.24-105.52q31.24-8 58.34-23.62 27.09-15.62 49.09-38.58l98.77 41 36.13-63.69-85.29-64.52q5-14.48 7.24-30.22 2.24-15.74 2.24-31.98 0-16.24-2.24-31.98-2.24-15.74-7.24-30.22l85.76-64.52-36.6-63.69-98.53 42q-22-23.72-49.09-39.58-27.1-15.86-58.58-23.62l-13-105.52h-73.98l-13.52 105.28q-31.72 7.76-58.94 23.62-27.21 15.86-49.45 38.82l-98.28-41-36.37 63.69 85.04 63.29q-5 15.47-7.24 30.83-2.24 15.36-2.24 32.6 0 16.24 2.24 31.72t7.24 30.95l-85.04 64.05 36.37 63.69 98.28-41.76q22.24 23.48 49.57 39.34 27.34 15.86 58.82 23.86l12.76 105.28ZM481.28-340q58 0 99-41t41-99q0-58-41-99t-99-41q-58.76 0-99.38 41t-40.62 99q0 58 40.62 99t99.38 41ZM480-480Z"/>
                    </svg>
                    Settings
                  </router-link>
                  <button
                    @click="handleLogout"
                    class="flex items-center gap-1 mb-2 text-gray-600 hover:text-gray-900 cursor-pointer"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                      <path d="M202.87-111.87q-37.78 0-64.39-26.61t-26.61-64.39v-554.26q0-37.78 26.61-64.39t64.39-26.61h279.04v91H202.87v554.26h279.04v91H202.87Zm434.02-156.65L574-333.93 674.56-434.5H358.09v-91h316.47L574-626.07l62.89-65.41L848.13-480 636.89-268.52Z"/>
                    </svg>
                    Logout
                  </button>
                </div>
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
