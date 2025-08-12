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
          :options="menuOptions"
          @select="handleMenuOptionsSelect"
        >
          <div
            class="flex items-center justify-between border-[#2F80ED] rounded-full bg-blue-200 px-2 py-1 cursor-pointer hover:bg-[#2F80ED] transition-colors"
            :class="isCollapsed ? 'justify-center' : ''"
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
                  {{ user?.name || user?.email }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ user?.role || Administrator }}
                </p>
              </div>
            </div>

            <PhCaretUpDown :size="20" v-show="!isCollapsed" />
          </div>
        </n-dropdown>

        <n-modal
          v-model:show="showProfileModal"
          preset="card"
          title="Profil"
          :style="{ width: '400px', maxWidth: '90%' }"
        >
          <div class="flex flex-col items-center gap-3 p-4">
            <n-image
              width="100"
              src="https://07akioni.oss-cn-beijing.aliyuncs.com/07akioni.jpeg"
              class="rounded-full"
            />
            <div class="text-center">
              <p class="text-lg font-semibold">{{ user?.name }}</p>
              <p class="text-sm text-gray-500">{{ user?.email }}</p>
              <p class="text-sm text-gray-400 capitalize">{{ user?.role }}</p>
            </div>
          </div>
        </n-modal>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, h, onMounted } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import {
  PhUser,
  PhBell,
  PhScan,
  PhGear,
  PhFolders,
  PhSignOut,
  PhUserGear,
  PhNewspaper,
  PhCaretUpDown,
  PhSidebarSimple,
  PhChalkboardSimple,
} from "@phosphor-icons/vue";
import { NAvatar, NDropdown, useMessage} from 'naive-ui';
import { useAuthStore } from "@/stores/Auth";
import { storeToRefs } from "pinia";

const route = useRoute();
const router = useRouter();
const message = useMessage();
const auth = useAuthStore();
const { user } = storeToRefs(auth);

const isCollapsed = ref(false);
const isDarkTheme = ref(false);
const isEnglish = ref(false);
const showProfileModal = ref(false);
const isNotificationAllowed = ref(false);

const collapseBreakpoint = 768;
function handleResize() {
  if (window.innerWidth < collapseBreakpoint) {
    isCollapsed.value = true;
  } else {
    isCollapsed.value = false;
  }
}

onMounted(() => {
  handleResize();
  window.addEventListener("resize", handleResize);
});

const menuItems = [
  { name: "Presensi", path: "/presensi", icon: PhScan },
  { name: "Daftar Siswa", path: "/daftar-siswa", icon: PhUser },
  { name: "Daftar Pengurus", path: "/daftar-pengurus", icon: PhUserGear },
  { name: "Daftar Kelas", path: "/daftar-kelas", icon: PhChalkboardSimple },
  { name: "Daftar Berita", path: "/daftar-berita", icon: PhNewspaper },
  { name: "Daftar Laporan", path: "/daftar-laporan", icon: PhFolders },
];

const menuOptions = [
  {
    label: "Profil",
    key: "profil",
    icon: () => h(PhUser, { size: 16 }),
  },
  {
    label: "Pengaturan",
    key: "pengaturan",
    icon: () => h(PhGear, { size: 16 }),
    children: [
      {
        key: "tema",
        label: () =>
          h("div", { class: "flex items-center justify-between w-[120px]" }, [
            h("span", { class: "text-sm" }, "Tema"),
            h(
              resolveComponent("n-switch"),
              {
                "onUpdate:value": (v) => (isDarkTheme.value = v),
                value: isDarkTheme.value,
                size: "small",
              },
              {
                icon: () => (isDarkTheme.value ? "🌙" : "☀️"),
              }
            ),
          ]),
      },
      {
        key: "bahasa",
        label: () =>
          h("div", { class: "flex items-center justify-between w-[120px]" }, [
            h("span", { class: "text-sm" }, "Bahasa"),
            h(
              resolveComponent("n-switch"),
              {
                "onUpdate:value": (v) => (isEnglish.value = v),
                value: isEnglish.value,
                size: "small",
              },
              {
                icon: () => (isEnglish.value ? "🇬🇧" : "🇮🇩"),
              }
            ),
          ]),
      },
    ],
  },
  {
    key: "notifikasi",
    label: () =>
      h(
        "div",
        {
          class: "flex  items-center justify-between w-[150px] ",
          onClick: (e) => e.stopPropagation(),
        },
        [
          h("span", { class: "text-sm" }, "Notifikasi"),
          h(resolveComponent("n-select"), {
            value: isNotificationAllowed.value,
            "onUpdate:value": (v) => (isNotificationAllowed.value = v),
            options: [
              { label: "Izinkan", value: true },
              { label: "Tidak", value: false },
            ],
            size: "small",
            bordered: false,
          }),
        ]
      ),
    icon: () => h(PhBell, { size: 16 }),
  },
  {
    label: () => h("span", { class: "text-red-500" }, "Keluar"),
    key: "keluar",
    icon: () => h(PhSignOut, { size: 16, class: "text-red-500" }),
  },
];

const toggleSidebar = () => {
  if (window.innerWidth >= collapseBreakpoint) {
    isCollapsed.value = !isCollapsed.value;
  }
};

const isActive = (path) => {
  return route.path.startsWith(path);
};

const handleMenuOptionsSelect = (key) => {
  if (key === "profil") {
    showProfileModal.value = true;
  } else if (key === "keluar") {
    auth.logout();
    router.push("/masuk");
    message.success("Keluar Berhasil!");
  }
};
</script>

