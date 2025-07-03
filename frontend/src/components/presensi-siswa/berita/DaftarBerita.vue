<template>
  <div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-5">
      <div
        v-for="berita in beritaList"
        :key="berita.daftar_berita_id"
        class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm bg-white transition hover:shadow-md hover:-translate-y-1 duration-200"
      >        
        <div class="relative w-full h-44">
          <img
            :src="getThumbnailUrl(berita.thumbnail)"
            alt="Thumbnail"
            class="w-full h-full object-cover"
          />
          <div
            class="absolute top-2 left-2 px-3 py-1 bg-blue-100 text-blue-600 text-xs font-semibold rounded-full shadow"
          >
            {{ berita.kategori }}
          </div>
        </div>

        <div class="p-4">        
          <div class="font-bold text-lg text-gray-900 mb-1 line-clamp-2">
            {{ berita.judul }}
          </div>
          
          <div class="flex items-center text-sm text-gray-500 mb-2 gap-1">
            <PhCalendarBlank :size="16" />
            <span>{{ formatTanggal(berita.updated_at) }}</span>
          </div>

         <div
  class="text-sm text-gray-600 mb-4 line-clamp-2"
  v-html="berita.konten">
  </div>
          
          <div class="flex justify-between items-center text-sm">
            <div class="flex items-center gap-2 text-gray-600">
              <img
                src="https://ui-avatars.com/api/?name=Admin&background=random"
                class="w-6 h-6 rounded-full"
                alt="Admin"
              />
              {{ berita.dibuat_oleh }}
            </div>

            <a
              href="#"
              class="text-blue-600 font-medium hover:underline flex items-center gap-1"
            >
              Baca Selengkapnya
              <PhCaretRight :size="16" />
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Api from "@/services/Api";
import { PhCalendarBlank, PhCaretRight } from "@phosphor-icons/vue";
import dayjs from "dayjs";
import "dayjs/locale/id";
const loading = ref(false);
const beritaList = ref([]);
const baseUrl = import.meta.env.VITE_API_BASE_URL;

const formatTanggal = (tanggal) => {
  return dayjs(tanggal).locale("id").format("D MMMM YYYY | HH:mm");
};
const getThumbnailUrl = (path) => {
  if (!path) return "https://source.unsplash.com/400x300/?school";
  return `${baseUrl}/storage/${path}`;   // path sudah konsisten, tinggal tambahkan /storage/
};


const fetchData = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/berita");
    beritaList.value = response.data.data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
