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
      :options="menuOptions"
      @select="handleMenuOptionsSelect"
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
import { ref, h, onMounted } from "vue";
import { useRouter } from 'vue-router';
import { NAvatar, NDropdown, useMessage} from 'naive-ui';
import { PhSignOut, PhGear, PhUser, PhCaretUpDown, PhBell,} from '@phosphor-icons/vue';
import { useAuthStore } from "@/stores/Auth";
import { storeToRefs } from 'pinia';

const router = useRouter();
const message = useMessage();
const auth = useAuthStore();
const { user } = storeToRefs(auth);

const isEnglish = ref(false);
const isDarkTheme = ref(false);
const showProfileModal = ref(false);
const isNotificationAllowed = ref(false);

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
