<template>
  <div class="min-h-screen py-8">
    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Form Section -->
      <div class="bg-white rounded-xl shadow-lg p-8">
        <!-- Form Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-800 mb-1">
            {{ showAbsenceForm ? 'Form Izin/Sakit' : 'Form Presensi' }}
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

          <p class="text-gray-600 italic">
            {{
              showAbsenceForm
                ? 'Lengkapi form izin/sakit dibawah ini'
                : 'Pastikan kamu berada dalam radius sekolah'
            }}
          </p>
        </div>

        <!-- Izin/Sakit Form -->
        <div v-if="showAbsenceForm" class="space-y-6">
          <n-form>
            <n-form-item label="Jenis Izin:" class="!mb-4">
              <n-radio-group v-model:value="absenceType">
                <n-space>
                  <n-radio value="sakit">Sakit</n-radio>
                  <n-radio value="izin">Izin</n-radio>
                </n-space>
              </n-radio-group>
            </n-form-item>

            <n-form-item label="Bukti Surat:" class="!mb-4">
              <n-upload
                v-model:file-list="fileList"
                :max="1"
                list-type="image-card"
              >
                <n-button type="warning" dashed class="!w-full">
                  Upload Bukti
                </n-button>
              </n-upload>
            </n-form-item>

            <n-form-item label="Tambahan:" class="!mb-4">
              <n-input
                v-model:value="additionalInfo"
                type="textarea"
                placeholder="Contoh: saya sakit / ada acara keluarga..."
                :rows="3"
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
            :disabled="!inSchoolArea && !showAbsenceForm"
            class="!font-semibold"
          >
            {{ showAbsenceForm ? 'Kirim Izin/Sakit' : 'Submit Presensi' }} -
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
              showAbsenceForm ? 'Kembali ke Form Presensi' : 'Ajukan Izin/Sakit'
            }}
          </n-button>
        </div>

        <!-- Location Info -->
        <div class="mt-8 space-y-4 text-sm text-gray-600 border-t pt-6">
          <div>
            <p class="font-semibold">Lokasi Sekolah:</p>
            <p>Jl. Tanimbar No 22, Malang</p>
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
              {{ userLocation || 'Mendeteksi lokasi...' }}
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
import { ref, onMounted, computed } from 'vue';
import {
  NForm,
  NFormItem,
  NRadio,
  NRadioGroup,
  NUpload,
  NButton,
  NInput,
  NSpace,
  NIcon,
} from 'naive-ui';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Data
const inSchoolArea = ref(true);
const showAbsenceForm = ref(false);
const absenceType = ref('sakit');
const fileList = ref([]);
const additionalInfo = ref('');
const userLocation = ref('');
const currentTime = computed(() => {
  return new Date().toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  });
});

// Map Setup
onMounted(() => {
  const map = L.map('map').setView([-7.9666, 112.6326], 17); // SMKN 4 Malang coordinates

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
  }).addTo(map);

  // School Marker
  const schoolIcon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/4476/4476154.png',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32],
  });

  L.marker([-7.9666, 112.6326], { icon: schoolIcon })
    .addTo(map)
    .bindPopup('SMKN 4 Malang')
    .openPopup();

  // School Area Circle
  L.circle([-7.9666, 112.6326], {
    color: '#3b82f6',
    fillColor: '#3b82f6',
    fillOpacity: 0.2,
    radius: 100, // 100 meter radius
  }).addTo(map);

  // Detect User Location
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const userLatLng = [
          position.coords.latitude,
          position.coords.longitude,
        ];

        const userIcon = L.icon({
          iconUrl: 'https://cdn-icons-png.flaticon.com/512/4476/4476158.png',
          iconSize: [32, 32],
          iconAnchor: [16, 32],
          popupAnchor: [0, -32],
        });

        L.marker(userLatLng, { icon: userIcon })
          .addTo(map)
          .bindPopup('Lokasi Anda')
          .openPopup();

        // Get address name (simplified)
        fetch(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${userLatLng[0]}&lon=${userLatLng[1]}`
        )
          .then((res) => res.json())
          .then((data) => {
            userLocation.value =
              data.display_name.split(',')[0] +
                ', ' +
                data.display_name.split(',')[1] || 'Lokasi Anda';
          });

        // Check if inside school area (simplified)
        const distance = map.distance(userLatLng, [-7.9666, 112.6326]);
        inSchoolArea.value = distance <= 100; // 100 meter radius
      },
      (error) => {
        console.error('Error getting location:', error);
        userLocation.value = 'Tidak dapat mengakses lokasi';
      }
    );
  }
});

// Methods
const handleSubmit = () => {
  if (showAbsenceForm.value) {
    console.log('Submit izin/sakit:', {
      type: absenceType.value,
      file: fileList.value,
      info: additionalInfo.value,
    });
    // API call here
  } else {
    console.log('Submit presensi');
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
