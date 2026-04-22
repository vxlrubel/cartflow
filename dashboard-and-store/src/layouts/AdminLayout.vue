<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import MenuItems from '@/components/admin/MenuItems.vue'
import LogoIcon from '@/components/icons/LogoIcon.vue'
import CloseCircle from '@/components/icons/CloseCircle.vue'
import MenuHamburger from '@/components/icons/MenuHamburger.vue'
import StopWatch from '@/components/icons/StopWatch.vue'
import UserIcon from '@/components/icons/UserIcon.vue'
import UserOptions from '@/components/admin/UserOptions.vue'

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
    second: '2-digit',
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

</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white transform transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        'lg:translate-x-0',
      ]"
    >
      <div class="flex items-center justify-between h-11 px-4 bg-theme-500">
        <router-link to="/dashboard" class="text-xl uppercase font-bold text-white flex items-center gap-2">
          <span class="p-1 rounded bg-white text-theme-500">
            <LogoIcon size="20"/>
          </span>
          Cartflow
        </router-link>
        <button @click="sidebarOpen = false" class="lg:hidden text-white">
          <CloseCircle/>
        </button>
      </div>

      <nav class="overflow-y-auto h-[calc(100vh-2.75rem)] py-4 border-r border-neutral-200">
        <MenuItems />
      </nav>
    </aside>

    <div class="lg:pl-64">
      <header class="bg-theme-500 sticky top-0 z-49 text-white">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-11">
            <div class="flex items-center">
              <button @click="sidebarOpen = true" class="lg:hidden p-2 text-white">
                <MenuHamburger/>
              </button>
              <span class="text-sm font-bold text-white text-center rounded flex items-center gap-1 w-30">
                <StopWatch size="20" />
                {{ currentTime }}
              </span>
            </div>
            <UserOptions/>
          </div>
        </div>
      </header>
      <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <router-view v-slot="{ Component }">
          <Transition name="page" mode="out-in">
            <component :is="Component" />
          </Transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<style>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.2s ease;
}

.page-enter-from,
.page-leave-to {
  opacity: 0;
}
</style>
