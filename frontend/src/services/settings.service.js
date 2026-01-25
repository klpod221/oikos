import api from "../utils/axios";

export const settingsService = {
  // Get user profile and settings
  getProfile: () => api.get("/user/profile"),

  // Update profile (name, email)
  updateProfile: (data) => api.put("/user/profile", data),

  // Update avatar
  updateAvatar: (formData) =>
    api.post("/user/avatar", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    }),

  // Update preferences
  updatePreferences: (data) => api.put("/user/preferences", data),

  // Change password
  changePassword: (data) => api.put("/user/password", data),
};
