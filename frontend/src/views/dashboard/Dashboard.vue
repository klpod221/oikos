<!--
  Dashboard.vue

  Main dashboard view with:
  - Period selector
  - External data widget
  - Finance statistics & charts
  - Health overview (collapsible)
-->
<script setup>
import { ref, onMounted } from "vue";
import { useDashboardStore } from "../../stores/dashboard";
import { useAuthStore } from "../../stores/auth";
import { useIntegrationStore } from "../../stores/integration";
import { useWorkoutStore } from "../../stores/workout";
import {
  ReloadOutlined,
  HeartOutlined,
  DownOutlined,
  UpOutlined,
} from "@ant-design/icons-vue";

// Dashboard Components
import StatsSummary from "../../components/dashboard/StatsSummary.vue";
import PeriodSelector from "../../components/dashboard/PeriodSelector.vue";
import IncomeExpenseChart from "../../components/dashboard/IncomeExpenseChart.vue";
import CategoryPieChart from "../../components/dashboard/CategoryPieChart.vue";
import TopExpensesTable from "../../components/dashboard/TopExpensesTable.vue";
import ExternalDataWidget from "../../components/dashboard/ExternalDataWidget.vue";
import GoalWarnings from "../../components/integration/GoalWarnings.vue";

const dashboard = useDashboardStore();
const auth = useAuthStore();
const integration = useIntegrationStore();
const workout = useWorkoutStore();

// Health Section
const healthExpanded = ref(false);

onMounted(async () => {
  await dashboard.fetchStatistics();
  auth.fetchUser();
});

const handlePeriodChange = async (period) => {
  await dashboard.setPeriod(period);
};

const handleCustomRange = async (start, end) => {
  await dashboard.setCustomRange(start, end);
};

async function loadHealthData() {
  const today = new Date().toISOString().split("T")[0];
  const nextWeek = new Date();
  nextWeek.setDate(nextWeek.getDate() + 7);

  await Promise.all([
    integration.fetchMacrosProgress(today),
    workout.fetchUpcomingWorkouts(today, nextWeek.toISOString().split("T")[0]),
    integration.fetchGoalWarnings(),
  ]);
}

function toggleHealth() {
  healthExpanded.value = !healthExpanded.value;
  if (healthExpanded.value) {
    loadHealthData();
  }
}

function formatWorkoutDate(dateString) {
  const date = new Date(dateString);
  const today = new Date();
  if (date.toDateString() === today.toDateString()) return "Hôm nay";
  const tomorrow = new Date(today);
  tomorrow.setDate(today.getDate() + 1);
  if (date.toDateString() === tomorrow.toDateString()) return "Ngày mai";
  return date.toLocaleDateString("vi-VN", {
    weekday: "short",
    day: "numeric",
    month: "short",
  });
}
</script>

