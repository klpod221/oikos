<script setup>
import { computed, ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "./stores/auth";
import DefaultLayout from "./layouts/DefaultLayout.vue";
import AuthLayout from "./layouts/AuthLayout.vue";
import ReloadPrompt from "./components/ReloadPrompt.vue";

const route = useRoute();
const authStore = useAuthStore();
const layout = computed(() => (route.meta.guest ? AuthLayout : DefaultLayout));

const isInitializing = ref(true);

onMounted(async () => {
  if (authStore.token) {
    await authStore.fetchUser();
  }
  isInitializing.value = false;
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
