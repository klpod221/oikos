/**
 * Settings Store
 *
 * Manages user settings state:
 * - User Profile updating
 * - Preferences (Currency, Language, etc.)
 */
import { defineStore } from "pinia";
import { ref } from "vue";
import { settingsService } from "../services/settings.service";
import { message } from "ant-design-vue";
import { useAuthStore } from "./auth";
import config from "../config/app";

export const useSettingsStore = defineStore("settings", () => {
  const settings = ref(null);
  const loading = ref(false);
  const auth = useAuthStore();

  const fetchSettings = async () => {
    loading.value = true;
    try {
      const res = await settingsService.getProfile();
      settings.value = res.data.data.settings || {};
      // Update auth user data if needed (e.g. avatar changed elsewhere)
      auth.user = res.data.data.user;
    } catch (error) {
      console.error("Failed to fetch settings", error);
    } finally {
      loading.value = false;
    }
  };

  const updateProfile = async (data) => {
    try {
      const res = await settingsService.updateProfile(data);
      auth.user = res.data.data;
      message.success("Cập nhật hồ sơ thành công");
      return true;
    } catch (error) {
      message.error(error.response?.data?.message || "Cập nhật hồ sơ thất bại");
      return false;
    }
  };

  const updateAvatar = async (file) => {
    try {
      const formData = new FormData();
      formData.append("avatar", file);
      const res = await settingsService.updateAvatar(formData);
      // Update local user state with new avatar URL
      if (auth.user) {
        // Backend returns path like 'storage/avatars/filename.jpg'
        // Use config.api.rootUrl which already removes /api suffix
        auth.user.avatar = `${config.api.rootUrl}/${res.data.data.avatar}`;
      }
      message.success("Cập nhật ảnh đại diện thành công");
      return true;
    } catch (error) {
      message.error("Tải lên ảnh đại diện thất bại");
      return false;
    }
  };

  const updatePreferences = async (data) => {
    try {
      const res = await settingsService.updatePreferences(data);
      settings.value = res.data.data;
      message.success("Đã lưu tùy chọn");
      return true;
    } catch (error) {
      message.error("Lưu tùy chọn thất bại");
      return false;
    }
  };

  const changePassword = async (data) => {
    try {
      await settingsService.changePassword(data);
      message.success("Đổi mật khẩu thành công");
      return true;
    } catch (error) {
      if (error.response?.data?.errors?.old_password) {
        message.error(error.response.data.errors.old_password[0]);
      } else {
        message.error("Đổi mật khẩu thất bại");
      }
      return false;
    }
  };

  return {
    settings,
    loading,
    fetchSettings,
    updateProfile,
    updateAvatar,
    updatePreferences,
    changePassword,
  };
});
