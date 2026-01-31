<script setup>
import { ref, watch, onMounted } from "vue";
import { useRoute } from "vue-router";
import { MessageOutlined, CloseOutlined } from "@ant-design/icons-vue";
import ChatWindow from "./ChatWindow.vue";

const route = useRoute();
const isOpen = ref(false);
const isVisible = ref(true);

const toggleChat = () => {
  isOpen.value = !isOpen.value;
};

// Auto-hide when on the main chat page
watch(
  () => route.path,
  (path) => {
    if (path.startsWith("/chat")) {
      isVisible.value = false;
      isOpen.value = false;
    } else {
      isVisible.value = true;
    }
  },
  { immediate: true },
);
</script>

<template>
  <div
    v-if="isVisible"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end"
  >
    <!-- Chat Window Popover -->
    <transition name="slide-fade">
      <div
        v-if="isOpen"
        class="mb-4 w-[90vw] sm:w-[400px] h-[70vh] sm:h-[600px] bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200 flex flex-col"
      >
        <!-- Header -->
        <div
          class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50"
        >
          <h3 class="font-semibold text-gray-700">OikOS Assistant</h3>
          <button
            @click="isOpen = false"
            class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-md hover:bg-gray-200/50"
          >
            <CloseOutlined />
          </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-hidden relative">
          <ChatWindow />
        </div>
      </div>
    </transition>

    <!-- FAB -->
    <button
      @click="toggleChat"
      class="w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 transform hover:scale-105 active:scale-95"
      :class="
        isOpen
          ? 'bg-gray-200 text-gray-600'
          : 'bg-blue-600 text-white! hover:bg-blue-700 hover:shadow-blue-500/30'
      "
    >
      <CloseOutlined v-if="isOpen" class="text-xl" />
      <MessageOutlined v-else class="text-xl" />
    </button>
  </div>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
</style>
