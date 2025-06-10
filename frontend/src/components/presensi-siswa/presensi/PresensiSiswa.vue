<template>
  <div class="min-h-screen py-8">
    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Form Section -->
      <div class="bg-white rounded-xl shadow-lg p-8">
        <!-- Form Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-800 mb-1">
            {{ showAbsenceForm ? "Form Izin/Sakit" : "Form Presensi" }}
          </h1>

          <!-- Location Permission Notice -->
          <div
            v-if="!showAbsenceForm"
            class="flex items-start gap-2 bg-blue-50 p-4 rounded-lg mb-4"
          >
            <n-icon size="20" color="#1d4ed8" class="mt-0.5">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z"
                  clip-rule="evenodd"
                />
              </svg>
            </n-icon>
            <div>
              <p class="text-blue-800 font-medium">
                Pastikan kamu mengizinkan lokasi
              </p>
              <p class="text-blue-600 text-sm">
                Kami membutuhkan akses lokasi untuk memverifikasi kehadiranmu
              </p>
            </div>
          </div>

           <div
  v-if="showAbsenceForm"
  class="flex items-start gap-2 bg-orange-50 p-4 rounded-lg mb-4"
>
  <n-icon size="20" color="#ea580c" class="mt-0.5">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
      <path
        fill-rule="evenodd"
        d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z"
        clip-rule="evenodd"
      />
    </svg>
  </n-icon>
  <div>
    <p class="text-orange-800 font-medium">
      Pastikan kamu mengirimkan bukti kegiatan
    </p>
    <p class="text-orange-600 text-sm">
      Kami membutuhkan bukti kegiatan untuk memverifikasi permohonan jenis kegiatan.
    </p>
  </div>
</div>

          <p class="text-gray-600 italic mb-2">
            {{
              showAbsenceForm
                ? "Lengkapi form izin/sakit dibawah ini"
                : "Pastikan kamu berada dalam radius sekolah"
            }}
          </p>

          <!-- Tampilkan tombol & info lokasi hanya untuk Form Presensi -->
          <div
            v-if="!showAbsenceForm && !locationInitialized"
            class="flex items-center justify-between gap-2 bg-red-50 border border-red-300 rounded-lg p-4 mb-6"
          >
            <div class="text-sm text-gray-600">
              Lokasi belum aktif. Klik tombol di samping untuk mendeteksi lokasi
              secara manual.
            </div>
            <n-button
              @click="initLocation"
              size="small"
              quaternary
              class="rounded-full bg-white shadow text-gray-700"
            >
              <template #icon>
                <n-icon size="18" class="text-blue-600">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </n-icon>
              </template>
              Aktifkan Lokasi
            </n-button>
          </div>
        </div>

        <!-- Izin/Sakit Form -->
        <div v-if="showAbsenceForm" class="space-y-6">
          <n-form :model="formData" :rules="rules" ref="formRef">

            <n-form-item label="Jenis Kegiatan:" path="jenis_kegiatan">
              <div class="grid grid-cols-2">
                <n-radio-group v-model:value="formData.jenis_kegiatan"
                name="jenis_kegiatan">
                 <n-radio-button
                  v-for="option in jenisKegiatanOptions"
                  :key="option.value"
                  :value="option.value"
                  :label="option.label"
                  class="!w-full"
                />
              </n-radio-group>
              </div>           
            </n-form-item>

            <n-form-item label="Upload Bukti:" path="upload_bukti">
              <n-upload
                multiple
                directory-dnd
                action="https://www.mocky.io/v2/5e4bafc63100007100d8b70f"
                :max="1"
                accept="image/*,.pdf, word, doc"
              >
                <n-upload-dragger>
                  <div style="margin-bottom: 12px">
                     <n-icon
                :component="PhFileArrowUp"
                :size="48"
                class="text-gray-400 "
              />
                  </div>
                  <p class="text-gray-600">
                Drag file ke sini atau
                <span class="text-[#1E1E1E] font-medium"
                  >klik untuk upload</span
                >
              </p>
              <p class="text-sm text-gray-500 mt-1">
                  Maksimal ukuran file 5MB (.jpg, .png, .pdf)
              </p>
               <p class="text-sm text-gray-500 mt-1">
                Bukti dapat berupa surat izin atau foto surat atau foto kegiatan yang memperlihatkan wajah.
              </p>
                 
                </n-upload-dragger>
              </n-upload>
            </n-form-item>

            <n-form-item label="Catatan:" class="!mb-4">
              <n-input
                v-model:value="additionalInfo"
                type="textarea"
                placeholder="Contoh: saya sakit / ada acara keluarga..."
                :rows="3"
                show-count
                maxlength="300"
              />
            </n-form-item>
          </n-form>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
          <n-button
            :type="showAbsenceForm ? 'warning' : 'success'"
            size="large"
            block
            @click="handleSubmit"
             :disabled="showAbsenceForm ? !isAbsenceFormValid : !inSchoolArea"
            class="!font-semibold"
          >
            {{ showAbsenceForm ? "Kirim Izin/Sakit" : "Submit Presensi" }} -
            {{ currentTime }}
          </n-button>
        </div>

        <!-- Toggle Absence Form -->
        <div class="mt-4">
          <n-button
            :type="showAbsenceForm ? 'success' : 'warning'"
            dashed
            @click="showAbsenceForm = !showAbsenceForm"
            class="!w-full"
          >
            {{
              showAbsenceForm ? "Kembali ke Form Presensi" : "Ajukan Izin/Sakit"
            }}
          </n-button>
        </div>

        <!-- Location Info -->
        <div
          v-if="!showAbsenceForm"
          class="mt-8 space-y-4 text-sm text-gray-600 border-t pt-6"
        >
          <div>
            <p class="font-semibold">Lokasi Sekolah:</p>
            <p>
              Jalan Kolonel Sutarto Nomor 150K, Jebres, Surakarta City, Central
              Java 57126
            </p>
          </div>
          <div>
            <p class="font-semibold">Lokasi Kamu:</p>
            <p class="flex items-center gap-1">
              <n-icon
                v-if="userLocation"
                size="16"
                :color="inSchoolArea ? '#16a34a' : '#dc2626'"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                    clip-rule="evenodd"
                  />
                </svg>
              </n-icon>
              {{ userLocation || "Mendeteksi lokasi..." }}
            </p>
          </div>
        </div>
      </div>

      <!-- Map Section -->
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div id="map" class="h-full min-h-[500px]"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
// import {
//   NForm,
//   NFormItem,
//   NRadio,
//   NRadioGroup,
//   NUpload,
//   NButton,
//   NInput,
//   NSpace,
//   NIcon,
// } from "naive-ui";
import { PhFileArrowUp } from "@phosphor-icons/vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const map = ref(null);
const showAbsenceForm = ref(false);
const absenceType = ref("sakit");
const fileList = ref([]);
const additionalInfo = ref("");
const userLocation = ref("");
const inSchoolArea = ref(false);
const locationInitialized = ref(false);
const formRef = ref(null);

