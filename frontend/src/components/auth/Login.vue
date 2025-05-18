<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div
      class="w-full max-w-[400px] bg-white rounded-2xl shadow-lg overflow-hidden"
    >
      <div class="py-2 px-6 text-center space-y-2">
        <h1 class="text-4xl font-extrabold text-gray-800">SPSS</h1>
        <p class="text-gray-500">Sistem Presensi Siswa SMA</p>
        <h3 class="text-2xl font-semibold text-gray-700">Masuk</h3>
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
            <span class="block text-sm font-medium text-gray-700">Email</span>
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
                class="text-gray-400"
              />
            </template>
          </n-input>
        </n-form-item>

        <n-form-item label="Kata Sandi" path="password">
          <template #label>
            <span class="block text-sm font-medium text-gray-700"
              >Kata Sandi</span
            >
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

        <button
          type="submit"
          :disabled="loading"
          class="w-full h-12 flex items-center justify-center bg-[#1E1E1E] hover:bg-[#353535] text-white font-semibold rounded-lg transition-all transform active:scale-95"
        >
          <span v-if="loading">Memproses...</span>
          <span v-else>Masuk</span>
        </button>
      </n-form>

      <div class="px-6 py-4 border-t border-gray-200 text-center">
        <a
          href="/perubahan-informasi"
          class="text-sm text-[#232323] font-medium hover:underline"
        >
          Tidak Bisa Masuk?
        </a>
        <p class="text-xs text-[#232323] underline">
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
import { useAuthStore } from "@/stores/Auth.js";
import { PhEye, PhEyeSlash, PhEnvelopeSimple } from "@phosphor-icons/vue";

const router = useRouter();
const message = useMessage();
const formRef = ref(null);
const loading = ref(false);
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
