<template>
  <div
    class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-8"
  >
    <div
      class="w-full max-w-[470px] bg-white rounded-2xl shadow-lg overflow-hidden"
    >
      <div class="py-6 px-6 text-center">
        <h1 class="text-4xl font-extrabold text-gray-800">SPSS</h1>
        <p class="text-gray-500">Sistem Presensi Siswa SMA</p>
        <h3 class="text-2xl font-semibold text-gray-700 mt-2">
          Perubahan Informasi Data Akun Siswa
        </h3>
      </div>

      <div class="px-6 py-4">
        <n-input
          v-model:value="search"
          type="text"
          clearable
          placeholder="Cari Nama Lengkap Anda..."
        >
          <template #prefix>
            <n-icon :component="PhMagnifyingGlass" class="text-gray-400" />
          </template>
        </n-input>
      </div>

      <div class="px-6 pb-4">
        <template v-if="filteredStudents.length">
          <div class="space-y-3 max-h-[calc(3*70px+2rem)] overflow-y-auto">
            <div
              v-for="(student, idx) in filteredStudents"
              :key="student.daftar_siswa_id"
              class="bg-white rounded-xl border border-[#C1C2C5] overflow-hidden"
            >
              <div>
                <button
                  class="w-full h-[70px] flex items-center justify-between px-4"
                  @click="toggle(idx)"
                >
                  <div class="text-left">
                    <div class="font-medium text-gray-800">
                      {{ student.nama }}
                    </div>
                    <div class="text-sm text-[#232323]">
                      {{ student.nama_kelas }}
                    </div>
                    <div class="text-sm text-[#232323]">
                      {{ student.nomor_absen }}
                    </div>
                  </div>
                  <PhCaretUpDown
                    :class="{ 'transform rotate-180': expanded === idx }"
                    class="w-5 h-5 text-gray-400 transition-transform duration-200"
                  />
                </button>
                <transition name="fade">
                  <div v-if="expanded === idx" class="px-4 pb-4 bg-white">
                    <n-form :model="formData" :rules="rules"   :ref="el => el && (formRef[student.daftar_siswa_id] = el)">
                      <n-form-item
                        label="Jenis Perubahan"
                        path="jenis_perubahan"
                      >
                        <n-radio-group
                          v-model:value="formData.jenis_perubahan"
                          name="jenis_perubahan"
                        >
                          <n-radio-button
                            v-for="option in jenisPerubahanOptions"
                            :key="option.value"
                            :value="option.value"
                            :label="option.label"
                          />
                        </n-radio-group>
                      </n-form-item>

                      <n-form-item
                        label="Upload Bukti Perubahan"
                        path="upload_bukti"
                      >
                        <n-upload                                                  
                          directory-dnd
                          :on-change="handleUploadChange"
                          :on-remove="() => (uploadFiles.value = [])"
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
                              Maksimal ukuran file 2MB (.jpeg, .jpg, .png)
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                              Bukti dapat berupa foto atau tangkapan layar.
                            </p>
                          </n-upload-dragger>
                        </n-upload>
                      </n-form-item>

                      <n-form-item
                        label="Keterangan Perubahan"
                        path="keterangan"
                      >
                        <n-input
                          type="textarea"
                          v-model:value="formData.keterangan"
                          placeholder="Masukkan alasan perubahan data akun Anda..."
                            show-count
                          maxlength="300"
                        />
                      </n-form-item>

                      <n-button
                        :loading="loading"
                        :disabled="loading"
                        block
                        type="primary"
                        attr-type="submit"
                         @click="() => handleSubmit(student.daftar_siswa_id)"
                        class="transition-transform transform active:scale-95"
                      >
                        <span v-if="loading">Memproses...</span>
                        <span v-else>Kirim </span>
                        <PhPaperPlaneTilt :size="20" />
                      </n-button>
                    </n-form>
                  </div>
                </transition>
              </div>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="flex flex-col items-center justify-center py-12">
            <div class="w-32 h-32 mb-4">
             <img src="@/assets/notfound.svg" alt="Ilustrasi" />
            </div>
            <p class="text-gray-500">Tidak Ada Nama Lengkap Tersebut!</p>
          </div>
        </template>
      </div>

      <div class="px-6 py-4 border-t border-gray-200 text-center">
        <RouterLink
          to="/masuk"
          class="text-[#232323] font-medium hover:underline"
        >
          Masuk?
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from "vue";
import { RouterLink } from "vue-router";
import { useMessage } from "naive-ui";
import {
  PhCaretUpDown,
  PhFileArrowUp,
  PhPaperPlaneTilt,
  PhMagnifyingGlass,
} from "@phosphor-icons/vue";
import Api from "@/services/Api"

