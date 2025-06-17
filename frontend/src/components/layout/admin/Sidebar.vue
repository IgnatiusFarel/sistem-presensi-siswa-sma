<script setup>
import { ref, h } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import {
  PhUser,
  PhScan,
  PhGear,
  PhSignOut,
  PhUserGear,
  PhFolders, 
  PhCaretUpDown,
  PhSidebarSimple,
  PhChalkboardSimple,
} from '@phosphor-icons/vue';
import { useAuthStore } from '@/stores/Auth';
import { storeToRefs } from 'pinia'; 

const isCollapsed = ref(false);
const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const { user } = storeToRefs(auth); 

const menuItems = [
  { name: 'Presensi', path: '/presensi', icon: PhScan },
  { name: 'Daftar Siswa', path: '/daftar-siswa', icon: PhUser },
  { name: 'Daftar Pengurus', path: '/daftar-pengurus', icon: PhUserGear },
  { name: 'Daftar Kelas', path: '/daftar-kelas', icon: PhChalkboardSimple },
  { name: 'Daftar Laporan', path: '/daftar-laporan', icon: PhFolders },
];

const profileOptions = [
  {
    label: 'Profile',
    key: 'profile',
    icon: () => h(PhUser, { size: 16 }),
  },
  {
    label: 'Settings',
    key: 'settings',
    icon: () => h(PhGear, { size: 16 }),
  },
  { type: 'divider' },
  {
    label: 'Logout',
    key: 'logout',
    icon: () => h(PhSignOut, { size: 16 }),
  },
];

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};

const isActive = (path) => {
  return route.path.startsWith(path);
};

const handleProfileSelect = (key) => {
  if (key === 'logout') {
    auth.logout();
    router.push('/masuk');
  }
};
</script>

<template>
  <aside
    class="flex flex-col h-screen px-5 py-8 overflow-y-auto bg-white border-r-1 border-[#C1C2C5] transition-all duration-300"
    :class="isCollapsed ? 'w-[72px]' : 'w-[250px]'"
  >
    <div class="flex items-center justify-between text-4xl font-bold">
      <span v-show="!isCollapsed">SPSS</span>
      <button @click="toggleSidebar" class="p-1 hover:bg-blue-100 rounded-lg">
        <PhSidebarSimple :size="24" class="text-gray-500" />
      </button>
    </div>

    <div class="flex flex-col justify-between flex-1 mt-6">
      <nav class="flex-1 -mx-3 space-y-3">
        <n-tooltip
          v-for="item in menuItems"
          :key="item.name"
          placement="right"
          trigger="hover"
          :disabled="!isCollapsed"
        >
          <template #trigger>
            <RouterLink
              :to="item.path"
              class="flex items-center px-3 py-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-blue-100 hover:text-blue-500"
              :class="{
                'bg-blue-200 text-[#2F80ED] font-medium': isActive(item.path),
              }"
            >
              <component :is="item.icon" :size="20" />
              <span class="mx-2 text-sm font-medium" v-show="!isCollapsed">
                {{ item.name }}
              </span>
            </RouterLink>
          </template>
          <span v-if="isCollapsed">{{ item.name }}</span>
        </n-tooltip>
      </nav>

      <div class="mt-6">
        <n-dropdown
          placement="top-end"
          trigger="click"
          :options="profileOptions"
          @select="handleProfileSelect"
        >
          <div
            class="flex items-center justify-between border-[#2F80ED] rounded-full bg-blue-200 px-2 py-1 cursor-pointer hover:bg-[#2F80ED]  transition-colors"
            :class="isCollapsed ? 'justify-center' : '' "
          >
            <div class="flex items-center gap-x-2">
                <n-avatar
          round
            size="small"
                src="https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80"
                alt="avatar"
              />
              <div class="flex flex-col" v-show="!isCollapsed">
                <p class="text-sm font-semibold text-[#1E1E1E]">
                  {{  user?.name || user?.email }}
                </p>
                <p class="text-xs text-gray-500"> {{ user?.role || Administrator }}</p>
              </div>
            </div>

            <PhCaretUpDown :size="20" v-show="!isCollapsed" />
          </div>
        </n-dropdown>
      </div>
    </div>
  </aside>
</template>
