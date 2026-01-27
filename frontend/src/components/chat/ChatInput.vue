<script setup>
import { ref } from "vue";
import { SendOutlined, LoadingOutlined } from "@ant-design/icons-vue";

const props = defineProps({
  loading: Boolean,
});

const emit = defineEmits(["send"]);

const input = ref("");
const textareaRef = ref(null);

const adjustTextareaHeight = () => {
  const el = textareaRef.value;
  if (!el) return;
  el.style.height = "auto";
  el.style.height = Math.min(el.scrollHeight, 150) + "px";
};

const handleSend = () => {
  if (!input.value.trim() || props.loading) return;
  emit("send", input.value);
  input.value = "";
  adjustTextareaHeight();
};

const handleKeydown = (e) => {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    handleSend();
  }
};
</script>

<template>
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
        :disabled="loading"
        rows="1"
      ></textarea>

      <button
        @click="handleSend"
        :disabled="!input.trim() || loading"
        class="absolute bottom-2 right-2 p-1.5 rounded-lg text-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        :class="
          !input.trim() || loading
            ? 'bg-gray-300'
            : 'bg-blue-600 hover:bg-blue-700'
        "
      >
        <LoadingOutlined v-if="loading" class="text-lg" />
        <SendOutlined v-else class="text-lg text-white" />
      </button>
    </div>
    <div class="text-center mt-2 text-xs text-gray-400">
      OikOS AI có thể mắc sai sót.
    </div>
  </div>
</template>
