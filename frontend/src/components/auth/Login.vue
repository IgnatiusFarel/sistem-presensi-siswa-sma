<template>
  <div
    class="min-h-screen flex items-center justify-center px-4 transition-colors duration-300"
    :class="themeStore.isDark ? 'bg-neutral-900' : 'bg-gray-50'"
  >
    <div
      class="w-full max-w-[400px] rounded-2xl shadow-lg overflow-hidden transition-all duration-300"
      :class="
        themeStore.isDark ? 'bg-neutral-800 shadow-2xl' : 'bg-white shadow-lg'
      "
    >
      <div class="py-2 px-6 text-center space-y-2">
        <h1
          class="text-4xl font-extrabold transition-colors duration-300"
          :class="themeStore.isDark ? 'text-white' : 'text-gray-800'"
        >
          SPSS
        </h1>
        <p
          class="transition-colors duration-300"
          :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'"
        >
          Sistem Presensi Siswa SMA
        </p>
        <h3
          class="text-2xl font-semibold transition-colors duration-300"
          :class="themeStore.isDark ? 'text-gray-200' : 'text-gray-700'"
        >
          Masuk
        </h3>
      </div>

      <n-form
        ref="formRef"
        :model="formData"
        :rules="rules"
        @submit.prevent="handleLogin"
        class="px-6 pb-8"
        require-mark-placement="right"
      >
        <n-form-item label="Email" path="email">
          <template #label>
            <span
              class="block text-sm font-medium transition-colors duration-300"
              :class="themeStore.isDark ? 'text-gray-200' : 'text-gray-700'"
            >
              Email
            </span>
          </template>
          <n-input
            v-model:value="formData.email"
            type="email"
            placeholder="Masukkan Email Anda..."
            @input="() => formRef?.restoreValidation()"
          >
            <template #suffix>
              <n-icon
                :component="PhEnvelopeSimple"
                :size="20"
                class="transition-colors duration-300"
                :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'"
              />
            </template>
          </n-input>
        </n-form-item>

        <n-form-item label="Kata Sandi" path="password">
          <template #label>
            <span
              class="block text-sm font-medium transition-colors duration-300"
              :class="themeStore.isDark ? 'text-gray-200' : 'text-gray-700'"
            >
              Kata Sandi
            </span>
          </template>
          <n-input
            v-model:value="formData.password"
            type="password"
            show-password-on="click"
            placeholder="Masukkan Kata Sandi Anda..."
            @input="() => formRef?.restoreValidation()"
          >
            <template #password-visible-icon>
              <n-icon :size="20" :component="PhEye" />
            </template>
            <template #password-invisible-icon>
              <n-icon :size="20" :component="PhEyeSlash" />
            </template>
          </n-input>
        </n-form-item>

        <n-button
          type="primary"
          attr-type="submit"
          block
          :loading="loading"
          :disabled="loading"
          class="transition-transform transform active:scale-95"
        >
          <span v-if="loading">Memproses...</span>
          <span v-else>Masuk</span>
        </n-button>
      </n-form>

      <div
        class="px-6 py-4 border-t text-center transition-all duration-300"
        :class="themeStore.isDark ? 'border-neutral-600' : 'border-gray-200'"
      >
        <a
          href="/perubahan-informasi"
          class="text-sm font-medium hover:underline transition-colors duration-300"
          :class="
            themeStore.isDark
              ? 'text-blue-400 hover:text-blue-300'
              : 'text-gray-700 hover:text-gray-900'
          "
        >
          Tidak Bisa Masuk?
        </a>
        <p
          class="text-xs underline mt-1 transition-colors duration-300"
          :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'"
        >
          Hubungi Admin untuk melakukan perubahan informasi data akun
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useMessage } from "naive-ui";
import { useAuthStore } from "@/stores/Auth";
import { useThemeStore } from "@/stores/ThemeMode";
import { PhEye, PhEyeSlash, PhEnvelopeSimple } from "@phosphor-icons/vue";

const router = useRouter();
const message = useMessage();
const formRef = ref(null);
const loading = ref(false);
const themeStore = useThemeStore();
const authStore = useAuthStore();

const formData = ref({
  email: "",
  password: "",
});

const rules = {
  email: [
    {
      required: true,
      message: "Email wajib diisi",
      trigger: ["input", "blur", "submit"],
    },
    {
      pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      message: "Format email tidak valid",
      trigger: ["input", "blur", "submit"],
    },
  ],
  password: [
    {
      required: true,
      message: "Password wajib diisi",
      trigger: ["input", "blur", "submit"],
    },
    {
      min: 8,
      message: "Password minimal 8 karakter",
      trigger: ["input", "blur", "submit"],
    },
  ],
};

const handleLogin = async () => {
  message.destroyAll();
  try {
    loading.value = true;

    await formRef.value?.validate();
    const response = await authStore.login(formData.value);
    message.success(response.message);
    const userRole = authStore.user.role;

    const redirectPath =
      userRole === "superadmin" ? "/presensi" : "/presensi-siswa";

    setTimeout(() => {
      router.push(redirectPath);
    }, 500);
  } catch (error) {
    message.error(
      typeof error === "string" ? error : "Masukan Email dan Kata Sandi"
    );
  } finally {
    loading.value = false;
  }
};
</script>
