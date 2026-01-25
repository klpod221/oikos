<!--
  PeriodSelector.vue

  Allows user to select a time period (Month, Year, Custom).
  Props:
    - period: Current selected period string
  Events:
    - update:period: Emitted when period changes
    - customRange: Emitted when custom date range is selected (start, end)
-->
<script setup>
import { computed } from "vue";

const props = defineProps({
  period: { type: String, required: true },
});

const emit = defineEmits(["update:period", "customRange"]);

const periodOptions = [
  { label: "Tháng này", value: "this_month" },
  { label: "Tháng trước", value: "last_month" },
  { label: "Năm nay", value: "this_year" },
  { label: "Năm trước", value: "last_year" },
  { label: "Tùy chọn", value: "custom" },
];

const showDatePicker = computed(() => props.period === "custom");

const handlePeriodChange = (value) => {
  emit("update:period", value);
};

const handleDateChange = (dates) => {
  if (dates && dates.length === 2) {
    const start = dates[0].format("YYYY-MM-DD");
    const end = dates[1].format("YYYY-MM-DD");
    emit("customRange", start, end);
  }
};
</script>

<template>
  <div class="flex flex-col gap-2">
    <!-- Mobile: Dropdown -->
    <div class="block lg:hidden">
      <a-select
        :value="period"
        :options="periodOptions"
        @change="handlePeriodChange"
        class="w-full"
        size="large"
      />
    </div>

    <!-- Desktop: Segmented -->
    <div class="hidden lg:block">
      <a-segmented
        :value="period"
        :options="periodOptions"
        @change="handlePeriodChange"
      />
    </div>

    <a-range-picker
      v-if="showDatePicker"
      @change="handleDateChange"
      format="YYYY-MM-DD"
      :placeholder="['Ngày bắt đầu', 'Ngày kết thúc']"
      class="w-full lg:w-auto"
    />
  </div>
</template>
