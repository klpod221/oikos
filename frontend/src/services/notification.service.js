/**
 * Notification Service
 *
 * API endpoints for mobile notifications.
 */
import api from "../utils/axios";

export const notificationService = {
  /**
   * Send notification to backend for processing
   * @param {object} data { package_name, title, text, timestamp }
   */
  send: (data) => {
    return api.post("/notifications", {
      package_name: data.packageName || data.package_name,
      title: data.title,
      content: data.text || data.content, // Supports both 'text' (from app) and 'content' (general)
      timestamp: data.timestamp,
    });
  },
};
