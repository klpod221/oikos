const COMMANDS: &[&str] = &["check_permission", "open_settings"];

fn main() {
    tauri_build::mobile::PluginBuilder::new()
        .android_path("android")
        .run();
}
