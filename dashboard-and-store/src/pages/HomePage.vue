<template>
  <DefaultLayout>
    <section class="bg-indigo-700 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">Welcome to CartFlow</h1>
        <p class="text-xl md:text-2xl text-indigo-100 mb-8 max-w-3xl mx-auto">
          Your one-stop e-commerce platform for seamless shopping experiences. Discover amazing
          products at unbeatable prices.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <router-link
            to="/products"
            class="bg-white text-indigo-700 px-8 py-3 rounded-lg font-semibold hover:bg-indigo-50 transition"
          >
            Browse Products
          </router-link>
          <router-link
            to="/register"
            class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition"
          >
            Create Account
          </router-link>
        </div>
      </div>
    </section>

    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12">Why Choose Us</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center p-6">
            <div
              class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <svg
                class="w-8 h-8 text-indigo-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Quality Products</h3>
            <p class="text-gray-600">Handpicked products from trusted sellers</p>
          </div>
          <div class="text-center p-6">
            <div
              class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <svg
                class="w-8 h-8 text-indigo-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Best Prices</h3>
            <p class="text-gray-600">Competitive prices with exclusive deals</p>
          </div>
          <div class="text-center p-6">
            <div
              class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <svg
                class="w-8 h-8 text-indigo-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 10V3L4 14h7v7l9-11h-7z"
                />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Fast Delivery</h3>
            <p class="text-gray-600">Quick and reliable shipping nationwide</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-6">Ready to Start?</h2>
        <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
          Join thousands of satisfied customers and experience the best online shopping.
        </p>
        <router-link
          to="/register"
          class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition"
        >
          Get Started Now
        </router-link>
      </div>
    </section>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/layouts/DefaultLayout.vue'
</script>
