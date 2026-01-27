/**
 * Integration Store
 *
 * Manages health integration state:
 * - User Stats (weight, height, etc.)
 * - User Goals (calories, macros)
 * - Energy Balance
 * - Macros Progress
 */
import { defineStore } from "pinia";
import { ref } from "vue";
import { integrationService } from "../services/integration.service";
import { message } from "ant-design-vue";

export const useIntegrationStore = defineStore("integration", () => {
  // State
  const userStats = ref(null);
  const userGoals = ref(null);
  const energyBalance = ref(null);
  const macrosProgress = ref({
    protein: { current: 0, target: 150, percentage: 0 },
    carbs: { current: 0, target: 200, percentage: 0 },
    fat: { current: 0, target: 60, percentage: 0 },
  });
  const loading = ref(false);

  // User Stats Actions
  const fetchUserStats = async () => {
    try {
      const res = await integrationService.getUserStats();
      userStats.value = res.data;
    } catch (e) {
      console.error("Failed to fetch user stats", e);
    }
  };

  const saveUserStats = async (data) => {
    loading.value = true;
    try {
      await integrationService.saveUserStats(data);
      message.success("Đã cập nhật thông số");
      await fetchUserStats();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi cập nhật thông số");
      return false;
    } finally {
      loading.value = false;
    }
  };

  // User Goals Actions
  const fetchUserGoals = async () => {
    try {
      const res = await integrationService.getUserGoals();
      userGoals.value = res.data;
    } catch (e) {
      console.error("Failed to fetch user goals", e);
    }
  };

  const saveUserGoals = async (data) => {
    loading.value = true;
    try {
      await integrationService.saveUserGoals(data);
      message.success("Đã cập nhật mục tiêu");
      await fetchUserGoals();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi cập nhật mục tiêu");
      return false;
    } finally {
      loading.value = false;
    }
  };

  // Energy Balance Actions
  const fetchEnergyBalance = async (date) => {
    try {
      const res = await integrationService.getEnergyBalance({ date });
      energyBalance.value = res.data;
    } catch (e) {
      console.error("Failed to fetch energy balance", e);
    }
  };

  // Macros Progress Actions
  const fetchMacrosProgress = async (date) => {
    try {
      const res = await integrationService.getMacrosProgress({ date });
      macrosProgress.value = res.data || macrosProgress.value;
    } catch (e) {
      console.error("Failed to fetch macros progress", e);
    }
  };

  // Energy Trend State
  const energyTrend = ref([]);

  // Goal Warnings State
  const goalWarnings = ref([]);

  // Energy Trend Actions
  const fetchEnergyTrend = async (params = {}) => {
    try {
      const res = await integrationService.getEnergyTrend(params);
      energyTrend.value = res.data || [];
    } catch (e) {
      console.error("Failed to fetch energy trend", e);
    }
  };

  // Goal Warnings Actions
  const fetchGoalWarnings = async () => {
    try {
      const res = await integrationService.getGoalWarnings();
      goalWarnings.value = res.data || [];
    } catch (e) {
      console.error("Failed to fetch goal warnings", e);
    }
  };

  return {
    // State
    userStats,
    userGoals,
    energyBalance,
    macrosProgress,
    loading,
    energyTrend,
    goalWarnings,

    // Actions
    fetchUserStats,
    saveUserStats,
    fetchUserGoals,
    saveUserGoals,
    fetchEnergyBalance,
    fetchMacrosProgress,
    fetchEnergyTrend,
    fetchGoalWarnings,
  };
});
