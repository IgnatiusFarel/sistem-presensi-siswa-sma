<template>
  <nav
    :class="[
      'px-5 py-8 flex items-center justify-between h-[60px] border-b transition-colors duration-300',
      theme.isDark
        ? 'bg-neutral-900 border-neutral-700 text-gray-200'
        : 'bg-white border-gray-300 text-gray-800',
    ]"
  >
    <div class="flex items-center">
      <h1
        :class="theme.isDark ? 'text-white' : 'text-gray-900'"
        class="text-2xl font-bold"
      >
        SPSS
      </h1>
    </div>

    <n-dropdown
      placement="bottom-end"
      trigger="click"
      :options="menuOptions"
      @select="handleMenuOptionsSelect"
    >
      <div
        :class="[
          'flex items-center gap-3 cursor-pointer rounded-full px-2 py-1 transition-colors border',
          theme.isDark
            ? 'bg-blue-900 border-blue-600 hover:bg-blue-800'
            : 'bg-blue-100 border-blue-500 hover:bg-blue-200',
        ]"
      >
        <PhCaretUpDown
          :size="20"
          v-show="!isCollapsed"
          :class="theme.isDark ? 'text-gray-300' : 'text-gray-700'"
        />
        <div class="flex flex-col items-end">
          <span
            :class="theme.isDark ? 'text-gray-100' : 'text-gray-800'"
            class="font-medium"
          >
            {{ user?.name || user?.email }}
          </span>
          <span
            :class="theme.isDark ? 'text-gray-400' : 'text-gray-500'"
            class="text-xs"
          >
            {{ user?.role || "Siswa" }}
          </span>
        </div>

        <n-avatar
          round
          alt="avatar"
          size="small"
          src="https://randomuser.me/api/portraits/men/1.jpg"
        />
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
  </nav>
</template>

<script setup>
import { ref, h, resolveComponent } from "vue";
import { useRouter } from "vue-router";
import { NAvatar, NDropdown, NImage, useMessage } from "naive-ui";
import {
  PhSignOut,
  PhGear,
  PhUser,
  PhCaretUpDown,
  PhBell,
} from "@phosphor-icons/vue";
import { useAuthStore } from "@/stores/Auth";
import { useThemeStore } from "@/stores/ThemeMode";
import { storeToRefs } from "pinia";

const router = useRouter();
const message = useMessage();
const auth = useAuthStore();
const { user } = storeToRefs(auth);

const theme = useThemeStore();
const isEnglish = ref(false);
const showProfileModal = ref(false);
const isNotificationAllowed = ref(false);
const isCollapsed = ref(false);

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
                "onUpdate:value": (v) => (theme.isDark = v),
                value: theme.isDark,
                size: "small",
              },
              {
                icon: () => (theme.isDark ? "🌙" : "☀️"),
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
    label: "Notifikasi",
    key: "notifikasi",
    icon: () => h(PhBell, { size: 16 }),
    children: [
      {
        label: () =>
          h(
            "div",
            {
              class: "flex items-center justify-between w-[150px]",
              onClick: (e) => e.stopPropagation(),
            },
            [
              h("span", { class: "text-sm" }, "Notifikasi"),
              h(
                resolveComponent("n-switch"),
                {
                  "onUpdate:value": (v) => (isNotificationAllowed.value = v),
                  value: isNotificationAllowed.value,
                  size: "small",
                },
                {
                  icon: () => (isNotificationAllowed.value ? "🔔" : "🔕"),
                }
              ),
            ]
          ),
      },
    ],
  },
  {
    label: () => h("span", { class: "text-red-500" }, "Keluar"),
    key: "keluar",
    icon: () => h(PhSignOut, { size: 16, class: "text-red-500" }),
  },
];

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

<style scoped></style>
