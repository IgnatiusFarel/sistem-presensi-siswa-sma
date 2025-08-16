<template>
  <div class="min-h-screen flex items-center justify-center px-4 py-8 transition-colors duration-300"
       :class="themeStore.isDark ? 'bg-neutral-900' : 'bg-gray-50'">
    <div class="w-full max-w-[470px] rounded-2xl shadow-lg overflow-hidden transition-all duration-300"
         :class="themeStore.isDark ? 'bg-neutral-800 shadow-2xl' : 'bg-white shadow-lg'">
      
      <!-- Header Section -->
      <div class="py-6 px-6 text-center">
        <h1 class="text-4xl font-extrabold transition-colors duration-300"
            :class="themeStore.isDark ? 'text-white' : 'text-gray-800'">
          SPSS
        </h1>
        <p class="transition-colors duration-300"
           :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
          Sistem Presensi Siswa SMA
        </p>
        <h3 class="text-2xl font-semibold mt-2 transition-colors duration-300"
            :class="themeStore.isDark ? 'text-gray-200' : 'text-gray-700'">
          Perubahan Informasi Data Akun Siswa
        </h3>
      </div>

      <!-- Search Section -->
      <div class="px-6 py-4">
        <n-input
          v-model:value="search"
          type="text"
          clearable
          placeholder="Cari Nama Lengkap Anda..."
        >
          <template #prefix>
            <n-icon :component="PhMagnifyingGlass" 
                    class="transition-colors duration-300"
                    :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'" />
          </template>
        </n-input>
      </div>

      <!-- Students List Section -->
      <div class="px-6 pb-4">
        <template v-if="filteredStudents.length">
          <div class="space-y-3 max-h-[calc(3*70px+2rem)] overflow-y-auto">
            <div
              v-for="(student, idx) in filteredStudents"
              :key="student.daftar_siswa_id"
              class="rounded-xl border overflow-hidden transition-all duration-300"
              :class="themeStore.isDark ? 'bg-neutral-700 border-neutral-600' : 'bg-white border-gray-300'"
            >
              <!-- Student Header Button -->
              <button
                class="w-full h-[70px] flex items-center justify-between px-4 transition-colors duration-300"
                :class="themeStore.isDark ? 'hover:bg-neutral-600' : 'hover:bg-gray-50'"
                @click="toggle(idx)"
              >
                <div class="text-left">
                  <div class="font-medium transition-colors duration-300"
                       :class="themeStore.isDark ? 'text-white' : 'text-gray-800'">
                    {{ student.nama }}
                  </div>
                  <div class="text-sm transition-colors duration-300"
                       :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                    {{ student.nama_kelas }}
                  </div>
                  <div class="text-sm transition-colors duration-300"
                       :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                    {{ student.nomor_absen }}
                  </div>
                </div>
                <PhCaretUpDown
                  :class="{ 'transform rotate-180': expanded === idx }"
                  class="w-5 h-5 transition-all duration-200"
                  :style="{ color: themeStore.isDark ? '#9CA3AF' : '#6B7280' }"
                />
              </button>

              <!-- Expandable Form -->
              <transition name="fade">
                <div v-if="expanded === idx" 
                     class="px-4 pb-4 transition-colors duration-300"
                     :class="themeStore.isDark ? 'bg-neutral-700' : 'bg-white'">
                  <n-form
                    :model="formData[student.daftar_siswa_id]"
                    :rules="rules"
                    :ref="(el) => el && (formRef[student.daftar_siswa_id] = el)"
                  >
                    <!-- Jenis Perubahan -->
                    <n-form-item label="Jenis Perubahan" path="jenis_perubahan">
                      <n-radio-group
                        v-model:value="formData[student.daftar_siswa_id].jenis_perubahan"
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

                    <!-- Upload Bukti -->
                    <n-form-item label="Upload Bukti Perubahan" path="upload_bukti">
                      <n-upload
                        directory-dnd
                        :on-change="(info) => handleUploadChange(info, student.daftar_siswa_id)"
                        :on-remove="() => (formData[student.daftar_siswa_id].upload_bukti = null)"
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
                              class="transition-colors duration-300"
                              :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'"
                            />
                          </div>
                          <p class="transition-colors duration-300"
                             :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                            Drag file ke sini atau
                            <span class="font-medium transition-colors duration-300"
                                  :class="themeStore.isDark ? 'text-white' : 'text-gray-800'">
                              klik untuk upload
                            </span>
                          </p>
                          <p class="text-sm mt-1 transition-colors duration-300"
                             :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                            Maksimal ukuran file 2MB (.jpeg, .jpg, .png)
                          </p>
                          <p class="text-sm mt-1 transition-colors duration-300"
                             :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                            Bukti dapat berupa foto atau tangkapan layar.
                          </p>
                        </n-upload-dragger>
                      </n-upload>
                    </n-form-item>

                    <!-- Keterangan -->
                    <n-form-item label="Keterangan Perubahan" path="keterangan">
                      <n-input
                        type="textarea"
                        v-model:value="formData[student.daftar_siswa_id].keterangan"
                        placeholder="Masukkan alasan perubahan data akun Anda..."
                        show-count
                        maxlength="300"
                      />
                    </n-form-item>

                    <!-- Submit Button -->
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
        </template>
        
        <!-- Not Found State -->
        <template v-else>
          <div class="flex flex-col items-center justify-center py-12">
            <div class="w-32 h-32 mb-4">
              <img src="@/assets/notfound.svg" alt="Not Found Icon" />
            </div>
            <p class="transition-colors duration-300"
               :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
              Tidak Ada Nama Lengkap Tersebut!
            </p>
          </div>
        </template>
      </div>

      <!-- Footer Section -->
      <div class="px-6 py-4 border-t text-center transition-all duration-300"
           :class="themeStore.isDark ? 'border-neutral-600' : 'border-gray-200'">
        <RouterLink
          to="/masuk"
          class="font-medium hover:underline transition-colors duration-300"
          :class="themeStore.isDark ? 'text-blue-400 hover:text-blue-300' : 'text-gray-700 hover:text-gray-900'"
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
import { useThemeStore } from '@/stores/ThemeMode'
import Api from "@/services/Api";

