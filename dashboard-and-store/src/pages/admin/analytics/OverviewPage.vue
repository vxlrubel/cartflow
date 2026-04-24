<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAnalyticsStore } from '@/stores/analytics'
import CurrencySymbol from '@/components/CurrencySymble.vue'

const store = useAnalyticsStore()

const statCards = computed(() => {
  const data = store.overview
  if (!data) return []
  return [
    {
      title: "Today's Sales",
      value: data.today?.sales ?? 0,
      type: 'currency',
      color: 'blue',
    },
    {
      title: "Today's Orders",
      value: data.today?.orders ?? 0,
      type: 'number',
      color: 'green',
    },
    {
      title: "This Month Revenue",
      value: data.month?.revenue ?? 0,
      type: 'currency',
      color: 'purple',
    },
    {
      title: 'Total Customers',
      value: data.totals?.customers ?? 0,
      type: 'number',
      color: 'orange',
    },
    {
      title: 'Avg Order Value',
      value: data.average_order_value ?? 0,
      type: 'currency',
      color: 'teal',
    },
    {
      title: 'Conversion Rate',
      value: data.conversion_rate ?? 0,
      type: 'percent',
      color: 'pink',
    },
  ]
})

const orderStatusCards = computed(() => {
  const data = store.overview?.order_status || {}
  return [
    { label: 'Pending', value: data.pending ?? 0, color: 'yellow' },
    { label: 'Processing', value: data.processing ?? 0, color: 'blue' },
    { label: 'Delivered', value: data.delivered ?? 0, color: 'green' },
  ]
})

const formatValue = (value, type) => {
  if (type === 'currency') {
    return `$${parseFloat(value || 0).toFixed(2)}`
  }
  if (type === 'percent') {
    return `${parseFloat(value || 0).toFixed(1)}%`
  }
  return value || 0
}

const getColorClass = (color) => {
  const colors = {
    blue: 'bg-blue-50 border-blue-200 text-blue-700',
    green: 'bg-green-50 border-green-200 text-green-700',
    purple: 'bg-purple-50 border-purple-200 text-purple-700',
    orange: 'bg-orange-50 border-orange-200 text-orange-700',
    teal: 'bg-teal-50 border-teal-200 text-teal-700',
    pink: 'bg-pink-50 border-pink-200 text-pink-700',
    yellow: 'bg-yellow-50 border-yellow-200 text-yellow-700',
  }
  return colors[color] || colors.blue
}

const getStatusColor = (color) => {
  const colors = {
    yellow: 'bg-yellow-500',
    blue: 'bg-blue-500',
    green: 'bg-green-500',
  }
  return colors[color] || colors.blue
}

onMounted(async () => {
  await store.fetchOverview()
})
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800">Analytics Overview</h2>
        <p class="text-gray-600 mt-1">Track your store performance at a glance</p>
      </div>

      <div v-if="store.loading" class="p-6">
        <div class="flex items-center justify-center py-12">
          <svg class="animate-spin h-8 w-8 text-theme-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span class="ml-3 text-gray-600">Loading analytics...</span>
        </div>
      </div>

      <div v-else-if="store.error" class="p-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
          {{ store.error }}
        </div>
      </div>

      <div v-else-if="store.overview" class="p-6 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          <div
            v-for="(card, index) in statCards"
            :key="index"
            :class="['rounded-lg border p-4', getColorClass(card.color)]"
          >
            <div class="text-sm font-medium opacity-75">{{ card.title }}</div>
            <div class="text-2xl font-bold mt-1">{{ formatValue(card.value, card.type) }}</div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">This Month</h3>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-600">Revenue</span>
                <span class="font-semibold">
                  <CurrencySymbol />
                  {{ parseFloat(store.overview.month?.revenue || 0).toFixed(2) }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Orders</span>
                <span class="font-semibold">{{ store.overview.month?.orders || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">New Customers</span>
                <span class="font-semibold">{{ store.overview.totals?.new_customers_month || 0 }}</span>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">This Year</h3>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-600">Revenue</span>
                <span class="font-semibold">
                  <CurrencySymbol />
                  {{ parseFloat(store.overview.year?.revenue || 0).toFixed(2) }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Orders</span>
                <span class="font-semibold">{{ store.overview.year?.orders || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">New Customers</span>
                <span class="font-semibold">{{ store.overview.totals?.new_customers_year || 0 }}</span>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Order Status</h3>
            <div class="space-y-3">
              <div v-for="status in orderStatusCards" :key="status.label" class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span :class="['w-3 h-3 rounded-full', getStatusColor(status.color)]"></span>
                  <span class="text-gray-600">{{ status.label }}</span>
                </div>
                <span class="font-semibold">{{ status.value }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="p-6">
        <div class="text-center py-12 text-gray-500">No analytics data available</div>
      </div>
    </div>
  </div>
</template>