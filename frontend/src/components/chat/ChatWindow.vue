<script setup>
import { ref, onMounted, nextTick, watch } from "vue";
import {
  LoadingOutlined,
  SearchOutlined,
  DeleteOutlined,
} from "@ant-design/icons-vue";
import { Popconfirm } from "ant-design-vue";
import { useChatStore } from "../../stores/chat";

// Components
import MessageItem from "./MessageItem.vue";
import ChatInput from "./ChatInput.vue";
import SearchMessageModal from "./SearchMessageModal.vue";

import { authService } from "../../services/auth.service";

const store = useChatStore();
const showSearchModal = ref(false);
const chatEnabled = ref(true); // Default true
const messagesContainer = ref(null);
const topSentinel = ref(null);
const userScrolledUp = ref(false);
const showScrollButton = ref(false); // Restore state
let observer = null;
// Markdown logic moved to MessageItem

// Scroll Logic
const scrollToBottom = async (force = false, behavior = "smooth") => {
  await nextTick();
  if (!messagesContainer.value) return;

  const container = messagesContainer.value;
  const isAtBottom =
    container.scrollHeight - container.scrollTop - container.clientHeight < 100;

  if (force || isAtBottom) {
    container.scrollTo({ top: container.scrollHeight, behavior });
    userScrolledUp.value = false;
  }
};

const handleScroll = () => {
  if (!messagesContainer.value) return;
  const container = messagesContainer.value;

  // Show/Hide scroll button
  if (
    container.scrollHeight - container.scrollTop - container.clientHeight >
    200
  ) {
    showScrollButton.value = true;
    userScrolledUp.value = true; // User intentionally scrolled up
  } else {
    showScrollButton.value = false;
    userScrolledUp.value = false;
  }
};

const setupObserver = () => {
  if (observer) observer.disconnect();

  observer = new IntersectionObserver(
    async (entries) => {
      const entry = entries[0];

      if (entry.isIntersecting && store.hasMore && !store.isLoadingMore) {
        const container = messagesContainer.value;
        const oldHeight = container.scrollHeight;

        await store.loadHistory(true);

        await nextTick();
        const newHeight = container.scrollHeight;
        container.scrollTop = newHeight - oldHeight;
      }
    },
    {
      root: messagesContainer.value,
      threshold: 0.1,
      rootMargin: "50px 0px 0px 0px",
    },
  );

  if (topSentinel.value) {
    observer.observe(topSentinel.value);
  }
};

// Watchers
watch(
  () => store.messages.length,
  async (newLen, oldLen) => {
    if (newLen > oldLen && !store.isLoadingMore) {
      // New message added (not history load) -> auto scroll if at bottom
      scrollToBottom();
    }
  },
);

watch(
  () => store.messages[store.messages.length - 1]?.content,
  () => {
    // Streaming content update
    if (!userScrolledUp.value) {
      scrollToBottom(true, "auto"); // Force instant scroll
    }
  },
  { deep: true },
);

onMounted(async () => {
  try {
    const response = await authService.getPublicSettings();
    let enabled = response.data.enable_ai_chat;
    if (enabled === "true") enabled = true;
    if (enabled === "false") enabled = false;
    if (enabled !== undefined) chatEnabled.value = enabled;
  } catch (e) {
    console.error(e);
  }

  await store.loadHistory();
  // Ensure we are scrolled to bottom BEFORE observing
  await scrollToBottom(true, "auto");
  // Add a small delay to ensure DOM is stable
  setTimeout(() => {
    setupObserver();
  }, 100);
});

// Search Logic
const openSearch = () => {
  showSearchModal.value = true;
};

// Clear History Logic
const confirmClearHistory = async () => {
  await store.clearHistory();
  // store.messages logic moved to store action
};

const handleSend = async (content) => {
  store.sendMessage(content);
  userScrolledUp.value = false;
  setTimeout(() => scrollToBottom(true, "auto"), 100);
};
</script>

