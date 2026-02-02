<script setup>
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "./stores/auth";
import DefaultLayout from "./layouts/DefaultLayout.vue";
import AuthLayout from "./layouts/AuthLayout.vue";
import ReloadPrompt from "./components/ReloadPrompt.vue";
import { listen } from "@tauri-apps/api/event";

import { notificationService } from "./services/notification.service";

const route = useRoute();
const authStore = useAuthStore();
const layout = computed(() => (route.meta.guest ? AuthLayout : DefaultLayout));

const isInitializing = ref(true);

// Listen for Android Notifications (Custom Event from Bridge)
const notificationHandler = async (event) => {
  const payload = event.detail; // CustomEvent detail
  console.log("New Notification (Bridge):", payload);
  try {
    await notificationService.send(payload);
    console.log("Notification sent to backend");
  } catch (err) {
    console.error("Failed to send notification to backend", err);
  }
};

onMounted(async () => {
  window.addEventListener("notification_received", notificationHandler);

  if (authStore.token) {
    await authStore.fetchUser();
  }
  isInitializing.value = false;
});

// Clean up listener on unmount
onUnmounted(() => {
  window.removeEventListener("notification_received", notificationHandler);
});
</script>

<template>
  <div
    v-if="isInitializing"
    class="flex items-center justify-center min-h-screen bg-white"
  >
    <div class="flex flex-col items-center gap-4 animate-pulse">
      <img src="/logo.png" alt="OikOS Logo" class="h-16 w-auto" />
    </div>
  </div>
  <component :is="layout" v-else>
    <router-view />
  </component>
  <ReloadPrompt />
</template>
