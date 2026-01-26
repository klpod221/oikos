<template>
  <div class="energy-balance-dashboard">
    <div class="dashboard-header">
      <h2>Energy Balance Dashboard</h2>
      <div class="view-toggle">
        <button
          @click="viewMode = 'daily'"
          :class="{ active: viewMode === 'daily' }"
        >
          Daily
        </button>
        <button
          @click="viewMode = 'weekly'"
          :class="{ active: viewMode === 'weekly' }"
        >
          Weekly
        </button>
      </div>
    </div>

    <!-- Goal Warnings -->
    <div v-if="warnings.length > 0" class="warnings-section">
      <h3>⚠️ Goal Warnings</h3>
      <div class="warnings-list">
        <div
          v-for="(warning, index) in warnings"
          :key="index"
          class="warning-item"
          :class="warning.severity"
        >
          <span class="warning-type">{{ getWarningIcon(warning.type) }}</span>
          <span class="warning-message">{{ warning.message }}</span>
        </div>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card bmr">
        <div class="stat-icon">🔥</div>
        <div class="stat-content">
          <div class="stat-label">BMR</div>
          <div class="stat-value">{{ summary?.bmr || 0 }} kcal</div>
          <div class="stat-sublabel">Base Metabolic Rate</div>
        </div>
      </div>

      <div class="stat-card tdee">
        <div class="stat-icon">⚡</div>
        <div class="stat-content">
          <div class="stat-label">TDEE</div>
          <div class="stat-value">{{ summary?.tdee || 0 }} kcal</div>
          <div class="stat-sublabel">Total Daily Expenditure</div>
        </div>
      </div>

      <div class="stat-card intake">
        <div class="stat-icon">🍽️</div>
        <div class="stat-content">
          <div class="stat-label">Intake</div>
          <div class="stat-value">{{ summary?.total_calories || 0 }} kcal</div>
          <div class="stat-sublabel">Food Consumed</div>
        </div>
      </div>

      <div class="stat-card balance" :class="balanceStatus">
        <div class="stat-icon">{{ balanceIcon }}</div>
        <div class="stat-content">
          <div class="stat-label">Balance</div>
          <div class="stat-value">
            {{ summary?.energy_balance >= 0 ? "+" : ""
            }}{{ summary?.energy_balance || 0 }} kcal
          </div>
          <div class="stat-sublabel">{{ balanceText }}</div>
        </div>
      </div>
    </div>

    <!-- Chart Section -->
    <div class="chart-section">
      <h3>Energy Trend</h3>
      <canvas ref="chartCanvas"></canvas>
    </div>

    <!-- Workout Summary -->
    <div v-if="summary?.workout_calories > 0" class="workout-summary">
      <h3>🏋️ Today's Workouts</h3>
      <div class="workout-stat">
        <span>Calories Burned:</span>
        <strong>{{ summary.workout_calories }} kcal</strong>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
  userId: {
    type: Number,
    required: true,
  },
  selectedDate: {
    type: String,
    default: () => new Date().toISOString().split("T")[0],
  },
});

const viewMode = ref("daily");
const summary = ref(null);
const warnings = ref([]);
const chartCanvas = ref(null);
let chartInstance = null;

const balanceStatus = computed(() => {
  if (!summary.value) return "";
  return summary.value.energy_balance >= 0 ? "surplus" : "deficit";
});

const balanceIcon = computed(() => {
  return balanceStatus.value === "surplus" ? "📈" : "📉";
});

const balanceText = computed(() => {
  return balanceStatus.value === "surplus"
    ? "Caloric Surplus"
    : "Caloric Deficit";
});

onMounted(async () => {
  await loadDashboardData();
  initializeChart();
});

watch(
  () => props.selectedDate,
  async () => {
    await loadDashboardData();
    updateChart();
  },
);

watch(viewMode, async () => {
  await loadDashboardData();
  updateChart();
});

/**
 * Load dashboard data from API
 */
async function loadDashboardData() {
  try {
    // Load daily summary
    const summaryResponse = await fetch(
      `/api/integration/energy-balance?user_id=${props.userId}&date=${props.selectedDate}`,
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      },
    );

    if (summaryResponse.ok) {
      summary.value = await summaryResponse.json();
    }

    // Load warnings
    const warningsResponse = await fetch(
      `/api/integration/goal-warnings?user_id=${props.userId}&date=${props.selectedDate}`,
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      },
    );

    if (warningsResponse.ok) {
      warnings.value = await warningsResponse.json();
    }
  } catch (error) {
    console.error("Failed to load dashboard data:", error);
  }
}

