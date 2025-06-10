<template>
  <div class="flex flex-row px-5 gap-2 mb-4">
    <n-card
      v-for="item in stats"
      :key="item.title"
      :title="item.title"
      hoverable
      class="!border-[#C1C2C5] !rounded-xl !max-w-[300px]"
    >
      <p class="text-base font-bold">{{ item.count }} / {{ total }} Kegiatan Presensi</p>
    </n-card>
  </div>
</template>

<script setup>
import { NCard } from 'naive-ui';

const total = 3000;

const stats = [
  { title: '✅ Hadir', count: 1280 }, 
  { title: '🤕 Sakit', count: 1280 },
  { title: '📝 Izin', count: 1280 },
  { title: '❌ Alpha', count: 1280 },
];
</script>
