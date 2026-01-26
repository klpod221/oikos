import api from "../utils/axios";

/**
 * Integration Service
 *
 * API endpoints for health integration (energy balance, user stats, goals).
 */
export const integrationService = {
  // User Stats
  getUserStats: () => api.get("/integration/user-stats"),
  saveUserStats: (data) => api.post("/integration/user-stats", data),

  // User Goals
  getUserGoals: () => api.get("/integration/user-goals"),
  saveUserGoals: (data) => api.post("/integration/user-goals", data),

  // Energy Balance
  getEnergyBalance: (params = {}) =>
    api.get("/integration/energy-balance", { params }),
  getEnergyTrend: (params = {}) =>
    api.get("/integration/energy-balance/trend", { params }),

  // Goal Warnings
  getGoalWarnings: () => api.get("/integration/goal-warnings"),

  // Macros Progress
  getMacrosProgress: (params = {}) =>
    api.get("/nutrition/macros/progress", { params }),
};
