<script setup>
import { computed } from "vue";
import { UserOutlined, RobotOutlined } from "@ant-design/icons-vue";
import MarkdownIt from "markdown-it";
import hljs from "highlight.js";
import "highlight.js/styles/github-dark.css";
import { useAuthStore } from "../../../stores/auth";

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
          class="prose prose-sm max-w-none break-words dark:prose-invert"
          v-html="md.render(msg.content)"
        ></div>

        <!-- User Message (Text) -->
        <div v-else class="whitespace-pre-wrap break-words">
          {{ msg.content }}
        </div>

        <!-- Streaming Indicator -->
        <div v-if="msg.isStreaming" class="mt-2 text-gray-400 animate-pulse">
          ●
        </div>
      </div>
    </div>
  </div>
</template>
