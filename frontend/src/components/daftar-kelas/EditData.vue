<template>
  <div
    class="max-w-xl mx-auto p-6 min-h-screen transition-colors duration-300"
    :class="themeStore.isDark ? 'bg-neutral-900' : 'bg-white'"
  >
    <n-button
      text
      type="primary"
      class="!mb-4 !text-sm !underline transition-colors duration-300"
      :class="themeStore.isDark ? '!text-blue-400' : '!text-gray-800'"
      @click="$emit('back-to-table')"
    >
      <template #icon>
        <n-icon :component="PhCaretDoubleLeft" :size="18" />
      </template>
      Kembali ke Halaman Daftar Kelas
    </n-button>

    <div
      class="rounded-lg p-6 border transition-all duration-300"
      :class="
        themeStore.isDark
          ? 'bg-neutral-800 border-neutral-700'
          : 'bg-white border-gray-200'
      "
    >
      <h1
        class="text-3xl font-bold mb-8 text-center transition-colors duration-300"
        :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
      >
        Edit Data Kelas
      </h1>

      <n-form :model="formData" :rules="rules" ref="formRef">
        <div class="grid grid-cols-2 gap-2">
          <n-form-item label="Kode Kelas" path="kode_kelas">
            <n-input
              v-model:value="formData.kode_kelas"
              placeholder="Masukkan Kode Kelas..."
            />
          </n-form-item>

          <n-form-item label="Nama Kelas" path="nama_kelas">
            <n-input
              v-model:value="formData.nama_kelas"
              placeholder="Masukkan Nama Kelas..."
            />
          </n-form-item>

          <n-form-item label="Jurusan" path="jurusan">
            <n-select
              v-model:value="formData.jurusan"
              :options="jurusanOptions"
              placeholder="Pilih Jurusan..."
            />
          </n-form-item>

          <n-form-item label="Tingkat" path="tingkat">
            <n-select
              v-model:value="formData.tingkat"
              :options="tingkatOptions"
              placeholder="Pilih Tingkat..."
            />
          </n-form-item>

          <n-form-item label="Tahun Ajaran" path="tahun_ajaran">
            <n-input
              v-model:value="formData.tahun_ajaran"
              placeholder="Masukkan Tahun Ajaran..."
            />
          </n-form-item>

          <n-form-item label="Wali Kelas" path="daftar_pengurus_id">
            <n-select
              v-model:value="formData.daftar_pengurus_id"
              :options="waliKelasOptions"
              placeholder="Pilih Wali Kelas..."
            />
          </n-form-item>
        </div>

        <n-button
          type="primary"
          block
          attr-type="submit"
          :loading="loadingSubmit"
          :disabled="loadingSubmit"
          @click="handleSubmit"
          class="transition-transform transform active:scale-95"
        >
          {{ loadingSubmit ? "Menyimpan..." : "Simpan" }}
        </n-button>
      </n-form>
    </div>
  </div>
</template>

<script setup>
import { defineComponent, ref, onMounted, watch } from "vue";
import { PhCaretDoubleLeft } from "@phosphor-icons/vue";
import Api from "@/services/Api";
import { useMessage } from "naive-ui";
import { useThemeStore } from "@/stores/ThemeMode";

const loading = ref(false);
const loadingSubmit = ref(false);
const formRef = ref(null);
const waliKelasOptions = ref([]);
const message = useMessage();
const themeStore = useThemeStore();
const emit = defineEmits(["back-to-table", "refresh"]);

const props = defineProps({
  editData: Object,
});

const jurusanOptions = [
  { value: "IPA", label: "IPA" },
  { value: "IPS", label: "IPS" },
  { value: "Bahasa", label: "Bahasa" },
];

const tingkatOptions = [
  { value: "X", label: "X" },
  { value: "XI", label: "XI" },
  { value: "XII ", label: "XII" },
];

const rules = {
  kode_kelas: [
    {
      required: true,
      message: "Kode kelas wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  nama_kelas: [
    {
      required: true,
      message: "Nama kelas wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  jurusan: [
    {
      required: true,
      message: "Jurusan wajib dipilih",
      trigger: ["blur", "change"],
    },
  ],
  tingkat: [
    {
      required: true,
      message: "Tingkat wajib dipilih",
      trigger: ["blur", "change"],
    },
  ],
  wali_kelas: [
    {
      required: true,
      message: "Wali kelas wajib dipilih",
      trigger: ["blur", "change"],
    },
  ],
  tahun_ajaran: [
    {
      required: true,
      message: "Tahun ajaran wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
};

const formData = ref({
  kode_kelas: "",
  nama_kelas: "",
  jurusan: null,
  tingkat: null,
  daftar_pengurus_id: null,
  tahun_ajaran: "",
});

const handleSubmit = async (e) => {
  e.preventDefault();
  try {
    await formRef.value?.validate(async (errors) => {
      if (!errors) {
        await handleSave();
        formRef.value?.restoreValidation();
      }
    });
  } catch (error) {
    console.error("Error Validasi:", error);
  }
};

const handleSave = async () => {
  loadingSubmit.value = true;
  try {
    const payload = {
      ...formData.value,
    };
    await Api.patch(`/daftar-kelas/${props.editData.daftar_kelas_id}`, payload);
    message.success("Data kelas berhasil diperbarui!");
    emit("refresh");
    emit("back-to-table");
  } catch (error) {
    message.error("Data kelas gagal diperbarui!");
    console.error("Error:", error);
  } finally {
    loadingSubmit.value = false;
  }
};

const fetchDataWaliKelas = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/daftar-pengurus");
    waliKelasOptions.value = response.data.data.map((waliKelas) => ({
      label: waliKelas.nama,
      value: waliKelas.daftar_pengurus_id,
    }));
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

watch(
  () => props.editData,
  (newVal) => {
    if (newVal) {
      formData.value = {
        kode_kelas: newVal.kode_kelas,
        nama_kelas: newVal.nama_kelas,
        jurusan: newVal.jurusan,
        tingkat: newVal.tingkat,
        daftar_pengurus_id: newVal.daftar_pengurus_id,
        tahun_ajaran: newVal.tahun_ajaran,
      };
    }
  },
  { immediate: true }
);

onMounted(() => {
  fetchDataWaliKelas();
});
</script>

<style scoped></style>
