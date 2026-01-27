<script setup>
import { ref, onMounted, nextTick, watch } from "vue";
import { SendOutlined, LoadingOutlined } from "@ant-design/icons-vue";
import { useChatStore } from "../../../stores/chat";
import MarkdownIt from "markdown-it";

const store = useChatStore();
const input = ref("");
const messagesContainer = ref(null);
const md = new MarkdownIt();

// Auto scroll to bottom
const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

watch(() => store.messages.length, scrollToBottom);
watch(
  () => store.messages[store.messages.length - 1]?.content,
  scrollToBottom,
  { deep: true },
);

onMounted(async () => {
  await store.loadHistory();
  scrollToBottom();
});

const handleSend = () => {
  if (!input.value.trim() || store.isStreaming) return;

  store.sendMessage(input.value);
  input.value = "";
};

const handleKeydown = (e) => {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    handleSend();
  }
};
</script>

<template>
  <div class="flex flex-col h-full">
    <!-- Messages Area -->
    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
      <div
        v-for="(msg, index) in store.messages"
        :key="index"
        class="flex"
        :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
      >
        <div
          class="max-w-[80%] rounded-lg p-3"
          :class="[
            msg.role === 'user'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-800',
          ]"
        >
          <!-- Assistant Message (Markdown) -->
          <div
            v-if="msg.role === 'assistant'"
            class="chat-markdown"
            v-html="md.render(msg.content)"
          ></div>

          <!-- User Message (Text) -->
          <div v-else class="whitespace-pre-wrap">{{ msg.content }}</div>

          <!-- Streaming Indicator -->
          <div v-if="msg.isStreaming" class="mt-2 text-gray-400">
            <LoadingOutlined />
          </div>
        </div>
      </div>
    </div>

    <!-- Input Area -->
    <div class="border-t border-gray-200 p-4 bg-white">
      <div class="flex gap-2">
        <textarea
          v-model="input"
          @keydown="handleKeydown"
          placeholder="Nhập tin nhắn... (Enter để gửi, Shift+Enter xuống dòng)"
          class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none h-[50px] max-h-[150px]"
          :disabled="store.isStreaming"
        ></textarea>

        <button
          @click="handleSend"
          :disabled="!input.trim() || store.isStreaming"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center min-w-[50px]"
        >
          <LoadingOutlined v-if="store.isStreaming" />
          <SendOutlined v-else />
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar for webkit browsers */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 20px;
}
</style>

<style>
/* Markdown Content Styles - Isolated from Tailwind */
.chat-markdown {
  font-size: 0.95rem;
  line-height: 1.6;
}

.chat-markdown p {
  margin-bottom: 0.75em;
}

.chat-markdown h1,
.chat-markdown h2,
.chat-markdown h3 {
  font-weight: 600;
  margin-top: 1em;
  margin-bottom: 0.5em;
  color: inherit;
}

.chat-markdown h1 {
  font-size: 1.5em;
}
.chat-markdown h2 {
  font-size: 1.25em;
}
.chat-markdown h3 {
  font-size: 1.1em;
}

.chat-markdown ul,
.chat-markdown ol {
  margin-bottom: 0.75em;
  padding-left: 1.5em;
}

.chat-markdown ul {
  list-style-type: disc;
}
.chat-markdown ol {
  list-style-type: decimal;
}

.chat-markdown li {
  margin-bottom: 0.25em;
}

.chat-markdown code {
  background-color: rgba(0, 0, 0, 0.1);
  padding: 0.2em 0.4em;
  border-radius: 4px;
  font-family: monospace;
  font-size: 0.9em;
}

.chat-markdown pre {
  background-color: #1e293b;
  color: #e2e8f0;
  padding: 1em;
  border-radius: 8px;
  overflow-x: auto;
  margin-bottom: 0.75em;
}

.chat-markdown pre code {
  background-color: transparent;
  padding: 0;
  color: inherit;
  font-size: 0.85em;
}

.chat-markdown strong {
  font-weight: 600;
}

.chat-markdown blockquote {
  border-left: 4px solid #cbd5e1;
  padding-left: 1em;
  font-style: italic;
  color: #64748b;
  margin-bottom: 0.75em;
}
</style>
