/**
 * Workout Timer Web Worker
 *
 * Provides accurate countdown timers that work even when the browser tab is in the background.
 * Handles start, pause, and stop commands from the main thread.
 */

let intervalId = null;
let remainingSeconds = 0;
let isPaused = false;

self.onmessage = (e) => {
  const { command, seconds } = e.data;

  if (command === "start") {
    // Start a new timer
    remainingSeconds = seconds;
    isPaused = false;

    // Clear any existing interval
    if (intervalId) {
      clearInterval(intervalId);
    }

    // Start countdown
    intervalId = setInterval(() => {
      if (!isPaused) {
        remainingSeconds--;

        // Send tick update to main thread
        self.postMessage({
          type: "tick",
          remaining: remainingSeconds,
        });

        // Check if timer completed
        if (remainingSeconds <= 0) {
          clearInterval(intervalId);
          intervalId = null;
          self.postMessage({ type: "complete" });
        }
      }
    }, 1000);

    // Confirm start
    self.postMessage({
      type: "started",
      remaining: remainingSeconds,
    });
  }

  if (command === "pause") {
    isPaused = true;
    self.postMessage({
      type: "paused",
      remaining: remainingSeconds,
    });
  }

  if (command === "resume") {
    isPaused = false;
    self.postMessage({
      type: "resumed",
      remaining: remainingSeconds,
    });
  }

  if (command === "stop") {
    // Stop and reset
    if (intervalId) {
      clearInterval(intervalId);
      intervalId = null;
    }
    remainingSeconds = 0;
    isPaused = false;

    self.postMessage({ type: "stopped" });
  }
};
