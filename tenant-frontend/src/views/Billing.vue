<script setup>
import { onMounted, ref } from 'vue'
import api from '@/lib/axios'
import { useBillingStore } from '@/stores/billing'

const billing = useBillingStore()
const plans = ref([])

onMounted(async () => {
  billing.fetch()
  const { data } = await api.get('/plans')
  plans.value = data
})

async function upgrade(planId) {
  await api.post(`/billing/upgrade/${planId}`)
  await billing.fetch()
}

async function downgrade(planId) {
  await api.post(`/billing/downgrade/${planId}`)
  await billing.fetch()
}
</script>

<template>
  <div>
    <h1>Billing</h1>

    <div v-for="plan in plans" :key="plan.id">
      <h3>{{ plan.name }}</h3>

      <button @click="upgrade(plan.id)">
        Upgrade
      </button>

      <button @click="downgrade(plan.id)">
        Downgrade
      </button>
    </div>
  </div>
</template>
