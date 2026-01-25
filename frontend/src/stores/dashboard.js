import { defineStore } from "pinia";
import { ref } from "vue";
import { dashboardService } from "../services/dashboard.service";
import { message } from "ant-design-vue";
import dayjs from "dayjs";

export const useDashboardStore = defineStore("dashboard", () => {
  // State
  const statistics = ref(null);
  const selectedPeriod = ref("this_month");
  const customRange = ref({ start: null, end: null });
  const loading = ref(false);
  const lastFetched = ref(null);

  // Actions
  const fetchStatistics = async (period = null, start = null, end = null) => {
    loading.value = true;
    try {
      const params = {};

      if (period) {
        params.period = period;
        selectedPeriod.value = period;
      } else {
        params.period = selectedPeriod.value;
      }

      if (params.period === "custom") {
        params.start_date = start || customRange.value.start;
        params.end_date = end || customRange.value.end;
        customRange.value = { start: params.start_date, end: params.end_date };
      }

      const res = await dashboardService.getStatistics(params);
      statistics.value = res.data.data;
      lastFetched.value = dayjs().format();
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to fetch statistics");
      console.error("Failed to fetch statistics", e);
    } finally {
      loading.value = false;
    }
  };

  const setPeriod = async (period) => {
    selectedPeriod.value = period;
    // Don't fetch for custom - wait until user picks dates
    if (period !== "custom") {
      await fetchStatistics(period);
    }
  };

  const setCustomRange = async (start, end) => {
    customRange.value = { start, end };
    selectedPeriod.value = "custom";
    await fetchStatistics("custom", start, end);
  };

  const refreshStatistics = async () => {
    try {
      await dashboardService.refreshCache();
      message.success("Cache refreshed");
      await fetchStatistics();
    } catch (e) {
      message.error("Failed to refresh cache");
    }
  };

  return {
    // State
    statistics,
    selectedPeriod,
    customRange,
    loading,
    lastFetched,
    // Actions
    fetchStatistics,
    setPeriod,
    setCustomRange,
    refreshStatistics,
  };
});
