<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '@/stores/auth'
import UserIcon from '@/components/icons/UserIcon.vue'
import EditIcon from '@/components/icons/EditIcon.vue';
import SettingsIcon from '@/components/icons/SettingsIcon.vue'
import LogoutIcon from '../icons/LogoutIcon.vue';

const authStore = useAuthStore()
const toggle = ref(false)
const dropdownRef = ref(null)

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    toggle.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

const items = [
  {
    icon: EditIcon,
    label: 'Edit Profile',
    path: '#',
  },
  {
    icon: UserIcon,
    label: authStore.user?.role,
    path: '#',
  },
  {
    icon: SettingsIcon,
    label: "Settings",
    path: '/dashboard/settings/general',
  },
  {
    icon: LogoutIcon,
    label: "Logout",
    path: '/dashboard/logout',
  },
]

const routerHandler = async (path) => {
  try {
    if (path === '/dashboard/logout') {
      await authStore.logout()
      return
    }
    toggle.value = false
  } catch (error) {
    console.error('Error during routing:', error)
  }
}


</script>

<template>
  <div class="flex items-center gap-2 relative h-full" ref="dropdownRef">
    <span class="text-sm font-medium">{{ authStore.user?.name }}</span>
    <button
      type="button"
      :class="toggle ? 'focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-theme-100' : ''"
      class="rounded-full inline-flex items-center justify-center h-7 w-7 bg-white cursor-pointer ring-2 ring-theme-50 duration-200"
      @click="toggle = !toggle"
    >
      <UserIcon class="text-neutral-700" size="20"/>
    </button>

    <div
      class="absolute top-full right-0 w-35 py-2 bg-white border border-neutral-200 shadow-lg text-xs text-gray-700 z-5"
      v-show="toggle"
    >

      <router-link
        v-for="{icon, label, path} in items"
        :key="path"
        :to="path" class="flex items-center gap-1 text-theme-500 hover:text-theme-700 hover:bg-theme-50 font-medium text-sm px-3 py-0.5 mb-1 last:mb-0"
        @click="routerHandler(path)">
        <component :is="icon" size="16"/>
        <span class="text-[12px] capitalize">{{ label }}</span>
      </router-link>
    </div>

  </div>

</template>
