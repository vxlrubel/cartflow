<template>
  <AuthLayout>
    <div class="text-center mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Customer Login</h1>
      <p class="text-gray-600 mt-2">Sign in to your account</p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-6 bg-white p-10 rounded-2xl">
      <div
        v-if="authStore.error"
        class="border-l-3 px-3 py-2 border-rose-500 text-rose-600 bg-rose-100 text-sm font-medium">
        {{ authStore.error }}
      </div>

      <InputEmail
          require="true"
          label="Email Address"
          placeholder="example@domain.com"
          hint="Enter your valid email address with @"
          v-model="form.email"
        />

      <InputPassword
          label="Password"\
          placeholder="Password"
          v-model="form.password"
        />

      <div class="flex justify-end">
        <PrimaryButton
          type="submit"
          label="Login"
          :disabled="authStore.loading"
          :loading="authStore.loading"
        />
      </div>
    </form>

    <div class="mt-6 text-center">
      <p class="text-gray-600 text-sm font-medium">
        Don't have an account?
        <router-link to="/register" class="text-theme-600 hover:text-theme-700 font-medium">
          Register here
        </router-link>
      </p>
    </div>

    <div class="mt-4 text-center">
      <router-link to="/login" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
        Admin Login?
      </router-link>
    </div>

    <div class="mt-2 text-center">
      <router-link to="/" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
        &larr; Back to home
      </router-link>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthLayout from '@/layouts/AuthLayout.vue'
import InputPassword from '@/components/input/InputPassword.vue'
import InputEmail from '@/components/input/InputEmail.vue'
import PrimaryButton from '@/components/buttons/PrimaryButton.vue'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
  email: '',
  password: '',
})

const handleLogin = async () => {
  try {
    await authStore.login(form.value)
    if (authStore.isCustomer) {
      router.push('/')
    } else {
      authStore.redirectBasedOnRole()
    }
  } catch (error) {
    console.error('Login failed:', error)
  }
}
</script>
