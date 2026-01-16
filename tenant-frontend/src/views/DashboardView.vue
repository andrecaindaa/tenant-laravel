<script setup>
import { onMounted } from 'vue'
import { useTenantStore } from '@/stores/tenant'
import { useBillingStore } from '@/stores/billing'

const tenant = useTenantStore()
const billing = useBillingStore()

onMounted(() => {
  billing.fetch()
})
</script>

<template>
  <div>
    <h1>Dashboard</h1>

    <p>
      <strong>Tenant:</strong>
      {{ tenant.currentTenant?.name }}
    </p>

    <div v-if="billing.plan">
      <h2>Plano: {{ billing.plan.name }}</h2>

      <ul>
        <li
          v-for="(limit, key) in billing.plan.limits"
          :key="key"
        >
          {{ key }}:
          {{ billing.usage[key] ?? 0 }} / {{ limit }}
        </li>
      </ul>
    </div>

    <p v-else>
      Nenhum plano ativo
    </p>
  </div>
</template>
