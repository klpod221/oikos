/**
 * Chat Service
 * Handles SSE streaming manually to support Bearer token authentication.
 */

import config from "../config/app";

export const chatService = {
  /**
   * Stream a chat message to the backend
   * @param {string} message - User message
   * @param {string} token - Auth token
   * @param {Function} onChunk - API for handling each content chunk
   * @param {Function} onComplete - Callback when stream ends
   * @param {Function} onError - Callback for errors
   */
  async streamMessage(message, token, onChunk, onComplete, onError) {
    try {
      const response = await fetch(`${config.api.baseUrl}/chat/send`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
          Accept: "text/event-stream",
        },
        body: JSON.stringify({ message }),
      });

      if (!response.ok) throw new Error("Network response was not ok");

      const reader = response.body.getReader();
      const decoder = new TextDecoder();
      let buffer = "";

      while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        buffer += decoder.decode(value, { stream: true });
        const lines = buffer.split("\n");

        // Keep the last partial line in the buffer
        buffer = lines.pop() || "";

        for (const line of lines) {
          if (line.startsWith("data: ")) {
            const jsonStr = line.slice(6);
            if (jsonStr === "[DONE]") {
              onComplete();
              return;
            }
            try {
              const data = JSON.parse(jsonStr);
              if (data.type === "content") {
                onChunk(data.content);
              } else if (data.type === "done") {
                onComplete();
                return;
              }
            } catch (e) {
              console.error("Error parsing SSE JSON:", e);
            }
          }
        }
      }
      onComplete();
    } catch (error) {
      if (onError) onError(error);
    }
  },

  async getHistory(params = {}) {
    const { default: api } = await import("../utils/axios");
    // Params can include: page, limit, search
    return (await api.get("/chat/history", { params })).data;
  },

  async clearHistory() {
    const { default: api } = await import("../utils/axios");
    await api.delete("/chat/clear");
  },
};
