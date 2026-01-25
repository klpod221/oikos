import api from "../utils/axios";

export const dashboardService = {
  // Get statistics for a period
  getStatistics: (params) => api.get("/statistics", { params }),

  // Force refresh cache
  refreshCache: () => api.post("/statistics/refresh"),

  // Get external data (weather, exchange rates, metals)
  getExternalData: (lat, lon) =>
    api.get("/external-data", { params: { lat, lon } }),
};
