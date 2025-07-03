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
        Edit Data Berita
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
            :on-change="handleUploadChange"
             :file-list="fileList"    
            list-type="image"
            accept="image/*"
            :show-remove-button="true"
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

      <n-form-item label="Konten Berita" path="konten">
  <RichTextEditor v-model="formData.konten" class="mb-6" />
</n-form-item>

        <n-button
          type="primary"
          block
          attr-type="submit"
          @click="handleSubmit"
          :loading="loading"
          :disabled="loading"
          class="transition-transform transform active:scale-95"
        >
          Simpan
        </n-button>
      </n-form>
    </div>
  </div>
</template>

<script setup>
import { defineComponent, ref, onMounted,  watch } from "vue";
import { PhCaretDoubleLeft, PhFileArrowUp } from "@phosphor-icons/vue";
import { useMessage } from "naive-ui";
import Api from "@/services/Api";
import RichTextEditor from "@/components/ui/RichTextEditor.vue";
import { useAuthStore } from "@/stores/auth";

const loading = ref(false);
const formRef = ref(null);
const message = useMessage();
const auth = useAuthStore();
const fileList = ref([])
const emit = defineEmits(["back-to-table", "refresh"]);
const baseUrl = import.meta.env.VITE_API_BASE_URL;

const props = defineProps({
  editData: Object
})

const kategoriOptions = [
  { value: "Pengumuman", label: "Pengumuman" },
  { value: "Kegiatan", label: "Kegiatan" },
  { value: "Prestasi", label: "Prestasi" },
  { value: "Informasi", label: "Informasi" },
  { value: "Agenda", label: "Agenda" },
  { value: "Lainnya", label: "Lainnya" },
];

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
      message: "Konten wajib dipilih",
      trigger: ["blur", "input"],
    },
  ],
};

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
    const form = new FormData();
    
    // FIX: Pastikan semua field tidak undefined/null
    form.append("judul", formData.value.judul || "");
    form.append("slug", formData.value.slug || "");
    form.append("kategori", formData.value.kategori || "");
    form.append("konten", formData.value.konten || "");
    form.append("user_id", auth.user?.user_id || "");
    form.append("dibuat_oleh", auth.user?.name || "");
    
    // FIX: Tambahkan _method untuk Laravel
    form.append("_method", "PATCH");

    if (formData.value.thumbnail) {
      form.append("thumbnail", formData.value.thumbnail);
    }

    // FIX: Gunakan POST dengan _method PATCH (Laravel standard)
    await Api.post(
      `/daftar-berita/${props.editData.daftar_berita_id}`,
      form,
      {
        headers: {
          "Content-Type": "multipart/form-data"
        }
      }
    );

    message.success("Data berita berhasil diperbarui!");
    emit("refresh");
    emit("back-to-table");
  } catch (error) {
    console.error("Error:", error);
    message.error("Data berita gagal diperbarui!");
  } finally {
    loading.value = false;
  }
};

function handleUploadChange({ fileList: newList }) {
  fileList.value = newList    
  if (newList.length > 0 && newList[0].file) {
    formData.value.thumbnail = newList[0].file
  } else {
    formData.value.thumbnail = null
  }
}
function handleBeforeUpload({ file }) {
  const isAllowedType = ["image/jpeg", "image/jpg", "image/png"].includes(file.type);
  const isLimitSize = file.file.size / 1024 / 1024 < 10;

  if (!isAllowedType) {
    message.error("Tipe file tidak didukung!");
    return false;
  }

  if (!isLimitSize) {
    message.error("Ukuran file harus kurang dari 10MB!");
    return false;
  }

  return true;
}

watch(() => props.editData, (newVal) => {
  if (newVal) {
    formData.value = {
      judul: newVal.judul,
      slug: newVal.slug, 
      kategori: newVal.kategori, 
      thumbnail: null,
      konten: newVal.konten,
    }

    if (newVal.thumbnail) {
      fileList.value = [{
        name: newVal.thumbnail.split('/').pop(),
        url: `${baseUrl}/storage/${newVal.thumbnail}`,
         status: 'finished' 
      }]
    } else {
      fileList.value = []
    }
  }
}, { immediate: true });

</script>

<style scoped></style>