/**
 * Initialize Chart.js
 */
function initializeChart() {
  if (!chartCanvas.value) return;

  const ctx = chartCanvas.value.getContext("2d");

  chartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: [],
      datasets: [
        {
          label: "Intake",
          data: [],
          borderColor: "#3b82f6",
          backgroundColor: "rgba(59, 130, 246, 0.1)",
          tension: 0.4,
        },
        {
          label: "TDEE",
          data: [],
          borderColor: "#ef4444",
          backgroundColor: "rgba(239, 68, 68, 0.1)",
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "top",
        },
        tooltip: {
          mode: "index",
          intersect: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: "Calories (kcal)",
          },
        },
      },
    },
  });

  updateChart();
}

/**
 * Update chart data
 */
async function updateChart() {
  if (!chartInstance) return;

  try {
    const days = viewMode.value === "daily" ? 7 : 30;
    const endDate = new Date(props.selectedDate);
    const startDate = new Date(endDate);
    startDate.setDate(startDate.getDate() - days);

    const response = await fetch(
      `/api/integration/energy-balance/trend?user_id=${props.userId}&start_date=${startDate.toISOString().split("T")[0]}&end_date=${endDate.toISOString().split("T")[0]}`,
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      },
    );

    if (!response.ok) return;

    const data = await response.json();

    chartInstance.data.labels = data.map((d) =>
      new Date(d.date).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
      }),
    );
    chartInstance.data.datasets[0].data = data.map((d) => d.total_calories);
    chartInstance.data.datasets[1].data = data.map((d) => d.tdee);

    chartInstance.update();
  } catch (error) {
    console.error("Failed to update chart:", error);
  }
}

/**
 * Get warning icon based on type
 */
function getWarningIcon(type) {
  const icons = {
    calories: "🔢",
    protein: "💪",
    energy_balance: "⚖️",
    workout: "🏋️",
  };
  return icons[type] || "⚠️";
}
</script>

<style scoped>
.energy-balance-dashboard {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-header h2 {
  margin: 0;
  color: #1f2937;
}

.view-toggle {
  display: flex;
  gap: 0.5rem;
  background: #f3f4f6;
  padding: 0.25rem;
  border-radius: 8px;
}

.view-toggle button {
  padding: 0.5rem 1rem;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  color: #6b7280;
  transition: all 0.2s;
}

.view-toggle button.active {
  background: white;
  color: #1f2937;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.warnings-section {
  margin-bottom: 2rem;
  padding: 1rem;
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
  border-radius: 8px;
}

.warnings-section h3 {
  margin: 0 0 1rem 0;
  color: #92400e;
}

.warnings-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.warning-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: white;
  border-radius: 6px;
}

.warning-item.high {
  border-left: 3px solid #ef4444;
}

.warning-item.medium {
  border-left: 3px solid #f59e0b;
}

.warning-type {
  font-size: 1.25rem;
}

.warning-message {
  color: #1f2937;
  font-size: 0.875rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-radius: 12px;
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
}

.stat-card.bmr {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
}

.stat-card.tdee {
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
}

.stat-card.intake {
  background: linear-gradient(135deg, #d1fae5, #a7f3d0);
}

.stat-card.balance.surplus {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
}

.stat-card.balance.deficit {
  background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
}

.stat-icon {
  font-size: 2.5rem;
}

.stat-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  font-variant-numeric: tabular-nums;
}

.stat-sublabel {
  font-size: 0.75rem;
  color: #9ca3af;
}

.chart-section {
  margin-bottom: 2rem;
}

.chart-section h3 {
  margin: 0 0 1rem 0;
  color: #1f2937;
}

canvas {
  max-height: 300px;
}

.workout-summary {
  padding: 1.5rem;
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  border-radius: 12px;
}

.workout-summary h3 {
  margin: 0 0 1rem 0;
  color: #1e40af;
}

.workout-stat {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.125rem;
}

.workout-stat strong {
  color: #1e40af;
  font-size: 1.25rem;
}

/* Mobile */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .stat-value {
    font-size: 1.25rem;
  }
}
</style>
