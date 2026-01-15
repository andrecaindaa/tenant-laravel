<script setup>
import { useAuthStore } from '@/stores/auth'
import { useTenantStore } from '@/stores/tenant'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const tenantStore = useTenantStore()
const router = useRouter()

async function select(tenant) {
  await tenantStore.selectTenant(tenant.id)
  router.push('/dashboard')
}
</script>

<template>
  <div>
    <h2>Select Tenant</h2>

    <ul>
      <li v-for="tenant in auth.tenants" :key="tenant.id">
        <button @click="select(tenant)">
          {{ tenant.name }}
        </button>
      </li>
    </ul>
  </div>
</template>
