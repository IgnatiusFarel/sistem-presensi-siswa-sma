<script setup>
import { NConfigProvider, NMessageProvider } from 'naive-ui'
import { useAuthStore } from '@/stores/Auth'
import { themeOverrides } from '@/theme'

const authStore = useAuthStore()
if (!authStore.isAuthenticated) {
  authStore.logout()
}
</script>

<template>
  <n-config-provider :theme-overrides="themeOverrides">
    <n-message-provider>
    <router-view />
  </n-message-provider>
  </n-config-provider>
</template>
