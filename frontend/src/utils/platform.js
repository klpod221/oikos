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
  try {
    return await platform();
  } catch (error) {
    console.warn("Failed to check OS platform:", error);
    return "unknown";
  }
};

/**
 * Check if the current platform is Android
 * @returns {Promise<boolean>}
 */
export const isAndroid = async () => {
  const os = await getPlatform();
  return os === "android";
};
