<script setup>
import { NConfigProvider, NMessageProvider } from 'naive-ui'
import { useAuthStore } from '@/stores/Auth'

const authStore = useAuthStore()

if (!authStore.isAuthenticated) {
  authStore.logout()
}
</script>

<template>
  <n-config-provider>
    <n-message-provider>
    <router-view />
  </n-message-provider>
  </n-config-provider>
</template>
