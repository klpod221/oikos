/**
 * Application Configuration
 *
 * Centralizes access to environment variables.
 */

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || "/api";
const rootUrl = apiBaseUrl.replace(/\/api\/?$/, "");

export const config = {
  api: {
    baseUrl: apiBaseUrl,
    rootUrl: rootUrl,
    googleAuthUrl: `${apiBaseUrl}/auth/google/url`,
    googleAuthCallback: `${apiBaseUrl}/auth/google/callback`,
    timeout: 60000,
  },
  app: {
    name: import.meta.env.VITE_APP_NAME || "Oikos",
    env: import.meta.env.MODE,
  },
};

export default config;
