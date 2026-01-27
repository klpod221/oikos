import api from "../utils/axios";

/**
 * Dashboard Service
 *
 * API endpoints for dashboard statistics and external data.
 */
export const dashboardService = {
  /**
   * Get statistics for a period
   * @param {Object} params - { period, start_date, end_date }
   */
  getStatistics: (params) => api.get("/statistics", { params }),

  /**
   * Force refresh statistics cache
   */
  refreshCache: () => api.post("/statistics/refresh"),

  /**
   * Get external data (weather, exchange rates, metals)
   * @param {number} lat - Latitude
   * @param {number} lon - Longitude
   */
  getExternalData: (lat, lon) =>
    api.get("/external-data", { params: { lat, lon } }),
};
