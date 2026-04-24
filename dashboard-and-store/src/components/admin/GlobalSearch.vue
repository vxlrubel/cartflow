<script setup>
  import { ref, onMounted, onBeforeUnmount } from 'vue'
  const isOpen = ref(false)
  const selectRef = ref(null)

  const handleClickOutside = (e) => {
    if (selectRef.value && !selectRef.value.contains(e.target)) {
      isOpen.value = false
    }
  }

  const visibleSearchContent = ()=>{
    isOpen.value = !isOpen.value;
  }

  onMounted(() => {
    document.addEventListener('click', handleClickOutside)
  })

  onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
  })


</script>

<template>
  <div class="w-100 hidden text-gray-500 lg:flex lg:items-center lg:justify-center lg:relative" ref="selectRef">

    <input type="search" class="input-field bg-white text-gray-700" placeholder="Search keyword" @focus="visibleSearchContent">

    <Transition name="search-content">
      <div v-if="isOpen" class="absolute top-full left-0 right-0 min-h-50 max-h-100 bg-white border border-gray-300 z-50 overflow-y-auto p-5 rounded">
        <ul class="space-y-4">
          <li v-for="n in 20" :key="n">
            <div class="text-gray-800 text-sm border-b border-gray-200 pb-1 mb-1 font-semibold">Product</div>
            <ul class="pl-3 text-sm">
              <li v-for="t in 4" :key="t">
                <router-link>Iphone 12 Pro (Max)</router-link>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </Transition>

  </div>
</template>

<style scoped>
.search-content-enter-active,
.search-content-leave-active {
  transition: all 0.3s ease;
}

.search-content-enter-from,
.search-content-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
