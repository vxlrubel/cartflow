<script setup>
import { onMounted, computed } from 'vue'
import { useAnalyticsStore } from '@/stores/analytics'
import { useOrderStore } from '@/stores/orders'
import { useInventoryStore } from '@/stores/inventory'
import { useAuthStore } from '@/stores/auth'
import { Bar, Doughnut, Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  PointElement,
  LineElement,
} from 'chart.js'
import CurrencySymbol from '@/components/CurrencySymble.vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  PointElement,
  LineElement
)

const analyticsStore = useAnalyticsStore()
const orderStore = useOrderStore()
const inventoryStore = useInventoryStore()
const authStore = useAuthStore()

const recentStats = computed(() => {
  const data = analyticsStore.overview
  if (!data) return []
  return [
    {
      title: "Today's Sales",
      value: data.today?.sales ?? 0,
      type: 'currency',
      icon: 'sales',
      color: 'blue',
      change: data.today?.sales_change || 0,
      trend: data.today?.sales_change >= 0 ? 'up' : 'down',
    },
    {
      title: "Today's Orders",
      value: data.today?.orders ?? 0,
      type: 'number',
      icon: 'orders',
      color: 'green',
      change: data.today?.orders_change || 0,
      trend: data.today?.orders_change >= 0 ? 'up' : 'down',
    },
    {
      title: "This Month Revenue",
      value: data.month?.revenue ?? 0,
      type: 'currency',
      icon: 'revenue',
      color: 'purple',
      change: data.month?.revenue_change || 0,
      trend: data.month?.revenue_change >= 0 ? 'up' : 'down',
    },
    {
      title: 'Total Customers',
      value: data.totals?.customers ?? 0,
      type: 'number',
      icon: 'customers',
      color: 'orange',
      change: data.totals?.customers_change || 0,
      trend: data.totals?.customers_change >= 0 ? 'up' : 'down',
    },
  ]
})

const recentOrders = computed(() => orderStore.orders.slice(0, 5))

const lowStockProducts = computed(() => inventoryStore.alerts.slice(0, 5))

const orderStatusData = computed(() => {
  const breakdown = analyticsStore.overview?.order_status || {}
  return {
    labels: Object.keys(breakdown).map((k) => k.charAt(0).toUpperCase() + k.slice(1)),
    datasets: [
      {
        data: Object.values(breakdown),
        backgroundColor: ['#FBBF24', '#3B82F6', '#22C55E', '#EF4444', '#8B5CF6'],
      },
    ],
  }
})

const revenueChartData = computed(() => {
  const data = analyticsStore.revenue
  if (!data || data.length === 0) {
    return {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [
        {
          label: 'Revenue',
          data: [0, 0, 0, 0, 0, 0],
          backgroundColor: '#3B82F6',
          borderRadius: 4,
        },
      ],
    }
  }
  return {
    labels: data.map((d) => d.period || d.date || d.month),
    datasets: [
      {
        label: 'Revenue',
        data: data.map((d) => parseFloat(d.revenue || d.amount || 0)),
        backgroundColor: '#3B82F6',
        borderRadius: 4,
      },
    ],
  }
})

const salesTrendData = computed(() => {
  const data = analyticsStore.sales
  if (!data || data.length === 0) {
    return {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      datasets: [
        {
          label: 'Sales',
          data: [0, 0, 0, 0, 0, 0, 0],
          borderColor: '#22C55E',
          backgroundColor: '#22C55E',
          tension: 0.4,
        },
      ],
    }
  }
  return {
    labels: data.map((d) => d.date || d.day),
    datasets: [
      {
        label: 'Sales',
        data: data.map((d) => parseInt(d.orders || d.sales || 0)),
        borderColor: '#22C55E',
        backgroundColor: '#22C55E',
        tension: 0.4,
      },
    ],
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: '#E5E7EB',
      },
    },
    x: {
      grid: {
        display: false,
      },
    },
  },
}

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
    },
  },
}

