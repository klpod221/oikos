package com.oikos.app.notification_listener

import android.app.Activity
import android.content.Intent
import android.provider.Settings
import app.tauri.annotation.Command
import app.tauri.annotation.TauriPlugin
import app.tauri.plugin.JSObject
import app.tauri.plugin.Plugin
import app.tauri.plugin.Invoke

@TauriPlugin
class NotificationPlugin(private val activity: Activity) : Plugin(activity) {
    companion object {
        var instance: NotificationPlugin? = null
    }

    override fun load(webView: app.tauri.plugin.WebView) {
        instance = this
        super.load(webView)
    }

    @Command
    fun checkPermission(invoke: Invoke) {
        val enabledListeners = Settings.Secure.getString(activity.contentResolver, "enabled_notification_listeners")
        val packageName = activity.packageName
        
        // Simple check: does the enabled list contain our package?
        // Note: This might be slightly inaccurate if multiple services exist in same package, usually it's ComponentName.
        // Better: check for ComponentName string.
        val componentName = "${packageName}/${NotificationService::class.java.canonicalName}"
        val isEnabled = enabledListeners != null && (enabledListeners.contains(packageName) || enabledListeners.contains(componentName))
        
        val ret = JSObject()
        ret.put("hasPermission", isEnabled)
        invoke.resolve(ret)
    }

    @Command
    fun openSettings(invoke: Invoke) {
        val intent = Intent(Settings.ACTION_NOTIFICATION_LISTENER_SETTINGS)
        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
        activity.startActivity(intent)
        invoke.resolve()
    }

    fun sendNotificationEvent(data: JSObject) {
         trigger("notification_received", data)
    }
}
