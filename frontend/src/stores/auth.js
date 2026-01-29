/**
 * Auth Store
 *
 * Manages authentication state:
 * - User profile
 * - Auth token
 * - Login/Register/Logout actions
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { authService } from "../services/auth.service";
import api from "../utils/axios";

export const useAuthStore = defineStore("auth", () => {
  const user = ref(null);
  const token = ref(localStorage.getItem("token"));
  const loading = ref(false);
  const error = ref(null);

  const isAuthenticated = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.role === "admin");

  const login = async (credentials) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await authService.login(credentials);
      token.value = res.data.data.token;
      user.value = res.data.data.user;
      localStorage.setItem("token", token.value);
      api.defaults.headers.common.Authorization = `Bearer ${token.value}`;
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Đăng nhập thất bại";
      return false;
    } finally {
      loading.value = false;
    }
  };

  const register = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await authService.register(data);
      token.value = res.data.data.token;
      user.value = res.data.data.user;
      localStorage.setItem("token", token.value);
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Đăng ký thất bại";
      return false;
    } finally {
      loading.value = false;
    }
  };

  const logout = async () => {
    try {
      await authService.logout();
    } finally {
      token.value = null;
      user.value = null;
      localStorage.removeItem("token");
      delete api.defaults.headers.common.Authorization;
    }
  };

  const fetchUser = async () => {
    if (!token.value) return;
    loading.value = true;
    try {
      const res = await authService.getProfile();
      user.value = res.data.data;
    } catch (e) {
      console.error("Failed to fetch user:", e);
      error.value =
        e.response?.data?.message || "Không thể lấy thông tin người dùng";
    } finally {
      loading.value = false;
    }
  };

  const setAuthData = (data) => {
    token.value = data.token;
    user.value = data.user;
    localStorage.setItem("token", token.value);
    api.defaults.headers.common.Authorization = `Bearer ${token.value}`;
  };

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
    fetchUser,
    setAuthData,
  };
});
