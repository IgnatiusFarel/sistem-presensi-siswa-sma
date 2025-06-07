import { defineStore } from "pinia";
import { ref, computed } from "vue";
import Api from "@/services/Api.js";

export const useAuthStore = defineStore("auth", () => {
  const token = ref(localStorage.getItem("auth_token") || null);
  const user = ref(JSON.parse(localStorage.getItem("user")) || null);
  const tokenExpiry = ref(localStorage.getItem("token_expiry") || null);

  function isTokenExpired() {
    if (!tokenExpiry.value) return true;
    return new Date() > new Date(tokenExpiry.value);
  }

  const isAuthenticated = computed(() => {
  return !!token.value && !isTokenExpired();
});


  async function login(credentials) {
    try {
      const response = await Api.post("/masuk", credentials);
      
      // Verify successful response
      if (!response.data.success) {
        throw response.data.message || 'Login gagal';
      }
      
      // Get data from correct structure
      const { token: authToken, user: userData } = response.data.data;    
      if (!authToken || !userData) {
        throw 'Data tidak lengkap dari server';
      }

      // Update state
      token.value = authToken;
      user.value = userData;

      // Set expiry date
      const expiryDate = new Date();
      expiryDate.setDate(expiryDate.getDate() + 7);
      tokenExpiry.value = expiryDate.toISOString();

      // Store in localStorage
      localStorage.setItem("auth_token", authToken);
      localStorage.setItem("user", JSON.stringify(userData));
      localStorage.setItem("token_expiry", tokenExpiry.value);

      return response.data;
    } catch (error) {
      // Handle different error types
      if (error.response) {
        throw error.response.data.message || 'Login gagal';
      } else if (typeof error === 'string') {
        throw error;
      } else {
        throw error.message || 'Terjadi kesalahan';
      }
    }
  }

  function logout() {
    token.value = null;
    user.value = null;
    tokenExpiry.value = null;

    localStorage.removeItem("auth_token");
    localStorage.removeItem("user");
    localStorage.removeItem("token_expiry");
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    logout,
  };
});
