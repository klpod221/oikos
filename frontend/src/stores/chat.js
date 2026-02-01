import { defineStore } from "pinia";
import { ref } from "vue";
import { chatService } from "../services/chat.service";
import { useAuthStore } from "./auth";

export const useChatStore = defineStore("chat", () => {
  const messages = ref([]);
  const isStreaming = ref(false);
  const isLoading = ref(false);
  const isLoadingMore = ref(false);
  const hasMore = ref(true);
  const auth = useAuthStore();

  const currentPage = ref(1);

  async function loadHistory(loadMore = false) {
    if (loadMore) {
      if (isLoadingMore.value || !hasMore.value) return;
      isLoadingMore.value = true;
    } else {
      isLoading.value = true;
      messages.value = []; // Reset on fresh load
      hasMore.value = true;
      currentPage.value = 1;
    }

    try {
      // Use page parameter
      const params = {
        limit: 10,
        page: loadMore ? currentPage.value + 1 : 1,
      };

      const response = await chatService.getHistory(params);
      const data = response.data || [];
      const meta = response.meta;

      if (Array.isArray(data)) {
        const history = data.map((msg) => ({
          id: msg.id,
          role: msg.role,
          content: msg.content,
        }));

        // Update pagination state
        if (meta) {
          currentPage.value = meta.current_page;
          hasMore.value = meta.current_page < meta.last_page;
        } else {
          // Fallback if meta is missing
          hasMore.value = response.has_more ?? false;
        }

        if (loadMore) {
          messages.value = [...history, ...messages.value];
        } else {
          // If fresh load and empty, add welcome message
            messages.value = history;
        }
      }
    } catch (error) {
      console.error(error);
    } finally {
      isLoading.value = false;
      isLoadingMore.value = false;
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

  async function clearHistory() {
    await chatService.clearHistory();
    messages.value = [];
  }

  return {
    messages,
    isStreaming,
    isLoading,
    isLoadingMore, // Added
    hasMore, // Added
    loadHistory,
    sendMessage,
    clearHistory,
    getService: () => chatService,
  };
});