<template>
  <div class="space-y-3 sm:space-y-4">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-2"
    >
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800">
          Chào mừng trở lại, {{ auth.user?.name }}
        </h1>
      </div>
      <a-button
        @click="dashboard.refreshStatistics"
        :loading="dashboard.loading"
        size="middle"
      >
        <template #icon><ReloadOutlined /></template>
        <span class="hidden sm:inline">Làm mới</span>
      </a-button>
    </div>

    <!-- Period Selector -->
    <PeriodSelector
      :period="dashboard.selectedPeriod"
      @update:period="handlePeriodChange"
      @customRange="handleCustomRange"
    />

    <!-- External Data Widget -->
    <ExternalDataWidget />

    <!-- Health Section (Collapsible) -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
      <button
        @click="toggleHealth"
        class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors"
      >
        <div class="flex items-center gap-2">
          <HeartOutlined class="text-red-500" />
          <span class="font-semibold text-slate-800">Sức khỏe hôm nay</span>
        </div>
        <DownOutlined v-if="!healthExpanded" class="text-slate-400" />
        <UpOutlined v-else class="text-slate-400" />
      </button>

      <div v-if="healthExpanded" class="border-t border-slate-200 p-4">
        <a-spin :spinning="integration.loading || workout.loading">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Macros -->
            <div>
              <h4 class="text-sm font-semibold text-slate-700 mb-3">
                Dinh dưỡng
              </h4>
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <span class="text-xs text-slate-600 w-14">Protein</span>
                  <a-progress
                    :percent="
                      integration.macrosProgress.protein.percentage || 0
                    "
                    :show-info="false"
                    size="small"
                    stroke-color="#22c55e"
                    class="flex-1"
                  />
                  <span class="text-xs text-slate-500"
                    >{{ integration.macrosProgress.protein.current }}/{{
                      integration.macrosProgress.protein.target
                    }}g</span
                  >
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-slate-600 w-14">Carbs</span>
                  <a-progress
                    :percent="integration.macrosProgress.carbs.percentage || 0"
                    :show-info="false"
                    size="small"
                    stroke-color="#f59e0b"
                    class="flex-1"
                  />
                  <span class="text-xs text-slate-500"
                    >{{ integration.macrosProgress.carbs.current }}/{{
                      integration.macrosProgress.carbs.target
                    }}g</span
                  >
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-slate-600 w-14">Fat</span>
                  <a-progress
                    :percent="integration.macrosProgress.fat.percentage || 0"
                    :show-info="false"
                    size="small"
                    stroke-color="#ef4444"
                    class="flex-1"
                  />
                  <span class="text-xs text-slate-500"
                    >{{ integration.macrosProgress.fat.current }}/{{
                      integration.macrosProgress.fat.target
                    }}g</span
                  >
                </div>
              </div>
            </div>

            <!-- Upcoming Workouts -->
            <div>
              <h4 class="text-sm font-semibold text-slate-700 mb-3">
                Lịch tập sắp tới
              </h4>
              <div v-if="workout.upcomingWorkouts.length > 0" class="space-y-2">
                <div
                  v-for="w in workout.upcomingWorkouts.slice(0, 3)"
                  :key="w.schedule_id"
                  class="p-2 bg-slate-50 rounded text-sm"
                >
                  <div class="font-medium text-slate-800">
                    {{ w.routine_name }}
                  </div>
                  <div class="text-xs text-slate-500">
                    {{ formatWorkoutDate(w.scheduled_date) }}
                  </div>
                </div>
              </div>
              <div v-else class="text-sm text-slate-400 py-4 text-center">
                Không có lịch tập
              </div>
            </div>
          </div>

          <!-- Goal Warnings -->
          <GoalWarnings
            v-if="integration.goalWarnings.length > 0"
            :warnings="integration.goalWarnings"
            class="mt-4"
          />
        </a-spin>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="dashboard.loading && !dashboard.statistics"
      class="flex items-center justify-center py-20"
    >
      <a-spin size="large" />
    </div>

    <!-- Custom Period - Waiting -->
    <div
      v-else-if="
        dashboard.selectedPeriod === 'custom' && !dashboard.customRange.start
      "
      class="bg-white border border-slate-200 rounded-xl p-12 text-center"
    >
      <p class="text-slate-500">
        Vui lòng chọn khoảng thời gian để xem thống kê
      </p>
    </div>

    <!-- Statistics Content -->
    <template v-else-if="dashboard.statistics">
      <!-- Summary Cards -->
      <StatsSummary :summary="dashboard.statistics.summary" />

      <!-- Charts Row 1 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
        <IncomeExpenseChart :daily-trend="dashboard.statistics.daily_trend" />
        <CategoryPieChart :by-category="dashboard.statistics.by_category" />
      </div>

      <!-- Charts Row 2 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
        <TopExpensesTable :top-expenses="dashboard.statistics.top_expenses" />

        <!-- Savings Goals Progress -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 lg:p-6">
          <h3
            class="text-base lg:text-lg font-semibold text-slate-800 mb-2 lg:mb-4"
          >
            Tiến độ mục tiêu tiết kiệm
          </h3>
          <div class="space-y-3">
            <div
              v-for="goal in dashboard.statistics.savings_goals_progress"
              :key="goal.goal_id"
              class="p-3 bg-slate-50 rounded-lg"
            >
              <div class="flex justify-between items-center mb-2">
                <span class="font-medium text-slate-800">{{ goal.name }}</span>
                <span class="text-sm text-slate-500"
                  >{{ goal.current_progress }}%</span
                >
              </div>
              <a-progress
                :percent="goal.current_progress"
                :show-info="false"
                stroke-color="#10b981"
              />
              <div
                v-if="goal.monthly_required > 0"
                class="text-xs text-slate-500 mt-1"
              >
                Cần {{ goal.monthly_required.toLocaleString() }}₫/tháng
              </div>
            </div>
            <div
              v-if="dashboard.statistics.savings_goals_progress.length === 0"
              class="text-center py-8 text-slate-400"
            >
              Không có mục tiêu tiết kiệm nào đang hoạt động
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Empty State -->
    <div
      v-else
      class="bg-white border border-slate-200 rounded-xl p-12 text-center"
    >
      <p class="text-slate-400">Không có dữ liệu</p>
    </div>
  </div>
</template>
