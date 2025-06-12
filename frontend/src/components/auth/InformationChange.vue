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
                    <n-form :model="formData" :rules="rules"   ref="formRef">
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
                         @click="handleSubmit"
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
              <svg
                width="129"
                height="126"
                viewBox="0 0 129 126"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <g clip-path="url(#clip0_810_100732)">
                  <path
                    d="M81.7596 28.6275H47.2692C46.4832 28.6284 45.7297 28.9411 45.1739 29.4969C44.6182 30.0526 44.3055 30.8061 44.3046 31.5921V108.247L43.9093 108.368L35.4482 110.959C35.0472 111.081 34.6141 111.04 34.2439 110.843C33.8738 110.646 33.5968 110.31 33.4738 109.91L8.30605 27.7005C8.18357 27.2995 8.22532 26.8663 8.42211 26.4961C8.61891 26.1259 8.95464 25.8489 9.35554 25.7261L22.394 21.7337L60.193 10.1638L73.2314 6.17142C73.4298 6.11035 73.6383 6.08901 73.845 6.10862C74.0517 6.12823 74.2525 6.18841 74.4359 6.2857C74.6193 6.383 74.7817 6.5155 74.9138 6.67564C75.0459 6.83577 75.1452 7.02039 75.2059 7.21892L81.6391 28.2322L81.7596 28.6275Z"
                    fill="#E9ECEF"
                  />
                  <path
                    d="M89.2859 28.2324L81.5324 2.90656C81.4035 2.48459 81.1926 2.09217 80.9118 1.75172C80.6311 1.41128 80.2861 1.12949 79.8964 0.922453C79.5068 0.715415 79.0801 0.587186 78.6409 0.545098C78.2016 0.50301 77.7584 0.547889 77.3365 0.677165L59.0052 6.28822L21.2083 17.8601L2.877 23.4732C2.02543 23.7347 1.31239 24.3233 0.894406 25.11C0.476421 25.8967 0.387635 26.8171 0.647541 27.6691L27.1474 114.222C27.3585 114.91 27.7845 115.512 28.3629 115.94C28.9413 116.368 29.6416 116.599 30.3611 116.6C30.6941 116.6 31.0252 116.55 31.3433 116.452L43.9094 112.606L44.3047 112.483V112.07L43.9094 112.191L31.2267 116.074C30.4751 116.303 29.6632 116.225 28.9692 115.856C28.2752 115.488 27.7558 114.859 27.5249 114.108L1.02709 27.5525C0.912677 27.1803 0.8728 26.7892 0.909744 26.4016C0.946689 26.014 1.05973 25.6375 1.24239 25.2936C1.42505 24.9498 1.67376 24.6453 1.97425 24.3977C2.27475 24.1501 2.62113 23.9642 2.99357 23.8507L21.3249 18.2376L59.1219 6.66769L77.4532 1.05466C77.7357 0.968453 78.0294 0.924495 78.3247 0.92422C78.9586 0.925643 79.5753 1.13 80.0847 1.50735C80.594 1.8847 80.9691 2.41522 81.155 3.0212L88.8729 28.2324L88.9955 28.6277H89.4066L89.2859 28.2324Z"
                    fill="#3F3D56"
                  />
                  <path
                    d="M24.7468 25.7907C24.3658 25.7905 23.995 25.6681 23.6887 25.4417C23.3824 25.2152 23.1567 24.8965 23.0448 24.5324L20.4991 16.2173C20.4307 15.9939 20.407 15.7593 20.4293 15.5267C20.4515 15.2942 20.5194 15.0683 20.629 14.862C20.7386 14.6557 20.8878 14.473 21.068 14.3244C21.2482 14.1758 21.4559 14.0641 21.6793 13.9957L56.4522 3.34999C56.9033 3.21234 57.3905 3.25928 57.8071 3.48052C58.2236 3.70176 58.5353 4.07922 58.6738 4.53005L61.2195 12.8453C61.3571 13.2964 61.3101 13.7836 61.0889 14.2001C60.8676 14.6166 60.4902 14.9283 60.0395 15.0669L25.2665 25.7127C25.0981 25.7643 24.9229 25.7906 24.7468 25.7907Z"
                    fill="#1E1E1E"
                  />
                  <path
                    d="M38.0822 9.413C40.2653 9.413 42.0351 7.64325 42.0351 5.46016C42.0351 3.27707 40.2653 1.50732 38.0822 1.50732C35.8991 1.50732 34.1294 3.27707 34.1294 5.46016C34.1294 7.64325 35.8991 9.413 38.0822 9.413Z"
                    fill="#1E1E1E"
                  />
                  <path
                    d="M38.0824 7.96315C39.4648 7.96315 40.5855 6.84249 40.5855 5.46009C40.5855 4.07769 39.4648 2.95703 38.0824 2.95703C36.7 2.95703 35.5793 4.07769 35.5793 5.46009C35.5793 6.84249 36.7 7.96315 38.0824 7.96315Z"
                    fill="#F8F8F8"
                  />
                  <path
                    d="M119.606 115.59H52.8032C52.3579 115.589 51.9308 115.412 51.6159 115.097C51.301 114.782 51.1238 114.355 51.1233 113.91V33.865C51.1238 33.4196 51.3009 32.9926 51.6159 32.6777C51.9308 32.3627 52.3578 32.1856 52.8032 32.1851H119.606C120.052 32.1856 120.479 32.3627 120.794 32.6777C121.108 32.9926 121.286 33.4196 121.286 33.865V113.91C121.286 114.355 121.108 114.782 120.793 115.097C120.479 115.412 120.052 115.589 119.606 115.59Z"
                    fill="#E9ECEF"
                  />
                  <path
                    d="M88.8729 28.2322H47.2693C46.3786 28.2334 45.5247 28.5878 44.8949 29.2177C44.2651 29.8475 43.9107 30.7014 43.9094 31.5921V112.19L44.3047 112.07V31.5921C44.3057 30.8061 44.6183 30.0526 45.1741 29.4968C45.7299 28.9411 46.4834 28.6284 47.2693 28.6275H88.9955L88.8729 28.2322ZM125.14 28.2322H47.2693C46.3786 28.2334 45.5247 28.5878 44.8949 29.2177C44.2651 29.8475 43.9107 30.7014 43.9094 31.5921V122.112C43.9107 123.003 44.2651 123.857 44.8949 124.486C45.5247 125.116 46.3786 125.471 47.2693 125.472H125.14C126.031 125.471 126.885 125.116 127.515 124.486C128.144 123.857 128.499 123.003 128.5 122.112V31.5921C128.499 30.7014 128.144 29.8475 127.515 29.2177C126.885 28.5878 126.031 28.2334 125.14 28.2322ZM128.105 122.112C128.104 122.898 127.791 123.652 127.235 124.207C126.68 124.763 125.926 125.076 125.14 125.077H47.2693C46.4834 125.076 45.7299 124.763 45.1741 124.207C44.6183 123.652 44.3057 122.898 44.3047 122.112V31.5921C44.3057 30.8061 44.6183 30.0526 45.1741 29.4968C45.7299 28.9411 46.4834 28.6284 47.2693 28.6275H125.14C125.926 28.6284 126.68 28.9411 127.235 29.4968C127.791 30.0526 128.104 30.8061 128.105 31.5921V122.112Z"
                    fill="#3F3D56"
                  />
                  <path
                    d="M104.388 36.9286H68.0217C67.5501 36.9281 67.098 36.7405 66.7645 36.407C66.431 36.0735 66.2435 35.6214 66.2429 35.1498V26.4536C66.2435 25.982 66.431 25.5299 66.7645 25.1964C67.098 24.8629 67.5501 24.6753 68.0217 24.6748H104.388C104.859 24.6753 105.312 24.8629 105.645 25.1964C105.978 25.5299 106.166 25.982 106.167 26.4536V35.1498C106.166 35.6214 105.978 36.0735 105.645 36.407C105.312 36.7405 104.859 36.9281 104.388 36.9286Z"
                    fill="#1E1E1E"
                  />
                  <path
                    d="M86.2045 25.2677C88.3876 25.2677 90.1574 23.498 90.1574 21.3149C90.1574 19.1318 88.3876 17.3621 86.2045 17.3621C84.0215 17.3621 82.2517 19.1318 82.2517 21.3149C82.2517 23.498 84.0215 25.2677 86.2045 25.2677Z"
                    fill="#1E1E1E"
                  />
                  <path
                    d="M86.2048 23.7225C87.5345 23.7225 88.6124 22.6446 88.6124 21.3149C88.6124 19.9852 87.5345 18.9072 86.2048 18.9072C84.8751 18.9072 83.7971 19.9852 83.7971 21.3149C83.7971 22.6446 84.8751 23.7225 86.2048 23.7225Z"
                    fill="#F8F8F8"
                  />
                </g>
                <defs>
                  <clipPath id="clip0_810_100732">
                    <rect
                      width="128"
                      height="124.944"
                      fill="white"
                      transform="translate(0.5 0.528076)"
                    />
                  </clipPath>
                </defs>
              </svg>
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
import { ref, computed, onMounted } from "vue";
import { RouterLink } from "vue-router";
import { useMessage } from "naive-ui";
import {
  PhMagnifyingGlass,
  PhPaperPlaneTilt,
  PhCaretUpDown,
  PhFileArrowUp,
} from "@phosphor-icons/vue";
import Api from "@/services/Api"

const search = ref("");
const loading = ref(false);
const message = useMessage();
const students = ref([]); 
const formRef = ref(null);
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
      required: true,
      message: "Upload bukti wajib diisi",
      trigger: ["blur", "input"],
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
  const form = formRefs.value[siswaId]
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

const handleSave = async () => {
  loading.value = true;
  try {
    const payload = {
      ...formData.value,
    };
    await Api.post('/daftar-laporan', payload, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    message.success("Laporan Perubahan Akun Anda Berhasil Dikirim!");
  } catch (error) {
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
