<template>
  <div class="flex flex-row gap-2 mb-4">
    <n-card
      v-for="item in stats"
      :key="item.title"
      :title="item.title"
      hoverable
      class="!border-[#C1C2C5] !rounded-xl !max-w-[300px]"
    >
      <p class="text-base font-bold">{{ item.count }} / {{ total }} Siswa</p>
    </n-card>
  </div>
</template>

<script setup>
import { NCard } from "naive-ui";
import { ref, onMounted } from "vue";
import Api from "@/services/Api";

const loading = ref(false);
const total = ref(0);
const stats = ref([]);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/presensi/rekap");
    const data = response.data.data;

    if (data) {
      total.value = data.total;

      stats.value = [
        { title: "✅ Hadir", count: data.rekap?.Hadir || 0 },
        { title: "🤕 Sakit", count: data.rekap?.Sakit || 0 },
        { title: "📝 Izin", count: data.rekap?.Izin || 0 },
        { title: "❌ Alpha", count: data.rekap?.Alpha || 0 },
      ];
    }
  } catch (error) {
    console.error("Gagal mengambil data rekap presensi:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
