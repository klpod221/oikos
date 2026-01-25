<!--
  Dashboard.vue

  Main dashboard view.
  Displays:
  - Welcome message
  - Period selector
  - External data widget
  - Statistics summary (charts, cards)
-->
<script setup>
import { onMounted } from "vue";
import { useDashboardStore } from "../../stores/dashboard";
import { useAuthStore } from "../../stores/auth";
import { ReloadOutlined } from "@ant-design/icons-vue";

// Components
import StatsSummary from "../../components/dashboard/StatsSummary.vue";
import PeriodSelector from "../../components/dashboard/PeriodSelector.vue";
import IncomeExpenseChart from "../../components/dashboard/IncomeExpenseChart.vue";
import CategoryPieChart from "../../components/dashboard/CategoryPieChart.vue";
import TopExpensesTable from "../../components/dashboard/TopExpensesTable.vue";
import ExternalDataWidget from "../../components/dashboard/ExternalDataWidget.vue";

const dashboard = useDashboardStore();
const auth = useAuthStore();

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

    <!-- External Data Widget (Weather, Rates, Metals) -->
    <ExternalDataWidget />

    <!-- Loading State -->
    <div
      v-if="dashboard.loading && !dashboard.statistics"
      class="flex items-center justify-center py-20"
    >
      <a-spin size="large" />
    </div>

    <!-- Custom Period - Waiting for date selection -->
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
