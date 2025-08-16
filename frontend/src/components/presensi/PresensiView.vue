<template>
  <n-tabs type="line" animated v-model:value="activeTab" class="mb-4">
    <n-tab-pane name="presensi" tab="Presensi Hari Ini">
      <h1 class="text-2xl font-bold transition-colors duration-300"
      :class="themeStore.isDark ? 'bg-neutral-900' : 'bg-gray-50'">Presensi Hari Ini</h1>
      <div class="flex items-center gap-2">
        <template v-if="presensiAktif">
          <div
            class="bg-[#D0EBFF] text-base text-[#1864AB] rounded-[4px] px-2 py-1"
          >
            {{ presensiAktif.jam_buka }} : {{ presensiAktif.jam_tutup }}
          </div>
          <span class="text-[#232323] font-semibold">-</span>
        </template>
        <template v-else>
          <div class="text-base text-red-600">Belum Ada Kegiatan Presensi</div>
          <span class="text-gray-400">|</span>
        </template>
        <p class="text-base font-semibold transition-colors duration-300" :class="themeStore.isDark ? 'bg-neutral-900' : 'bg-gray-50'">{{ currentDate }}</p>
      </div>
    </n-tab-pane>
    <n-tab-pane name="riwayat" tab="Riwayat Presensi">
       <h1 class="text-2xl font-bold transition-colors duration-300"
      :class="themeStore.isDark ? 'bg-neutral-900' : 'bg-gray-50'">Riwayat Presensi</h1>
    </n-tab-pane>
  </n-tabs>

  <component :is="currentComponent" :key="activeTab" />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { NTabs, NTabPane } from "naive-ui";
import Presensi from "@/components/presensi/presensi/Presensi.vue";
import Riwayat from "@/components/presensi/riwayat/Riwayat.vue";
import Api from "@/services/Api";
import { useThemeStore } from "@/stores/ThemeMode";

const loading = ref(false);
const currentDate = ref("");
const activeTab = ref("presensi");
const presensiAktif = ref(null);
const themeStore = useThemeStore()

const updateDateTime = () => {
  const now = new Date();

  currentDate.value = now.toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

let interval;
onMounted(() => {
  updateDateTime();
  interval = setInterval(updateDateTime, 1000);
  fetchData();
});

onUnmounted(() => {
  clearInterval(interval);
});

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/presensi/aktif");
    presensiAktif.value = response.data.data;
    console.log(response.data);
  } catch (error) {
    presensiAktif.value = null;
  } finally {
    loading.value = false;
  }
};

const currentComponent = computed(() => {
  return {
    presensi: Presensi,
    riwayat: Riwayat,
  }[activeTab.value];
});
</script>
