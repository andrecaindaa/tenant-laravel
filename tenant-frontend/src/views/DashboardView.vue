<script setup>
import { useTenantStore } from '@/stores/tenant'
const tenant = useTenantStore()
</script>

<template>
  <div>
    <h1>Dashboard</h1>
    <p>Tenant: {{ tenant.currentTenant?.name }}</p>
  </div>
</template>