const formatValue = (value, type) => {
  if (type === 'currency') {
    return `$${parseFloat(value || 0).toFixed(2)}`
  }
  return value || 0
}

const getMiniChartData = (stat) => {
  const colors = {
    blue: '#3B82F6',
    green: '#22C55E',
    purple: '#8B5CF6',
    orange: '#F97316',
  }
  const color = colors[stat.color] || '#3B82F6'
  
  const generateFakeValues = () => {
    const base = stat.value || 100
    return [
      base * 0.6,
      base * 0.8,
      base * 0.7,
      base * 1.2,
      base * 1.0,
      base * 0.9,
      base,
    ]
  }
  
  let chartData = {
    labels: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
    datasets: [
      {
        data: generateFakeValues(),
        backgroundColor: color + '80',
        borderColor: color,
        borderWidth: 1,
        borderRadius: 3,
      },
    ],
  }
  
  return chartData
}

const miniChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: { enabled: false },
  },
  scales: {
    x: { display: false },
    y: { display: false },
  },
}

const getTrendColor = (trend) => {
  return trend === 'up' ? 'text-green-600' : 'text-red-600'
}

const getStatusBadge = (status) => {
  const badges = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-indigo-100 text-indigo-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return badges[status?.toLowerCase()] || 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  await Promise.all([
    analyticsStore.fetchOverview(),
    analyticsStore.fetchRevenue(),
    analyticsStore.fetchSales(),
    orderStore.fetchOrders(),
    inventoryStore.fetchAlerts(),
  ])
})
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
          <p class="text-gray-600 mt-1">Welcome back, {{ authStore.user?.name || 'Admin' }}!</p>
        </div>
        <div class="flex gap-2">
          <router-link
            to="/dashboard/analytics/overview"
            class="px-4 py-2 bg-theme-600 text-white rounded-md text-sm font-medium hover:bg-theme-700"
          >
            View Analytics
          </router-link>
        </div>
      </div>
    </div>

    <div v-if="analyticsStore.loading" class="space-y-4">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <SkeletonLoader v-for="i in 4" :key="i" variant="card" />
      </div>
    </div>

    <div v-else-if="analyticsStore.error" class="bg-white rounded-lg shadow p-6">
      <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
        {{ analyticsStore.error }}
        <button @click="analyticsStore.fetchOverview()" class="underline ml-2">Retry</button>
      </div>
    </div>

    <template v-else>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="(stat, index) in recentStats"
          :key="index"
          class="bg-white rounded-lg shadow p-4"
        >
          <div class="text-sm font-medium text-gray-600">{{ stat.title }}</div>
          <div class="text-2xl font-bold text-gray-800 mt-1">{{ formatValue(stat.value, stat.type) }}</div>
          <div class="mt-2 h-20">
            <Bar
              :key="stat.title"
              :data="getMiniChartData(stat)"
              :options="miniChartOptions"
            />
          </div>
          <div v-if="stat.change !== 0" class="mt-1 flex items-center gap-1 text-sm">
            <span :class="getTrendColor(stat.trend)">
              {{ stat.trend === 'up' ? '↑' : '↓' }} {{ Math.abs(stat.change) }}%
            </span>
            <span class="text-gray-500 text-xs">vs last</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Revenue Trend</h3>
          </div>
          <div class="p-4">
            <div v-if="analyticsStore.loading" class="h-64">
              <SkeletonLoader variant="chart" />
            </div>
            <div v-else class="h-64">
              <Bar :data="revenueChartData" :options="chartOptions" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Order Status</h3>
          </div>
          <div class="p-4">
            <div v-if="analyticsStore.loading" class="h-64">
              <SkeletonLoader variant="chart" />
            </div>
            <div v-else-if="analyticsStore.overview?.order_status" class="h-64">
              <Doughnut :data="orderStatusData" :options="doughnutOptions" />
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-500">
              No order status data
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">Sales Trend</h3>
        </div>
        <div class="p-4">
          <div v-if="analyticsStore.loading" class="h-64">
            <SkeletonLoader variant="chart" />
          </div>
          <div v-else class="h-64">
            <Line :data="salesTrendData" :options="chartOptions" />
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow">
          <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Recent Orders</h3>
            <router-link
              to="/dashboard/orders"
              class="text-sm text-theme-600 hover:text-theme-700 font-medium"
            >
              View All
            </router-link>
          </div>
          <div v-if="orderStore.loading" class="p-4">
            <SkeletonLoader variant="table" />
          </div>
          <div v-else class="divide-y divide-gray-200">
            <div v-if="recentOrders.length === 0" class="p-6 text-center text-gray-500">
              No recent orders
            </div>
            <div
              v-for="order in recentOrders"
              :key="order.id"
              class="p-4 hover:bg-gray-50"
            >
              <div class="flex items-center justify-between">
                <div>
                  <router-link
                    :to="`/dashboard/orders/${order.id}`"
                    class="font-medium text-gray-800 hover:text-theme-600"
                  >
                    #{{ order.order_number }}
                  </router-link>
                  <p class="text-sm text-gray-500">{{ order.customer?.name || 'Guest' }}</p>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-800">
                    <CurrencySymbol />
                    {{ parseFloat(order.total_amount || 0).toFixed(2) }}
                  </p>
                  <span :class="['text-xs px-2 py-1 rounded-full', getStatusBadge(order.status)]">
                    {{ order.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow">
          <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Low Stock Alerts</h3>
            <router-link
              to="/dashboard/inventory/alerts"
              class="text-sm text-theme-600 hover:text-theme-700 font-medium"
            >
              View All
            </router-link>
          </div>
          <div v-if="inventoryStore.loading" class="p-4">
            <SkeletonLoader variant="table" />
          </div>
          <div v-else class="divide-y divide-gray-200">
            <div v-if="lowStockProducts.length === 0" class="p-6 text-center text-gray-500">
              No low stock products
            </div>
            <div
              v-for="product in lowStockProducts"
              :key="product.id"
              class="p-4 hover:bg-gray-50"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-800">{{ product.name }}</p>
                  <p class="text-sm text-gray-500">SKU: {{ product.sku }}</p>
                </div>
                <div class="text-right">
                  <p class="font-medium text-red-600">{{ product.stock }} left</p>
                  <p class="text-xs text-gray-500">Min: {{ product.threshold || product.min_stock }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <router-link
          to="/dashboard/orders"
          class="bg-white rounded-lg shadow p-6 hover:bg-gray-50 transition-colors"
        >
          <h3 class="text-lg font-semibold text-gray-800">Orders</h3>
          <p class="text-gray-600 mt-1">Manage all orders</p>
        </router-link>
        <router-link
          to="/dashboard/products"
          class="bg-white rounded-lg shadow p-6 hover:bg-gray-50 transition-colors"
        >
          <h3 class="text-lg font-semibold text-gray-800">Products</h3>
          <p class="text-gray-600 mt-1">Manage product catalog</p>
        </router-link>
        <router-link
          to="/dashboard/customers"
          class="bg-white rounded-lg shadow p-6 hover:bg-gray-50 transition-colors"
        >
          <h3 class="text-lg font-semibold text-gray-800">Customers</h3>
          <p class="text-gray-600 mt-1">View customer list</p>
        </router-link>
        <router-link
          to="/dashboard/reports/period"
          class="bg-white rounded-lg shadow p-6 hover:bg-gray-50 transition-colors"
        >
          <h3 class="text-lg font-semibold text-gray-800">Reports</h3>
          <p class="text-gray-600 mt-1">View sales reports</p>
        </router-link>
      </div>
    </template>
  </div>
</template>