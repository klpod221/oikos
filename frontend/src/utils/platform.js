import { platform } from "@tauri-apps/plugin-os";

/**
 * Check if the app is running in a Tauri environment
 * @returns {boolean}
 */
export const isTauri = () => {
  return !!(window.__TAURI__ || window.__TAURI_INTERNALS__ || window.isTauri);
};

/**
 * Get the current OS platform
 * @returns {Promise<string>} 'android', 'ios', 'macos', 'windows', 'linux', or 'web'
 */
export const getPlatform = async () => {
  if (!isTauri()) return "web";

  let attempts = 0;
  const maxAttempts = 3;

  while (attempts < maxAttempts) {
    try {
      // Use type() as it's the standard for OS checks 'android', 'ios', etc.
      // Dynamic import to avoid load-time errors if plugin is missing?
      // No, import is already top-level.
      return await platform();
    } catch (error) {
      console.warn(
        `Attempt ${attempts + 1} failed to check OS platform:`,
        error,
      );
      attempts++;
      if (attempts >= maxAttempts) {
        return "unknown";
      }
      // Wait 500ms before retrying
      await new Promise((resolve) => setTimeout(resolve, 500));
    }
  }
  return "unknown";
};

/**
 * Check if the current platform is Android
 * @returns {Promise<boolean>}
 */
export const isAndroid = async () => {
  const os = await getPlatform();
  return os === "android";
};
