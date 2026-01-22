import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '@/views/LoginView.vue'
import SelectTenant from '@/views/SelectTenant.vue'
import DashboardView from '@/views/DashboardView.vue'

const router = createRouter({
  history: createWebHistory(),

  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },

    {
      path: '/select-tenant',
      name: 'select-tenant',
      component: SelectTenant,
    },

    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
    },

    {
      path: '/',
      redirect: '/login',
    },
  ],
})

export default router
