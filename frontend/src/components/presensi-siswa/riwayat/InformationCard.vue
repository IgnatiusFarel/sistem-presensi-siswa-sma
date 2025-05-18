<template>
  <div class="flex flex-row px-5 gap-2 mb-4">
    <n-card title="✅ Hadir" hoverable class="!border-[#C1C2C5] !rounded-xl">
      <p class="text-base font-bold">1280 / 3000 Siswa</p>
    </n-card>
    <n-card
      title="⏰ Terlambat"
      hoverable
      class="!border-[#C1C2C5] !rounded-xl"
    >
      <p class="text-base font-bold">1280 / 3000 Siswa</p>
    </n-card>
    <n-card title="🤕 Sakit" hoverable class="!border-[#C1C2C5] !rounded-xl">
      <p class="text-base font-bold">1280 / 3000 Siswa</p>
    </n-card>
    <n-card title="📝 Izin" hoverable class="!border-[#C1C2C5] !rounded-xl">
      <p class="text-base font-bold">1280 / 3000 Siswa</p>
    </n-card>
    <n-card title="❌ Alpha" hoverable class="!border-[#C1C2C5] !rounded-xl">
      <p class="text-base font-bold">1280 / 3000 Siswa</p>
    </n-card>
  </div>
</template>

<script setup>
import { NCard } from 'naive-ui';
</script>

<style scoped>
.n-card {
  max-width: 300px;
}
</style>
