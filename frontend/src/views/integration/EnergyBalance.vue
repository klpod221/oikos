<script setup>
import { ref, onMounted, watch } from "vue";
import {
  ThunderboltOutlined,
  BarChartOutlined,
  SettingOutlined,
} from "@ant-design/icons-vue";
import EnergyBalanceDashboard from "../../components/integration/EnergyBalanceDashboard.vue";
import MacroProgressBar from "../../components/nutrition/MacroProgressBar.vue";
import { integrationService } from "../../services/integration.service";
import { useAuthStore } from "../../stores/auth";

const auth = useAuthStore();
const userId = auth.user?.id;

const selectedDate = ref(new Date().toISOString().split("T")[0]);
const activeTab = ref("dashboard");
const macroProgress = ref({
  protein: { current: 0, target: 150, percentage: 0, status: "below" },
  carbs: { current: 0, target: 200, percentage: 0, status: "below" },
  fat: { current: 0, target: 60, percentage: 0, status: "below" },
});
const userStats = ref(null);
const userGoals = ref(null);
const loading = ref(false);

onMounted(async () => {
  await Promise.all([loadMacroProgress(), loadUserStats(), loadUserGoals()]);
});

watch(selectedDate, async () => {
  await loadMacroProgress();
});

async function loadMacroProgress() {
  try {
    const response = await integrationService.getMacrosProgress({
      date: selectedDate.value,
    });
    macroProgress.value = response.data;
  } catch (error) {
    console.error("Failed to load macro progress:", error);
  }
}

async function loadUserStats() {
  loading.value = true;
  try {
    const response = await integrationService.getUserStats();
    userStats.value = response.data;
  } catch (error) {
    console.error("Failed to load user stats:", error);
  } finally {
    loading.value = false;
  }
}

async function loadUserGoals() {
  try {
    const response = await integrationService.getUserGoals();
    userGoals.value = response.data;
  } catch (error) {
    console.error("Failed to load user goals:", error);
  }
}

function formatActivityLevel(level) {
  const levels = {
    sedentary: "Ít vận động",
    lightly_active: "Vận động nhẹ",
    moderately_active: "Vận động vừa",
    very_active: "Vận động nhiều",
    extra_active: "Vận động rất nhiều",
  };
  return levels[level] || level;
}

function formatGoalType(type) {
  const types = {
    maintain: "Duy trì cân nặng",
    lose_weight: "Giảm cân",
    gain_muscle: "Tăng cơ",
    improve_fitness: "Cải thiện sức khỏe",
  };
  return types[type] || type;
}
</script>

<template>
  <div class="space-y-3 sm:space-y-4">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-2"
    >
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Năng lượng</h1>
        <p class="text-xs sm:text-sm text-slate-500">
          Theo dõi cân bằng năng lượng và dinh dưỡng
        </p>
      </div>
      <a-date-picker
        v-model:value="selectedDate"
        class="w-full sm:w-auto"
        value-format="YYYY-MM-DD"
      />
    </div>

    <!-- Tabs -->
    <a-tabs v-model:activeKey="activeTab">
      <!-- Dashboard Tab -->
      <a-tab-pane key="dashboard">
        <template #tab>
          <span><BarChartOutlined /> Tổng quan</span>
        </template>
        <div class="space-y-4">
          <EnergyBalanceDashboard
            v-if="userId"
            :user-id="userId"
            :selected-date="selectedDate"
          />
          <MacroProgressBar :progress="macroProgress" />
        </div>
      </a-tab-pane>

      <!-- Settings Tab -->
      <a-tab-pane key="settings">
        <template #tab>
          <span><SettingOutlined /> Cài đặt</span>
        </template>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- User Stats Card -->
          <div class="bg-white border border-slate-200 rounded-lg p-2">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-slate-800">📊 Thông tin cơ thể</h3>
              <a-button size="small" type="link">Cập nhật</a-button>
            </div>
            <a-spin :spinning="loading">
              <div v-if="userStats" class="space-y-3">
                <div
                  class="flex justify-between items-center p-2 bg-slate-50 rounded"
                >
                  <span class="text-sm text-slate-600">Cân nặng:</span>
                  <span class="font-medium">{{ userStats.weight }} kg</span>
                </div>
                <div
                  class="flex justify-between items-center p-2 bg-slate-50 rounded"
                >
                  <span class="text-sm text-slate-600">Chiều cao:</span>
                  <span class="font-medium">{{ userStats.height }} cm</span>
                </div>
                <div
                  class="flex justify-between items-center p-2 bg-slate-50 rounded"
                >
                  <span class="text-sm text-slate-600">Tuổi:</span>
                  <span class="font-medium">{{ userStats.age }} tuổi</span>
                </div>
                <div
                  class="flex justify-between items-center p-2 bg-slate-50 rounded"
                >
                  <span class="text-sm text-slate-600">Mức vận động:</span>
                  <span class="font-medium text-xs">{{
                    formatActivityLevel(userStats.activity_level)
                  }}</span>
                </div>
              </div>
              <div v-else class="text-center py-8 text-slate-500 text-sm">
                Chưa có thông tin. Vui lòng cập nhật.
              </div>
            </a-spin>
          </div>

          <!-- User Goals Card -->
          <div class="bg-white border border-slate-200 rounded-lg p-2">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-slate-800">🎯 Mục tiêu</h3>
              <a-button size="small" type="link">Cập nhật</a-button>
            </div>
            <div v-if="userGoals" class="space-y-3">
              <div
                class="flex justify-between items-center p-2 bg-slate-50 rounded"
              >
                <span class="text-sm text-slate-600">Loại mục tiêu:</span>
                <span class="font-medium text-xs">{{
                  formatGoalType(userGoals.goal_type)
                }}</span>
              </div>
              <div
                class="flex justify-between items-center p-2 bg-slate-50 rounded"
              >
                <span class="text-sm text-slate-600">Calo mục tiêu:</span>
                <span class="font-medium"
                  >{{ userGoals.target_calories }} kcal</span
                >
              </div>
              <div
                class="flex justify-between items-center p-2 bg-slate-50 rounded"
              >
                <span class="text-sm text-slate-600">Protein:</span>
                <span class="font-medium">{{ userGoals.target_protein }}g</span>
              </div>
              <div
                class="flex justify-between items-center p-2 bg-slate-50 rounded"
              >
                <span class="text-sm text-slate-600">Carbs:</span>
                <span class="font-medium">{{ userGoals.target_carbs }}g</span>
              </div>
              <div
                class="flex justify-between items-center p-2 bg-slate-50 rounded"
              >
                <span class="text-sm text-slate-600">Fat:</span>
                <span class="font-medium">{{ userGoals.target_fat }}g</span>
              </div>
            </div>
            <div v-else class="text-center py-8 text-slate-500 text-sm">
              Chưa có mục tiêu. Vui lòng cài đặt.
            </div>
          </div>
        </div>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>
