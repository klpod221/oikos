<script setup>
import { ref, onMounted, nextTick, watch, computed } from "vue";
import {
  SendOutlined,
  LoadingOutlined,
  UserOutlined,
  RobotOutlined,
} from "@ant-design/icons-vue";
import { useChatStore } from "../../../stores/chat";
import MarkdownIt from "markdown-it";
import hljs from "highlight.js";
import "highlight.js/styles/github-dark.css";

const store = useChatStore();
const input = ref("");
const messagesContainer = ref(null);
const textareaRef = ref(null);
const topSentinel = ref(null);
let observer = null;

// Configure MarkdownIt with highlight.js
const md = new MarkdownIt({
  highlight: function (str, lang) {
    if (lang && hljs.getLanguage(lang)) {
      try {
        return (
          '<pre class="hljs"><code>' +
          hljs.highlight(str, { language: lang, ignoreIllegals: true }).value +
          "</code></pre>"
        );
      } catch (__) {}
    }
    return (
      '<pre class="hljs"><code>' + md.utils.escapeHtml(str) + "</code></pre>"
    );
  },
});

// State
const showScrollButton = ref(false);
const userScrolledUp = ref(false);

// Auto-expand textarea
const adjustTextareaHeight = () => {
  const el = textareaRef.value;
  if (!el) return;
  el.style.height = "auto";
  el.style.height = Math.min(el.scrollHeight, 150) + "px";
};

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
  await store.loadHistory();
  // Ensure we are scrolled to bottom BEFORE observing
  await scrollToBottom(true, "auto");
  // Add a small delay to ensure DOM is stable
  setTimeout(() => {
    setupObserver();
  }, 100);
});

const handleSend = () => {
  if (!input.value.trim() || store.isStreaming) return;
  store.sendMessage(input.value);
  input.value = "";
  adjustTextareaHeight(); // Reset height
  userScrolledUp.value = false; // Reset scroll lock
  setTimeout(() => scrollToBottom(true, "auto"), 100);
};

const handleKeydown = (e) => {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    handleSend();
  }
};
</script>

<template>
  <div class="flex flex-col h-full bg-white relative">
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
        <div
          v-for="(msg, index) in store.messages"
          :key="msg.id || index"
          class="flex gap-4"
          :class="msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'"
        >
          <!-- Avatar -->
          <div class="flex-shrink-0 mt-1">
            <div
              v-if="msg.role === 'assistant'"
              class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white shadow-sm"
            >
              <RobotOutlined />
            </div>
            <div
              v-else
              class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-white shadow-sm"
            >
              <UserOutlined />
            </div>
          </div>

          <!-- Content Bubble -->
          <div class="flex flex-col max-w-[85%]">
            <div
              class="font-medium text-xs text-gray-400 mb-1 px-1"
              :class="msg.role === 'user' ? 'text-right' : 'text-left'"
            >
              {{ msg.role === "user" ? "Bạn" : "OikOS Assistant" }}
            </div>

            <div
              class="rounded-2xl px-4 py-3 text-[15px] leading-relaxed shadow-sm"
              :class="[
                msg.role === 'user'
                  ? 'bg-blue-600 text-white rounded-tr-sm'
                  : 'bg-gray-100 text-gray-800 rounded-tl-sm border border-gray-200',
              ]"
            >
              <!-- Assistant Message (Markdown) -->
              <div
                v-if="msg.role === 'assistant'"
                class="prose prose-sm max-w-none break-words dark:prose-invert"
                v-html="md.render(msg.content)"
              ></div>

              <!-- User Message (Text) -->
              <div v-else class="whitespace-pre-wrap break-words">
                {{ msg.content }}
              </div>

              <!-- Streaming Indicator -->
              <div
                v-if="msg.isStreaming"
                class="mt-2 text-gray-400 animate-pulse"
              >
                ●
              </div>
            </div>
          </div>
        </div>
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
    <div class="border-t border-gray-200 p-4 bg-white/95 backdrop-blur-sm">
      <div
        class="max-w-3xl mx-auto relative rounded-xl border border-gray-300 shadow-sm focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 bg-white transition-all"
      >
        <textarea
          ref="textareaRef"
          v-model="input"
          @keydown="handleKeydown"
          @input="adjustTextareaHeight"
          placeholder="Nhập tin nhắn... (Shift+Enter xuống dòng)"
          class="w-full py-3 pl-4 pr-12 bg-transparent border-none rounded-xl focus:ring-0 resize-none max-h-[150px] min-h-[50px] overflow-y-auto outline-none"
          :disabled="store.isStreaming"
          rows="1"
        ></textarea>

        <button
          @click="handleSend"
          :disabled="!input.trim() || store.isStreaming"
          class="absolute bottom-2 right-2 p-1.5 rounded-lg text-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
          :class="
            !input.trim() || store.isStreaming
              ? 'bg-gray-300'
              : 'bg-blue-600 hover:bg-blue-700'
          "
        >
          <LoadingOutlined v-if="store.isStreaming" class="text-lg" />
          <SendOutlined v-else class="text-lg" />
        </button>
      </div>
      <div class="text-center mt-2 text-xs text-gray-400">
        OikOS AI có thể mắc sai sót. Hãy kiểm tra lại thông tin quan trọng.
      </div>
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

<style>
/* Markdown Content Styles - Enhanced with Typography */
.prose p {
  margin-bottom: 0.8em;
}
.prose p:last-child {
  margin-bottom: 0;
}
.prose h1,
.prose h2,
.prose h3 {
  margin-top: 1em;
  font-weight: 600;
}
.prose ul,
.prose ol {
  padding-left: 1.25em;
  margin-bottom: 0.8em;
}
.prose ul {
  list-style-type: disc;
}
.prose ol {
  list-style-type: decimal;
}
.prose code {
  background-color: rgba(0, 0, 0, 0.06);
  padding: 0.2em 0.4em;
  border-radius: 4px;
  font-size: 0.9em;
  color: #e11d48;
}
.prose pre {
  background-color: #1f2937;
  padding: 0.75em;
  border-radius: 8px;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
.prose pre code {
  background-color: transparent;
  padding: 0;
  color: #f3f4f6;
  font-size: 0.9em;
}
</style>
