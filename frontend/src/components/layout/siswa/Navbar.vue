<template>
  <nav
    class="bg-white border-b-1 border-[#C1C2C5] px-5 py-8 flex items-center justify-between h-[60px]"
  >
    <div class="flex items-center">
      <h1 class="text-4xl font-bold">SPSS</h1>
    </div>

    <n-dropdown
      placement="bottom-end"
      trigger="click"
      :options="profileOptions"
      @select="handleProfileSelect"
    >
      <div
        class="flex items-center gap-3 cursor-pointer border-[#C1C2C5] rounded-full bg-blue-200 px-2 py-1 cursor-pointer hover:bg-[#2F80ED] transition-colors"
      >
        <PhCaretUpDown :size="20" v-show="!isCollapsed" />
        <div class="flex flex-col items-end">
          <span class="font-medium text-gray-800">{{  user?.name || user?.email  }}</span>
          <span class="text-xs text-gray-500">{{  user?.role  || 'Siswa'}}</span>
        </div>

        <n-avatar
          round
          alt="avatar"
          size="small"
          src="https://randomuser.me/api/portraits/men/1.jpg"
        />
      </div>
    </n-dropdown>
  </nav>
</template>

<script setup>
import { h } from 'vue';
import { useRouter } from 'vue-router';
import { NAvatar, NDropdown } from 'naive-ui';
import { PhSignOut, PhGear, PhUser, PhCaretUpDown } from '@phosphor-icons/vue';
import { useAuthStore } from "@/stores/Auth";
import { storeToRefs } from 'pinia';

const auth = useAuthStore();
const { user } = storeToRefs(auth);
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

const handleProfileSelect = (key) => {
  if (key === 'logout') {
    auth.logout();
    router.push('/masuk');
  }
};
</script>

<style scoped></style>
