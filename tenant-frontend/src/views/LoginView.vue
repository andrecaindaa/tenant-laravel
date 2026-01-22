<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const email = ref('admin@teste.com')
const password = ref('password')
const error = ref(null)
const loading = ref(false)

const auth = useAuthStore()
const router = useRouter()

async function submit() {
  error.value = null
  loading.value = true

  try {
    await auth.login(email.value, password.value)

    if (auth.tenants.length >= 1) {
      router.push('/select-tenant')
    } else {
      error.value = 'Utilizador sem tenants associados'
    }
  } catch {
    error.value = 'Credenciais inválidas'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <div class="card">
      <h1>Login</h1>

      <form @submit.prevent="submit">
        <input v-model="email" placeholder="Email" />
        <input v-model="password" type="password" placeholder="Password" />

        <button :disabled="loading">
          {{ loading ? 'A entrar...' : 'Entrar' }}
        </button>
      </form>

      <p v-if="error" class="error">
        {{ error }}
      </p>
    </div>
  </div>
</template>

<style scoped>
.error {
  margin-top: 1rem;
  color: #dc2626;
  text-align: center;
}
</style>