const currentTime = computed(() => {
  return new Date().toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
  });
});
const SCHOOL_RADIUS = 500;
const SCHOOL_COORDS = [-7.559501483655755, 110.83844505717236];

// Map Setup
onMounted(() => {
  map.value = L.map("map").setView(SCHOOL_COORDS, 17);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors",
  }).addTo(map.value);

  const schoolIcon = L.icon({
    iconUrl: "https://cdn-icons-png.flaticon.com/512/4476/4476154.png",
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32],
  });

  L.marker(SCHOOL_COORDS, { icon: schoolIcon })
    .addTo(map.value)
    .bindPopup("Sekolah Vokasi UNS");

  L.circle(SCHOOL_COORDS, {
    color: "#3b82f6",
    fillColor: "#3b82f6",
    fillOpacity: 0.2,
    radius: SCHOOL_RADIUS,
  }).addTo(map.value);
});

// Lokasi Deteksi Manual
const initLocation = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const userLatLng = [
          position.coords.latitude,
          position.coords.longitude,
        ];

        const distance = map.value.distance(userLatLng, SCHOOL_COORDS);
        inSchoolArea.value = distance <= SCHOOL_RADIUS;

        const userIcon = L.icon({
          iconUrl: "https://cdn-icons-png.flaticon.com/512/4476/4476158.png",
          iconSize: [32, 32],
          iconAnchor: [16, 32],
          popupAnchor: [0, -32],
        });

        L.marker(userLatLng, { icon: userIcon })
          .addTo(map.value)
          .bindPopup("Lokasi Anda")
          .openPopup();

        fetch(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${userLatLng[0]}&lon=${userLatLng[1]}`
        )
          .then((res) => res.json())
          .then((data) => {
            userLocation.value =
              data.display_name?.split(",")[0] +
                ", " +
                data.display_name?.split(",")[1] || "Lokasi Anda";
          });

        locationInitialized.value = true;
      },
      (error) => {
        userLocation.value = "Gagal mengakses lokasi";
        console.error("Location error:", error);
        locationInitialized.value = true;
      }
    );
  }
};

const rules = { 
  jenis_kegiatan: [
    {
      required: true,
      message: "Jenis kegiatan wajib dipilih",
      trigger: ["blur", "change"],
    },
  ],
  upload_bukti: [
    {
      required: true,
      validator(_, value) {
        if (!value || value.length === 0) {
          return new Error("Upload bukti wajib diisi");
        }
        return true;
      },
        trigger: ["blur", "change"],
    },
  ]
}

const jenisKegiatanOptions = [
  { value: 'Sakit', label: 'Sakit' },
  { value: 'Izin', label: 'Izin' },
];

const formData = ref({
  jenis_kegiatan: null,
  upload_bukti: null, 
  catatan: ""
});

const isAbsenceFormValid = computed(() => {
  return (
    formData.value.jenis_kegiatan &&
    fileList.value.length > 0
  );
});

// Methods
const handleSubmit = () => {
  if (showAbsenceForm.value) {
    console.log("Submit izin/sakit:", {
      type: absenceType.value,
      file: fileList.value,
      info: additionalInfo.value,
    });
    // API call here
  } else {
    console.log("Submit presensi");
    // API call here
  }
};
</script>

<style scoped>
/* Custom marker fix */
:deep(.leaflet-div-icon) {
  background: transparent;
  border: none;
}
</style>
