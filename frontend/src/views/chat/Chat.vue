<script setup>
import ChatWindow from "./components/ChatWindow.vue";
</script>

<template>
  <div class="h-full flex flex-col">
    <div class="mb-4">
      <h1 class="text-2xl font-bold text-gray-800">AI Assistant</h1>
      <p class="text-gray-600">Trợ lý tài chính và sức khỏe của bạn</p>
    </div>

    <div
      class="flex-1 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200"
    >
      <ChatWindow />
    </div>
  </div>
</template>
