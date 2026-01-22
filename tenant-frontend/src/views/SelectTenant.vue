<script setup>
import { useAuthStore } from '@/stores/auth'
import { useTenantStore } from '@/stores/tenant'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const tenantStore = useTenantStore()
const router = useRouter()

async function selectTenant(tenantId) {
  await tenantStore.selectTenant(tenantId)
  router.push('/dashboard')
}
</script>

<template>
  <div>
    <h1>Selecionar Tenant</h1>

    <ul>
      <li v-for="tenant in auth.tenants" :key="tenant.id">
        <button @click="selectTenant(tenant.id)">
          {{ tenant.name }}
        </button>
      </li>
    </ul>
  </div>
</template>
