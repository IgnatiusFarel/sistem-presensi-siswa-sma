import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/Auth.js' 
import LoginPage from '@/pages/auth/LoginPage.vue';
import InformationChangePage from '@/pages/auth/InformationChangePage.vue';
import PresensiPage from '@/pages/presensi/PresensiPage.vue';
import DaftarSiswaPage from '@/pages/daftar-siswa/DaftarSiswaPage.vue';
import DaftarPengurusPage from '@/pages/daftar-pengurus/DaftarPengurusPage.vue';
import DaftarKelasPage from '@/pages/daftar-kelas/DaftarKelasPage.vue';
import DaftarBeritaPage from '@/pages/daftar-berita/DaftarBeritaPage.vue';
import DaftarLaporanPage from '@/pages/daftar-laporan/DaftarLaporanPage.vue';
import PresensiSiswaPage from '@/pages/presensi-siswa/PresensiSiswaPage.vue';

const routes = [
  { path: '/masuk', component: LoginPage, meta: {public: true}},
  { path: '/perubahan-informasi', component: InformationChangePage, meta: {public: true} },
  { path: '/presensi', component: PresensiPage, meta: {requiresAuth: true, roles: ['superadmin']} },
  { path: '/daftar-siswa', component: DaftarSiswaPage, meta: {requiresAuth: true, roles: ['superadmin']} },
  { path: '/daftar-pengurus', component: DaftarPengurusPage, meta: {requiresAuth: true, roles: ['superadmin']} },
  { path: '/daftar-kelas', component: DaftarKelasPage, meta: {requiresAuth: true, roles: ['superadmin']} },
  { path: '/daftar-laporan', component: DaftarLaporanPage, meta: {requiresAuth: true, roles: ['superadmin']} },
  { path: '/daftar-berita', component: DaftarBeritaPage, meta: {requiresAuth: true, roles: ['superadmin']} },
  { path: '/presensi-siswa', component: PresensiSiswaPage, meta: {requiresAuth: true, roles: ['siswa']} },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const isAuth = authStore.isAuthenticated
  const userRole = authStore.user?.role || null 

  if (to.meta.public) {
    if (isAuth) {
      if (userRole === 'superadmin') {
        return next ('/presensi')
      }

      if (userRole === 'siswa') {
        return next ('/presensi-siswa')
      }
    }
    return next()
  }

  if (to.meta.requiresAuth) { 
    if (!isAuth) {
      return next('/masuk')
    }

    const allowedRoles = to.meta.roles || []
    if (allowedRoles.length > 0 && !allowedRoles.includes(userRole)) {
      if (userRole === 'siswa') {
        return next('/presensi-siswa')
      }

      if (userRole === 'superadmin') {
        return next('/presensi')
      }
      return next('/masuk')
    }
    return next()
  }

  if (!to.meta.public && !to.meta.requiresAuth) {
    if (!isAuth) {
      return next('/masuk')
    }
  }
 next()
})

export default router;
