<script setup>
import { useRegisterSW } from "virtual:pwa-register/vue";

const { offlineReady, needRefresh, updateServiceWorker } = useRegisterSW();

const close = () => {
  offlineReady.value = false;
  needRefresh.value = false;
};
</script>

<template>
  <div
    v-if="offlineReady || needRefresh"
    class="fixed bottom-4 right-4 z-50 p-4 bg-white dark:bg-zinc-800 rounded-lg shadow-xl border border-zinc-200 dark:border-zinc-700 flex flex-col gap-2 max-w-sm transition-all"
    role="alert"
  >
    <div class="mb-2">
      <span v-if="offlineReady"> App ready to work offline </span>
      <span v-else>
        New content available, click on reload button to update.
      </span>
    </div>
    <div class="flex gap-2">
      <button
        v-if="needRefresh"
        @click="updateServiceWorker()"
        class="px-3 py-1.5 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 rounded-md text-sm font-medium hover:opacity-90 active:scale-95 transition-transform"
      >
        Reload
      </button>
      <button
        @click="close"
        class="px-3 py-1.5 border border-zinc-200 dark:border-zinc-700 rounded-md text-sm font-medium hover:bg-zinc-100 dark:hover:bg-zinc-800 active:scale-95 transition-all outline-none"
      >
        Close
      </button>
    </div>
  </div>
</template>
