package com.oikos.app.notification_listener

import android.service.notification.NotificationListenerService
import android.service.notification.StatusBarNotification
import app.tauri.plugin.JSObject

class NotificationService : NotificationListenerService() {
    override fun onNotificationPosted(sbn: StatusBarNotification?) {
        super.onNotificationPosted(sbn)
        if (sbn == null) return

        val packageName = sbn.packageName
        val notification = sbn.notification
        val extras = notification.extras
        
        val title = extras.getString("android.title") ?: ""
        val text = extras.getCharSequence("android.text")?.toString() ?: ""
        
        val data = JSObject()
        data.put("packageName", packageName)
        data.put("title", title)
        data.put("text", text)
        data.put("postTime", sbn.postTime.toString())

        NotificationPlugin.instance?.sendNotificationEvent(data)
    }

    override fun onNotificationRemoved(sbn: StatusBarNotification?) {
        // Ignore
    }
}