const search = ref("");
const loading = ref(false);
const message = useMessage();
const students = ref([]); 
const formRef = reactive({});
const expanded = ref(null);

const jenisPerubahanOptions = [
  { value: "Email", label: "Email" },
  { value: "Password", label: "Password" },
];

const formData = ref({
  jenis_perubahan: null,
  upload_bukti: null,
  keterangan: "",
});

const rules = {
  jenis_perubahan: [
    {
      required: true,
      message: "Jenis perubahan wajib dipilih",
      trigger: ["blur", "change"],
    },
  ],
  upload_bukti: [
  {
    validator(rule, value) {
      if (!value) {
        return new Error("Upload bukti wajib diisi");
      }
      if (!(value instanceof File)) {
        return new Error("Upload bukti tidak valid");
      }
      return true;
    },
    trigger: ["change"],
  },
],
  keterangan: [
    {
      required: true,
      message: "Keterangan wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
};

const fetchData = async () => {
  loading.value = true; 
  try {
    const response = await Api.get('/daftar-siswa-aktif')  
    students.value = response.data.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false; 
  }
};

const filteredStudents = computed(() => {
  if (!search.value) return students.value;
  return students.value.filter((s) =>
    s.nama.toLowerCase().includes(search.value.toLowerCase())
  );
});

function toggle(index) {
  expanded.value = expanded.value === index ? null : index;
}

const handleUploadChange = ({ fileList }) => {
  // hanya simpan 1 file (karena max=1)
  if (fileList.length > 0) {
    formData.value.upload_bukti = fileList[0].file; // ambil File object-nya
  } else {
    formData.value.upload_bukti = null;
  }
};

function handleBeforeUpload({ file }) {
  const isAllowedType = ['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)
  const isLimitSize = file.file.size / 1024 / 1024 < 2 
  if (!isAllowedType) {
    message.error('Tipe file tidak didukung!')
    return false
  }

  if (!isLimitSize) {
    message.error('Ukuran file harus kurang dari 2MB!')
    return false
  }

  return true
}

const handleSubmit = async (siswaId) => {
  const form = formRef[siswaId]
  if (!form) return

  try {
    await form.validate(errors => {
      if (!errors) {
        handleSave(siswaId)
        form.restoreValidation()
      }
    })
  } catch (error) {
    console.error("Error Validasi:", error);
    message.error('Laporan Perubahan Akun Anda Gagal Tervalidasi!')
  }
}; 

const handleSave = async (siswaId) => {
  loading.value = true;
  try {
    const formPayload = new FormData();
    formPayload.append("daftar_siswa_id", siswaId);
    formPayload.append("jenis_perubahan", formData.value.jenis_perubahan);
    formPayload.append("upload_bukti", formData.value.upload_bukti); // ini File
    formPayload.append("keterangan", formData.value.keterangan);
    
    await Api.post('/daftar-laporan', formPayload, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    message.success("Laporan Perubahan Akun Anda Berhasil Dikirim!");
  } catch (error) {
   console.error(error)
    message.error("Laporan Perubahan Akun Anda Gagal Dikirim!");
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData); 
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
