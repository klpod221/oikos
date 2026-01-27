/**
 * Chat Service
 * Handles SSE streaming manually to support Bearer token authentication.
 */

const API_URL = import.meta.env.VITE_API_URL || "http://localhost:8000/api";

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
      const response = await fetch(`${API_URL}/chat/send`, {
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

  async getHistory() {
    try {
      const token = localStorage.getItem("token"); // Or however auth token is accessed, but typically services use current auth state or pass it in.
      // Better to rely on axios interceptor if available, but here we used fetch for stream.
      // Let's use the standard axios instance if possible, but for consistency with streamMessage let's use fetch or just import api from utils.
      // Wait, auth.service.js uses `../utils/axios`. Let's use that for non-streaming calls.
      const { default: api } = await import("../utils/axios");
      const response = await api.get("/chat/history");
      return response.data;
    } catch (error) {
      console.error("Failed to load history:", error);
      throw error;
    }
  },

  async clearHistory() {
    const { default: api } = await import("../utils/axios");
    await api.post("/chat/clear");
  },
};
