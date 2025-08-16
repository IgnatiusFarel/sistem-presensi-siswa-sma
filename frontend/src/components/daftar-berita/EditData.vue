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
      Kembali ke Halaman Daftar Berita
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
            <n-upload-dragger
              class="border-2 border-dashed rounded-md transition-all duration-300 p-6"
              :class="
                themeStore.isDark
                  ? 'border-neutral-600 hover:border-blue-500 hover:bg-neutral-700'
                  : 'border-gray-400 hover:border-blue-500 hover:bg-gray-50'
              "
            >
              <div class="py-6 flex flex-col items-center justify-center">
                <img
                  src="@/assets/image.png"
                  alt="Upload Image Icon"
                  class="w-12 h-12"
                />

                <p
                  class="transition-colors duration-300"
                  :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'"
                >
                  Drag file ke sini atau
                  <span
                    class="font-medium transition-colors duration-300"
                    :class="
                      themeStore.isDark ? 'text-blue-400' : 'text-gray-800'
                    "
                  >
                    klik untuk upload
                  </span>
                </p>
                <p
                  class="text-sm mt-1 transition-colors duration-300"
                  :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'"
                >
                  Maksimal ukuran file 10MB (.jpeg, .jpg, .png)
                </p>
                <p
                  class="text-sm mt-1 transition-colors duration-300"
                  :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'"
                >
                  Upload Dokumentasi / Gambar Sebagai Thumbnail
                </p>
              </div>
            </n-upload-dragger>
          </n-upload>
        </n-form-item>

        <n-form-item label="Konten Berita" path="konten">
          <RichTextEditor v-model="formData.konten" contentType="html" />
        </n-form-item>

        <n-form-item>
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
        </n-form-item>
      </n-form>
    </div>
  </div>
</template>

<script setup>
import { defineComponent, ref, onMounted, watch } from "vue";
import { PhCaretDoubleLeft, PhFileArrowUp } from "@phosphor-icons/vue";
import { useMessage } from "naive-ui";
import Api from "@/services/Api";
import RichTextEditor from "@/components/ui/RichTextEditor.vue";
import { useAuthStore } from "@/stores/auth";
import { useThemeStore } from "@/stores/ThemeMode";

const loading = ref(false);
const loadingSubmit = ref(false);
const formRef = ref(null);
const message = useMessage();
const auth = useAuthStore();
const themeStore = useThemeStore();
const fileList = ref([]);
const emit = defineEmits(["back-to-table", "refresh"]);
const baseUrl = import.meta.env.VITE_API_BASE_URL;

const props = defineProps({
  editData: Object,
});

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
      validator: (rule, value) => {
        const plainText = value.replace(/<(.|\n)*?>/g, "").trim();
        if (!plainText) {
          return new Error("Konten wajib diisi");
        }
        return true;
      },
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
  loadingSubmit.value = true;
  try {
    const form = new FormData();

    form.append("judul", formData.value.judul || "");
    form.append("slug", formData.value.slug || "");
    form.append("kategori", formData.value.kategori || "");
    form.append("konten", formData.value.konten || "");
    form.append("user_id", auth.user?.user_id || "");
    form.append("dibuat_oleh", auth.user?.name || "");

    form.append("_method", "PATCH");

    if (formData.value.thumbnail) {
      form.append("thumbnail", formData.value.thumbnail);
    }

    await Api.post(`/daftar-berita/${props.editData.daftar_berita_id}`, form, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });

    message.success("Data berita berhasil diperbarui!");
    emit("refresh");
    emit("back-to-table");
  } catch (error) {
    console.error("Error:", error);
    message.error("Data berita gagal diperbarui!");
  } finally {
    loadingSubmit.value = false;
  }
};

function handleUploadChange({ fileList: newList }) {
  fileList.value = newList;
  if (newList.length > 0 && newList[0].file) {
    formData.value.thumbnail = newList[0].file;
  } else {
    formData.value.thumbnail = null;
  }
}

function handleBeforeUpload({ file }) {
  const isAllowedType = ["image/jpeg", "image/jpg", "image/png"].includes(
    file.type
  );
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

watch(
  () => props.editData,
  (newVal) => {
    if (newVal) {
      formData.value = {
        judul: newVal.judul,
        slug: newVal.slug,
        kategori: newVal.kategori,
        thumbnail: null,
        konten: newVal.konten,
      };

      if (newVal.thumbnail) {
        fileList.value = [
          {
            name: newVal.thumbnail.split("/").pop(),
            url: `${baseUrl}/storage/${newVal.thumbnail}`,
            status: "finished",
          },
        ];
      } else {
        fileList.value = [];
      }
    }
  },
  { immediate: true }
);
</script>

<style scoped></style>
