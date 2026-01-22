<script setup>
import { onMounted, computed } from 'vue'
import { useTenantStore } from '@/stores/tenant'
import { useBillingStore } from '@/stores/billing'

const tenant = useTenantStore()
const billing = useBillingStore()

onMounted(() => {
  billing.fetch()
})

const trialDaysLeft = computed(() => {
  if (!billing.trialEndsAt) return null
  const diff =
    (new Date(billing.trialEndsAt) - new Date()) / 86400000
  return Math.ceil(diff)
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
      <p>
        <strong>Plano:</strong>
        {{ billing.plan.name }}
      </p>

      <p v-if="billing.onTrial">
        Trial ativo — termina em {{ trialDaysLeft }} dias
      </p>

      <ul>
        <li
          v-for="(limit, key) in billing.plan.limits"
          :key="key"
        >
          {{ key }}:
          {{ billing.usage[key] ?? 0 }} / {{ limit }}
        </li>
      </ul>

      <p v-if="billing.onTrial && trialDaysLeft <= 3">
        ⚠️ O seu trial está prestes a terminar
      </p>
    </div>
  </div>
</template>
