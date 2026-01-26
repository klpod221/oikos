<template>
  <div class="macro-progress-container">
    <h3 class="section-title">Daily Macros</h3>

    <div class="macro-bars">
      <!-- Protein -->
      <div class="macro-item">
        <div class="macro-header">
          <span class="macro-label">💪 Protein</span>
          <span class="macro-values">
            {{ progress.protein.current }}g / {{ progress.protein.target }}g
          </span>
        </div>
        <div class="progress-bar-wrapper">
          <div
            class="progress-bar protein"
            :style="{ width: progress.protein.percentage + '%' }"
          >
            <span class="percentage">{{ progress.protein.percentage }}%</span>
          </div>
        </div>
        <div class="status-badge" :class="progress.protein.status">
          {{ getStatusText(progress.protein.status) }}
        </div>
      </div>

      <!-- Carbs -->
      <div class="macro-item">
        <div class="macro-header">
          <span class="macro-label">🍞 Carbs</span>
          <span class="macro-values">
            {{ progress.carbs.current }}g / {{ progress.carbs.target }}g
          </span>
        </div>
        <div class="progress-bar-wrapper">
          <div
            class="progress-bar carbs"
            :style="{ width: progress.carbs.percentage + '%' }"
          >
            <span class="percentage">{{ progress.carbs.percentage }}%</span>
          </div>
        </div>
        <div class="status-badge" :class="progress.carbs.status">
          {{ getStatusText(progress.carbs.status) }}
        </div>
      </div>

      <!-- Fat -->
      <div class="macro-item">
        <div class="macro-header">
          <span class="macro-label">🥑 Fat</span>
          <span class="macro-values">
            {{ progress.fat.current }}g / {{ progress.fat.target }}g
          </span>
        </div>
        <div class="progress-bar-wrapper">
          <div
            class="progress-bar fat"
            :style="{ width: progress.fat.percentage + '%' }"
          >
            <span class="percentage">{{ progress.fat.percentage }}%</span>
          </div>
        </div>
        <div class="status-badge" :class="progress.fat.status">
          {{ getStatusText(progress.fat.status) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  progress: {
    type: Object,
    required: true,
    default: () => ({
      protein: { current: 0, target: 150, percentage: 0, status: "below" },
      carbs: { current: 0, target: 200, percentage: 0, status: "below" },
      fat: { current: 0, target: 60, percentage: 0, status: "below" },
    }),
  },
});

function getStatusText(status) {
  const statusMap = {
    met: "✓ Met",
    close: "~ Close",
    below: "↓ Below",
  };
  return statusMap[status] || status;
}
</script>

<style scoped>
.macro-progress-container {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-title {
  margin: 0 0 1.5rem 0;
  font-size: 1.25rem;
  color: #1f2937;
}

.macro-bars {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.macro-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.macro-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.95rem;
}

.macro-label {
  font-weight: 600;
  color: #374151;
}

.macro-values {
  color: #6b7280;
  font-variant-numeric: tabular-nums;
}

.progress-bar-wrapper {
  height: 32px;
  background: #f3f4f6;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
}

.progress-bar {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 0.75rem;
  transition: width 0.4s ease;
  position: relative;
  min-width: 60px;
}

.progress-bar.protein {
  background: linear-gradient(90deg, #4ade80, #22c55e);
}

.progress-bar.carbs {
  background: linear-gradient(90deg, #fbbf24, #f59e0b);
}

.progress-bar.fat {
  background: linear-gradient(90deg, #f87171, #ef4444);
}

.percentage {
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.status-badge {
  align-self: flex-end;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-badge.met {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.close {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.below {
  background: #fee2e2;
  color: #991b1b;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .macro-header {
    font-size: 0.875rem;
  }

  .percentage {
    font-size: 0.75rem;
  }
}
</style>
