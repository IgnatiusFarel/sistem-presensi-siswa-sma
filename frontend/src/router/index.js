import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '@/pages/auth/LoginPage.vue';
import InformationChangePage from '@/pages/auth/InformationChangePage.vue';
import PresensiPage from '@/pages/presensi/PresensiPage.vue';
import DaftarSiswaPage from '@/pages/daftar-siswa/DaftarSiswaPage.vue';
import DaftarPengurusPage from '@/pages/daftar-pengurus/DaftarPengurusPage.vue';
import DaftarKelasPage from '@/pages/daftar-kelas/DaftarKelasPage.vue';
import PresensiSiswaPage from '@/pages/presensi-siswa/PresensiSiswaPage.vue';

const routes = [
  { path: '/masuk', component: LoginPage },
  { path: '/perubahan-informasi', component: InformationChangePage },
  { path: '/presensi', component: PresensiPage },
  { path: '/daftar-siswa', component: DaftarSiswaPage },
  { path: '/daftar-pengurus', component: DaftarPengurusPage },
  { path: '/daftar-kelas', component: DaftarKelasPage },
  { path: '/presensi-siswa', component: PresensiSiswaPage },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
