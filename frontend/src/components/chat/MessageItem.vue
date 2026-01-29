<script setup>
import { computed } from "vue";
import { UserOutlined, RobotOutlined } from "@ant-design/icons-vue";
import MarkdownIt from "markdown-it";
import hljs from "highlight.js";
import "highlight.js/styles/github-dark.css";
import { useAuthStore } from "../../stores/auth";

const props = defineProps({
  msg: Object,
});

const authStore = useAuthStore();

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

const formatTime = (isoString) => {
  if (!isoString) return "";
  try {
    const date = new Date(isoString);
    if (isNaN(date.getTime())) return "";
    return date.toLocaleTimeString("vi-VN", {
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (e) {
    return "";
  }
};
</script>

<template>
  <div
    class="flex gap-4"
    :class="msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'"
  >
    <!-- Avatar -->
    <div class="shrink-0 mt-1">
      <div
        v-if="msg.role === 'assistant'"
        class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white shadow-sm"
      >
        <RobotOutlined />
      </div>
      <a-avatar
        v-else
        :size="32"
        :src="authStore.user?.avatar"
        class="bg-gray-600 flex items-center justify-center text-white shadow-sm"
      >
        <template #icon v-if="!authStore.user?.avatar"
          ><UserOutlined
        /></template>
      </a-avatar>
    </div>

    <!-- Content Bubble -->
    <div class="flex flex-col max-w-[85%]">
      <div
        class="font-medium text-xs text-gray-400 mb-1 px-1 flex items-center gap-2"
        :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
      >
        <span v-if="msg.role !== 'user'">OikOS Assistant</span>
        <span v-if="msg.created_at" class="text-[10px] opacity-70">{{
          formatTime(msg.created_at)
        }}</span>
        <span v-if="msg.role === 'user'">{{
          authStore.user?.name || "Bạn"
        }}</span>
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
          class="prose prose-sm prose-gray max-w-none wrap-break-word [&_*]:!text-inherit"
          v-html="md.render(msg.content)"
        ></div>

        <!-- User Message (Text) -->
        <div v-else class="whitespace-pre-wrap wrap-break-word">
          {{ msg.content }}
        </div>

        <!-- Streaming Indicator - Typing dots -->
        <div v-if="msg.isStreaming" class="flex items-center gap-1 mt-2">
          <span class="typing-dot"></span>
          <span class="typing-dot"></span>
          <span class="typing-dot"></span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.typing-dot {
  width: 8px;
  height: 8px;
  background-color: #9ca3af;
  border-radius: 50%;
  animation: typing-bounce 1.4s infinite ease-in-out both;
}

.typing-dot:nth-child(1) {
  animation-delay: 0s;
}

.typing-dot:nth-child(2) {
  animation-delay: 0.16s;
}

.typing-dot:nth-child(3) {
  animation-delay: 0.32s;
}

@keyframes typing-bounce {
  0%,
  80%,
  100% {
    transform: scale(0.8);
    opacity: 0.4;
  }
  40% {
    transform: scale(1.2);
    opacity: 1;
  }
}
</style>
