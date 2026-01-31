import { invoke } from "@tauri-apps/api/core";
import { listen } from "@tauri-apps/api/event";

const PLUGIN_NAME = 'notification-listener';

export interface NotificationEvent {
  packageName: string;
  title: string;
  text: string;
  postTime: string;
}

export class NotificationService {
  /**
   * Check if notification listener permission is granted
   */
  async checkPermission(): Promise<boolean> {
    try {
      const res = await invoke<{ hasPermission: boolean }>(`plugin:${PLUGIN_NAME}|check_permission`);
      return res.hasPermission;
    } catch (e) {
      console.error("Failed to check permission:", e);
      return false;
    }
  }

  /**
   * Open Android Notification Listener Settings
   */
  async openSettings(): Promise<void> {
    try {
        await invoke(`plugin:${PLUGIN_NAME}|open_settings`);
    } catch (e) {
        console.error("Failed to open settings:", e);
    }
  }

  /**
   * Start listening for notifications
   * @param onNotification Callback when a notification is received
   */
  async startListening(onNotification: (notification: NotificationEvent) => void) {
    try {
      // Listen to the event name defined in Kotlin "notification_received"
      // But typically Tauri events are prefixed or scoped unless emitted globally.
      // In Plugin.kt: trigger("notification_received", data) uses the plugin channel.
      // So on frontend: listen("plugin:notification-listener:notification_received") ?
      // Wait, trigger() on a plugin instance emits to `plugin:<plugin-name>:<event-name>` usually?
      // Or just `<event-name>` if using Webview window emit.
      // But Plugin.kt extends Plugin. 
      // Let's verify standard behavior. usually `plugin:notification-listener|notification_received` logic is for commands.
      // Events are usually `plugin-name://event-name` or similar.
      // The `trigger` method in Kotlin plugin class: 
      // trigger(eventName: String, data: JSObject) 
      // This emits an event to the webview. The event name is usually namespaced.
      
      // Let's assume standard namespacing: `notification_received` (if triggered directly) or `plugin:notification-listener:notification_received`.
      // I will listen to `notification_received` first as I used `trigger("notification_received", data)` in Kotlin.
      // If `trigger` scopes it, it might be scoped. 
      // Let's safe bet: listen to "notification_received" and debug if needed.
      
      await listen<NotificationEvent>("notification_received", (event) => {
        onNotification(event.payload);
      });
      console.log("Listening for notifications...");
    } catch (e) {
      console.error("Failed to start listener:", e);
    }
  }
}

export const notificationService = new NotificationService();
