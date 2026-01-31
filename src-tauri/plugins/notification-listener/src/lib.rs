use tauri::{
    plugin::{Builder, TauriPlugin},
    Runtime,
};

pub fn init<R: Runtime>() -> TauriPlugin<R> {
    Builder::new("notification-listener")
        .setup(|app, api| {
            #[cfg(target_os = "android")]
            api.register_android_plugin(
                "com.oikos.app.notification_listener",
                "NotificationPlugin",
            )?;
            Ok(())
        })
        .build()
}
