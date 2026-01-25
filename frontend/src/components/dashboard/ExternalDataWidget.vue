<script setup>
import { ref, onMounted, computed } from "vue";
import { dashboardService } from "../../services/dashboard.service";
import { useSettingsStore } from "../../stores/settings";
import { formatCurrency, formatMetalPrice } from "../../utils/formatters";
import {
  CloudOutlined,
  DollarOutlined,
  GoldOutlined,
} from "@ant-design/icons-vue";

const externalData = ref(null);
const loading = ref(false);
const settingsStore = useSettingsStore();

const userCurrency = computed(() => settingsStore.settings?.currency || "VND");
const goldUnit = computed(() => settingsStore.settings?.gold_unit || "lượng");
const silverUnit = computed(
  () => settingsStore.settings?.silver_unit || "lượng",
);
const userLocale = computed(() =>
  settingsStore.settings?.language === "en" ? "en-US" : "vi-VN",
);

const fetchExternalData = async () => {
  loading.value = true;
  await settingsStore.fetchSettings(); // Ensure settings are loaded
  try {
    const res = await dashboardService.getExternalData();
    externalData.value = res.data.data;
  } catch (e) {
    console.error("Failed to fetch external data", e);
  } finally {
    loading.value = false;
  }
};

const displayCurrency = (amount, currency) => {
  return formatCurrency(amount, currency, userLocale.value);
};

const displayMetalPrice = (metalData, targetUnit) => {
  if (!metalData) return "N/A";
  const price = metalData.price || metalData.price_usd;
  const sourceUnit = metalData.unit || "oz";
  const sourceCurrency = metalData.currency || "USD";

  return formatMetalPrice(
    price,
    targetUnit,
    userCurrency.value,
    externalData.value?.exchange_rates,
    userLocale.value,
    sourceUnit,
    sourceCurrency,
  );
};

onMounted(() => {
  fetchExternalData();
});
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
    <!-- Weather -->
    <div
      class="bg-linear-to-br from-blue-500 to-blue-600 text-white rounded-xl p-3 sm:p-4"
    >
      <div class="flex items-center justify-between mb-2">
        <span class="text-white/80 text-xs sm:text-sm">Thời tiết</span>
        <CloudOutlined class="text-lg sm:text-xl" />
      </div>
      <div v-if="externalData?.weather" class="space-y-1">
        <div class="text-xl sm:text-2xl font-bold">
          {{ externalData.weather.temperature }}°C
        </div>
        <div class="text-xs sm:text-sm text-white/80 flex items-center gap-1">
          <span>{{ externalData.weather.condition?.icon }}</span>
          <span>{{ externalData.weather.condition?.text }}</span>
        </div>
      </div>
      <div v-else class="text-white/60 text-xs sm:text-sm">Đang tải...</div>
    </div>

    <!-- USD/VND Rate -->
    <div
      class="bg-linear-to-br from-green-500 to-green-600 text-white rounded-xl p-3 sm:p-4"
    >
      <div class="flex items-center justify-between mb-2">
        <span class="text-white/80 text-xs sm:text-sm">USD/VND</span>
        <DollarOutlined class="text-lg sm:text-xl" />
      </div>
      <div v-if="externalData?.exchange_rates?.usd_vnd" class="space-y-1">
        <div class="text-base sm:text-xl lg:text-2xl font-bold">
          {{ displayCurrency(externalData.exchange_rates.usd_vnd, "VND") }}
        </div>
        <div class="text-xs sm:text-sm text-white/80">1 USD</div>
      </div>
      <div v-else class="text-white/60 text-xs sm:text-sm">Đang tải...</div>
    </div>

    <!-- Gold Price -->
    <div
      class="bg-linear-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-3 sm:p-4"
    >
      <div class="flex items-center justify-between mb-2">
        <span class="text-white/80 text-xs sm:text-sm">Vàng</span>
        <GoldOutlined class="text-lg sm:text-xl" />
      </div>
      <div v-if="externalData?.metals?.gold" class="space-y-1">
        <div class="text-base sm:text-xl lg:text-2xl font-bold">
          {{ displayMetalPrice(externalData.metals.gold, goldUnit) }}
        </div>
        <div class="text-xs sm:text-sm text-white/80 capitalize">mỗi {{ goldUnit }}</div>
      </div>
      <div v-else class="text-white/60 text-xs sm:text-sm">Đang tải...</div>
    </div>

    <!-- Silver Price -->
    <div
      class="bg-linear-to-br from-slate-400 to-slate-500 text-white rounded-xl p-3 sm:p-4"
    >
      <div class="flex items-center justify-between mb-2">
        <span class="text-white/80 text-xs sm:text-sm">Bạc</span>
        <GoldOutlined class="text-lg sm:text-xl" />
      </div>
      <div v-if="externalData?.metals?.silver" class="space-y-1">
        <div class="text-base sm:text-xl lg:text-2xl font-bold">
          {{ displayMetalPrice(externalData.metals.silver, silverUnit) }}
        </div>
        <div class="text-xs sm:text-sm text-white/80 capitalize">mỗi {{ silverUnit }}</div>
      </div>
      <div v-else class="text-white/60 text-xs sm:text-sm">Đang tải...</div>
    </div>
  </div>
</template>
