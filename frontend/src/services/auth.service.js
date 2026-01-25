/**
 * Auth Service
 *
 * API endopints for authentication and user profile.
 */
import api from "../utils/axios";

export const authService = {
  /**
   * Login user
   * @param {Object} credentials - { email, password }
   */
  login: (credentials) => api.post("/auth/login", credentials),

  /**
   * Register new user
   * @param {Object} data - User registration data
   */
  register: (data) => api.post("/auth/register", data),

  /**
   * Logout user
   */
  logout: () => api.post("/auth/logout"),

  /**
   * Get current user profile
   */
  getProfile: () => api.get("/auth/me"),
};