<template>
  <div class="flex flex-col h-full bg-white relative">
    <!-- Header with Actions -->
    <!-- Queue Header Actions -->
    <div class="absolute top-4 right-4 z-20 flex gap-2">
      <button
        @click="openSearch"
        class="w-8 h-8 rounded-full bg-white text-gray-500 hover:text-blue-600 shadow-sm border border-gray-200 flex items-center justify-center transition-all"
        title="Tìm kiếm"
      >
        <SearchOutlined />
      </button>

      <Popconfirm
        title="Bạn có chắc chắn muốn xóa toàn bộ lịch sử?"
        ok-text="Xóa"
        cancel-text="Hủy"
        @confirm="confirmClearHistory"
      >
        <button
          class="w-8 h-8 rounded-full bg-white text-gray-500 hover:text-red-600 shadow-sm border border-gray-200 flex items-center justify-center transition-all"
          title="Xóa lịch sử"
        >
          <DeleteOutlined />
        </button>
      </Popconfirm>
    </div>

    <!-- Search Modal -->
    <SearchMessageModal v-model:open="showSearchModal" :store="store" />

    <!-- Messages Area -->
    <div
      ref="messagesContainer"
      @scroll="handleScroll"
      class="flex-1 overflow-y-auto px-4 py-6 scroll-smooth"
    >
      <!-- Sentinel for Infinite Scroll -->
      <div
        ref="topSentinel"
        class="h-4 w-full flex justify-center items-center"
      >
        <LoadingOutlined
          v-if="store.isLoadingMore"
          class="text-gray-400 text-sm"
        />
      </div>

      <div class="max-w-3xl mx-auto space-y-6">
        <!-- Welcome Message when no history -->
        <div
          v-if="store.messages.length === 0 && !store.isLoading"
          class="flex flex-col items-center justify-center py-12 text-center"
        >
          <div
            class="w-16 h-16 rounded-full bg-white flex items-center justify-center mb-4 shadow-sm border border-gray-100 p-2"
          >
            <img
              src="/logo.png"
              alt="OikOS"
              class="w-full h-full object-contain"
            />
          </div>
          <h2 class="text-xl font-semibold text-gray-800 mb-2">Xin chào! 👋</h2>
          <p class="text-gray-500 mb-6 max-w-md">
            Tôi là OikOS Assistant, trợ lý AI giúp bạn quản lý cuộc sống hàng
            ngày.
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left max-w-lg">
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
              <div class="font-medium text-gray-700 text-sm mb-1">
                💰 Tài chính
              </div>
              <p class="text-xs text-gray-500">
                Ghi chép thu chi, theo dõi số dư, báo cáo tài chính
              </p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
              <div class="font-medium text-gray-700 text-sm mb-1">
                🥗 Dinh dưỡng
              </div>
              <p class="text-xs text-gray-500">
                Tra cứu nguyên liệu, lên kế hoạch bữa ăn
              </p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
              <div class="font-medium text-gray-700 text-sm mb-1">
                🏋️ Thể dục
              </div>
              <p class="text-xs text-gray-500">
                Tìm bài tập, xem lịch tập luyện
              </p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
              <div class="font-medium text-gray-700 text-sm mb-1">
                📊 Thống kê
              </div>
              <p class="text-xs text-gray-500">
                Báo cáo tuần, mục tiêu sức khỏe
              </p>
            </div>
          </div>
          <p class="text-gray-400 text-sm mt-6">
            Hãy nhắn tin để bắt đầu cuộc trò chuyện!
          </p>
        </div>

        <MessageItem
          v-for="(msg, index) in store.messages"
          :key="msg.id || index"
          :msg="msg"
        />
      </div>

      <!-- Bottom spacing -->
      <div class="h-4"></div>
    </div>

    <!-- Scroll to Bottom Button -->
    <button
      v-if="showScrollButton"
      @click="scrollToBottom(true)"
      class="absolute bottom-24 left-1/2 transform -translate-x-1/2 bg-white border border-gray-200 text-gray-600 rounded-full w-10 h-10 flex items-center justify-center shadow-lg hover:bg-gray-50 transition-all z-10"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
          clip-rule="evenodd"
          transform="rotate(180 10 10)"
        />
      </svg>
    </button>

    <!-- Input Area -->
    <ChatInput
      :loading="store.isStreaming"
      @send="handleSend"
      v-if="chatEnabled"
    />

    <div
      v-else
      class="p-4 border-t border-gray-100 bg-gray-50 text-center text-gray-500"
    >
      <p>Tính năng AI Chat đã bị tạm khóa bởi quản trị viên.</p>
    </div>
  </div>
</template>

<style scoped>
/* Minimal Scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.3);
  border-radius: 20px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.6);
}

/* Custom transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
