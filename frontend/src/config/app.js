/**
 * Application Configuration
 *
 * Centralizes access to environment variables.
 */

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || "/api";
// Ensure no double slashes if base url ends with /
const cleanBaseUrl = apiBaseUrl.replace(/\/$/, "");
const rootUrl = cleanBaseUrl.replace(/\/api$/, "");

export const config = {
  api: {
    baseUrl: cleanBaseUrl,
    rootUrl: rootUrl,
    googleAuthUrl: `${cleanBaseUrl}/auth/google/url`,
    googleAuthCallback: `${cleanBaseUrl}/auth/google/callback`,
    timeout: 60000,
  },
  app: {
    name: import.meta.env.VITE_APP_NAME || "OikOS",
    env: import.meta.env.MODE,
  },
};

export default config;