// Stores & composables
const themeStore = useThemeStore()
const message = useMessage()

// Refs
const search = ref("")
const loading = ref(false)
const students = ref([])
const formRef = reactive({})
const expanded = ref(null)
const formData = reactive({})

// Options
const jenisPerubahanOptions = [
  { value: "Email", label: "Email" },
  { value: "Password", label: "Password" },
]

// Validation rules
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
          return new Error("Upload bukti wajib diisi")
        }
        if (!(value instanceof File)) {
          return new Error("Upload bukti tidak valid")
        }
        return true
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
}

// Computed
const filteredStudents = computed(() => {
  if (!search.value) return students.value
  return students.value.filter((s) =>
    s.nama.toLowerCase().includes(search.value.toLowerCase())
  )
})

// Methods
const fetchData = async () => {
  loading.value = true
  try {
    const response = await Api.get("/daftar-siswa-aktif")
    students.value = response.data.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

function toggle(index) {
  const student = filteredStudents.value[index]
  const id = student.daftar_siswa_id

  expanded.value = expanded.value === index ? null : index
  
  if (!formData[id]) {
    formData[id] = {
      jenis_perubahan: null,
      upload_bukti: null,
      keterangan: "",
    }
  }
}

const handleUploadChange = ({ fileList }, siswaId) => {
  if (fileList.length > 0) {
    formData[siswaId].upload_bukti = fileList[0].file
  } else {
    formData[siswaId].upload_bukti = null
  }
}

function handleBeforeUpload({ file }) {
  const isAllowedType = ["image/jpeg", "image/jpg", "image/png"].includes(
    file.type
  )
  
  const isLimitSize = file.file.size / 1024 / 1024 < 2
  if (!isAllowedType) {
    message.error("Tipe file tidak didukung!")
    return false
  }

  if (!isLimitSize) {
    message.error("Ukuran file harus kurang dari 2MB!")
    return false
  }

  return true
}

const handleSubmit = async (siswaId) => {
  const form = formRef[siswaId]
  if (!form) return

  try {
    await form.validate((errors) => {
      if (!errors) {
        handleSave(siswaId)
        form.restoreValidation()
      }
    })
  } catch (error) {
    console.error("Error Validasi:", error)
    message.error("Laporan Perubahan Akun Anda Gagal Tervalidasi!")
  }
}

const resetForm = (siswaId) => {
  formData[siswaId] = {
    jenis_perubahan: null,
    upload_bukti: null,
    keterangan: "",
  }
  if (formRef[siswaId]) {
    formRef[siswaId].restoreValidation()
  }
}

const handleSave = async (siswaId) => {
  loading.value = true
  try {
    const data = formData[siswaId]

    const formPayload = new FormData()
    formPayload.append("daftar_siswa_id", siswaId)
    formPayload.append("jenis_perubahan", data.jenis_perubahan)
    formPayload.append("upload_bukti", data.upload_bukti)
    formPayload.append("keterangan", data.keterangan)

    await Api.post("/daftar-laporan", formPayload, {
      headers: { "Content-Type": "multipart/form-data" },
    })
    message.success("Laporan Perubahan Akun Anda Berhasil Dikirim!")
    resetForm(siswaId)
  } catch (error) {
    console.error(error)
    message.error("Laporan Perubahan Akun Anda Gagal Dikirim!")
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>