/**
 * Axios Instance
 *
 * Configured axios instance with base URL and interceptors.
 * - Base URL: /api
 * - Request Interceptor: Attaches Bearer token
 * - Response Interceptor: Handles 401 Unauthorized (redirects to login)
 */
import axios from "axios";

const api = axios.create({
  baseURL: "/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("token");
      if (!["/login", "/register"].includes(globalThis.location.pathname)) {
        globalThis.location.href = "/login";
      }
    }

    // Handle Maintenance Mode
    if (error.response?.status === 503 && error.response.data?.maintenance) {
      if (globalThis.location.pathname !== "/maintenance") {
        globalThis.location.href = "/maintenance";
      }
    }

    return Promise.reject(error);
  },
);

export default api;
