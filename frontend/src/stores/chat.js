import { defineStore } from "pinia";
import { ref } from "vue";
import { chatService } from "../services/chat.service";
import { useAuthStore } from "./auth";

export const useChatStore = defineStore("chat", () => {
  const messages = ref([
    {
      role: "assistant",
      content: "Xin chào! Tôi có thể giúp gì cho bạn hôm nay?",
    },
  ]);
  const isStreaming = ref(false);
  const isLoading = ref(false);
  const auth = useAuthStore();

  async function loadHistory() {
    isLoading.value = true;
    try {
      const data = await chatService.getHistory();
      if (data.messages) {
        // Convert API messages to UI format
        const history = data.messages.map((msg) => ({
          role: msg.role,
          content: msg.content,
        }));

        // If history is empty, keep the welcome message, otherwise replace
        if (history.length > 0) {
          messages.value = history;
        }
      }
    } catch (error) {
      console.error(error);
    } finally {
      isLoading.value = false;
    }
  }

  async function sendMessage(content) {
    // Add user message
    messages.value.push({ role: "user", content });

    // Add placeholder assistant message
    const assistantMsgIndex =
      messages.value.push({
        role: "assistant",
        content: "",
        isStreaming: true,
      }) - 1;

    isStreaming.value = true;
    isLoading.value = true;

    try {
      await chatService.streamMessage(
        content,
        auth.token,
        (chunk) => {
          isLoading.value = false; // Got first chunk
          messages.value[assistantMsgIndex].content += chunk;
        },
        () => {
          isStreaming.value = false;
          messages.value[assistantMsgIndex].isStreaming = false;
        },
        (error) => {
          messages.value[assistantMsgIndex].content +=
            "\n[Error: " + error.message + "]";
          isStreaming.value = false;
          isLoading.value = false;
        },
      );
    } catch (e) {
      isLoading.value = false;
      isStreaming.value = false;
    }
  }

  return {
    messages,
    isStreaming,
    isLoading,
    loadHistory,
    sendMessage,
  };
});
