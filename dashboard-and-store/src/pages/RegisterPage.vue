<template>
  <AuthLayout>
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
      <p class="text-gray-600 mt-2">Join us and start shopping</p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-6 bg-white p-8 rounded-2xl">
      <div
        v-if="authStore.error"
        class="border-l-3 px-3 py-2 border-rose-500 text-rose-600 bg-rose-100 text-sm font-medium">
        {{ authStore.error }}
      </div>
      <div
        v-if="successMessage"
        class="border-l-3 px-3 py-2 border-green-500 text-green-600 bg-green-100 text-sm font-medium">
        {{ successMessage }}
      </div>


      <div class="space-y-4">

        <InputText
          require="true"
          label="Full Name"
          placeholder="Enter your full name"
          v-model="form.name"
        />

        <InputEmail
          require="true"
          label="Email Address"
          placeholder="example@domain.com"
          hint="Enter your valid email address with @"
          v-model="form.email"
        />

        <InputPassword
          label="Password"\
          v-model="form.password"
        />
        <InputPassword
          label="Confirm Password"\
          placeholder="Confirm Your Password"
          v-model="form.password_confirmation"
        />
      </div>

      <div class="flex justify-end">
        <PrimaryButton
          type="submit"
          label="Register"
          :disabled="authStore.loading"
          :loading="authStore.loading"
        />
      </div>
    </form>

    <div class="mt-6 text-center">
      <p class="text-gray-600 text-sm font-medium">
        Already have an account?
        <router-link to="/customer/login" class="text-theme-600 hover:text-theme-700 font-medium">
          Sign in
        </router-link>
      </p>
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
import InputText from '@/components/input/InputText.vue'
import PrimaryButton from '@/components/buttons/PrimaryButton.vue'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const successMessage = ref('')

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    authStore.error = 'Passwords do not match'
    return
  }

  try {
    await authStore.register(form.value)
    successMessage.value = 'Account created successfully! Redirecting to login...'
    setTimeout(() => {
      router.push('/customer/login')
    }, 1500)
  } catch (error) {
    console.error('Registration failed:', error)
  }
}
</script>
