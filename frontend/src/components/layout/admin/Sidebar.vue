<script setup>
import { ref, h } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import {
  PhCaretUpDown,
  PhScan,
  PhUser,
  PhUserGear,
  PhChalkboardSimple,
  PhSidebarSimple,
  PhSignOut,
  PhEnvelopeSimple,
  PhGear,
} from '@phosphor-icons/vue';
import { NTooltip, NDropdown } from 'naive-ui';

const menuItems = [
  { name: 'Presensi', path: '/presensi', icon: PhScan },
  { name: 'Daftar Siswa', path: '/daftar-siswa', icon: PhUser },
  { name: 'Daftar Pengurus', path: '/daftar-pengurus', icon: PhUserGear },
  { name: 'Daftar Kelas', path: '/daftar-kelas', icon: PhChalkboardSimple },
];

const isCollapsed = ref(false);
const route = useRoute();
const router = useRouter();

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
      <button @click="toggleSidebar" class="p-1 hover:bg-gray-100 rounded-lg">
        <PhSidebarSimple :size="24" class="text-gray-600" />
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
              class="flex items-center px-3 py-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-gray-100 hover:text-gray-700"
              :class="{
                'bg-[#F1F5F5] text-[#1E1E1E] font-medium': isActive(item.path),
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
            class="flex items-center justify-between border-[#C1C2C5] rounded-full bg-[#F1F3F5] px-2 py-1 cursor-pointer hover:bg-gray-200 transition-colors"
            :class="isCollapsed ? 'justify-center' : ''"
          >
            <div class="flex items-center gap-x-2">
              <img
                class="object-cover rounded-full h-7 w-7"
                src="https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80"
                alt="avatar"
              />
              <div class="flex flex-col" v-show="!isCollapsed">
                <p class="text-sm font-semibold text-[#1E1E1E]">
                  Farel Kusuma Dewa
                </p>
                <p class="text-xs text-gray-500">Administrator</p>
              </div>
            </div>

            <PhCaretUpDown :size="20" v-show="!isCollapsed" />
          </div>
        </n-dropdown>
      </div>
    </div>
  </aside>
</template>
