<template>
  <div class="max-w-xl mx-auto p-6 min-h-screen">
    <n-button
      text
      type="primary"
      class="!text-[#1E1E1E] !mb-4 !text-sm !underline"
      @click="$emit('back-to-table')"
    >
      <template #icon>
        <n-icon :component="PhCaretDoubleLeft" :size="18" />
      </template>
      Kembali ke Halaman Daftar Berita
    </n-button>

    <div class="bg-white rounded-lg p-6 border border-[#C1C2C5]">
      <h1 class="text-3xl font-bold text-[#1E1E1E] mb-8 text-center">
        Tambah Data Berita
      </h1>

      <n-form :model="formData" :rules="rules" ref="formRef">
        <n-form-item label="Judul Berita" path="judul">
          <n-input
            v-model:value="formData.judul"
            placeholder="Masukkan Judul Berita..."
          />
        </n-form-item>

        <n-form-item label="Slug" path="slug">
          <n-input
            v-model:value="formData.slug"
            placeholder="Masukkan Slug..."
          />
        </n-form-item>

        <n-form-item label="Kategori" path="kategori">
          <n-select
            v-model:value="formData.kategori"
            :options="kategoriOptions"
            placeholder="Pilih Kategori..."
          />
        </n-form-item>

        <n-form-item label="Thumbnail" path="thumbnail">
          <n-upload
            directory-dnd
            :max="1"
            :on-before-upload="handleBeforeUpload"
            list-type="image"
            accept="image/*"
          >
            <n-upload-dragger>
              <div style="margin-bottom: 12px">
                <n-icon
                  :component="PhFileArrowUp"
                  :size="48"
                  class="text-gray-400"
                />
              </div>
              <p class="text-gray-600">
                Drag file ke sini atau
                <span class="text-[#1E1E1E] font-medium"
                  >klik untuk upload</span
                >
              </p>
              <p class="text-sm text-gray-500 mt-1">
                Maksimal ukuran file 10MB (.jpeg, .jpg, .png)
              </p>
              <p class="text-sm text-gray-500 mt-1">
                Upload Dokumentasi / Gambar Sebagai Thumbnail
              </p>
            </n-upload-dragger>
          </n-upload>
        </n-form-item>

        <RichTextEditor v-model="formData.konten" class="mb-6" />

        <n-button
          type="primary"
          block
          attr-type="submit"
          @click="handleSubmit"
          :loading="loading"
          :disabled="loading"
          class="transition-transform transform active:scale-95"
        >
          Tambah
        </n-button>
      </n-form>
    </div>
  </div>
</template>

<script setup>
import { defineComponent, ref, onMounted } from "vue";
import { PhCaretDoubleLeft, PhFileArrowUp } from "@phosphor-icons/vue";
import { useMessage } from "naive-ui";
import Api from "@/services/Api";
import RichTextEditor from "@/components/ui/RichTextEditor.vue";
import { useAuthStore } from "@/stores/auth";

const loading = ref(false);
const formRef = ref(null);
const message = useMessage();
const auth = useAuthStore();
const emit = defineEmits(["back-to-table", "refresh"]);

const rules = {
  judul: [
    {
      required: true,
      message: "Judul Berita wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  slug: [
    {
      required: true,
      message: "Slug wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  kategori: [
    {
      required: true,
      message: "Kategori wajib dipilih",
      trigger: ["blur", "change"],
    },
  ],
  konten: [
    {
      required: true,
      message: "Wali kelas wajib dipilih",
      trigger: ["blur", "input"],
    },
  ],
};

const kategoriOptions = [
  { value: "Pengumuman", label: "Pengumuman" },
  { value: "Kegiatan", label: "Kegiatan" },
  { value: "Prestasi", label: "Prestasi" },
  { value: "Informasi", label: "Informasi" },
  { value: "Agenda", label: "Agenda" },
  { value: "Lainnya", label: "Lainnya" },
];

const formData = ref({
  judul: "",
  slug: "",
  kategori: null,
  konten: "",
  thumbnail: null,
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
  loading.value = true;
  try {
    const payload = {
      ...formData.value,
      user_id: auth.user?.user_id,    
      dibuat_oleh: auth.user?.name, 
    };
    await Api.post("/daftar-berita", payload);
    message.success("Data berita berhasil ditambahkan!");
    emit("refresh");
    emit("back-to-table");
  } catch (error) {
    message.error("Data berita gagal ditambahkan!");
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped></style>
