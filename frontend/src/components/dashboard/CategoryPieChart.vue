<!--
  CategoryPieChart.vue

  Displays a doughnut chart showing expense distribution by category.
  Props:
    - byCategory: Array of category data objects { category_name, total, percentage, type }
-->
<script setup>
import { computed } from "vue";
import { Doughnut } from "vue-chartjs";
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from "chart.js";
import { formatCurrency } from "../../utils/formatters";

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
  byCategory: { type: Array, required: true },
});

const expenseCategories = computed(() =>
  props.byCategory.filter((c) => c.type === "expense"),
);

const chartData = computed(() => ({
  labels: expenseCategories.value.map((c) => c.category_name),
  datasets: [
    {
      data: expenseCategories.value.map((c) => c.total),
      backgroundColor: [
        "#3b82f6",
        "#ef4444",
        "#f59e0b",
        "#10b981",
        "#8b5cf6",
        "#ec4899",
        "#06b6d4",
      ],
      borderWidth: 2,
      borderColor: "#fff",
    },
  ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "bottom",
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          const category = expenseCategories.value[context.dataIndex];
          return (
            context.label +
            ": " +
            formatCurrency(context.parsed, "VND") +
            ` (${category.percentage}%)`
          );
        },
      },
    },
  },
};
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-2 lg:p-6">
    <h3
      class="text-sm sm:text-base lg:text-lg font-semibold text-slate-800 mb-2 sm:mb-3 lg:mb-4"
    >
      Phân bổ chi tiêu
    </h3>
    <div v-if="expenseCategories.length > 0" class="h-48 sm:h-60 lg:h-80">
      <Doughnut :data="chartData" :options="chartOptions" />
    </div>
    <div
      v-else
      class="h-48 sm:h-60 lg:h-80 flex items-center justify-center text-slate-400 text-sm"
    >
      Không có dữ liệu chi tiêu
    </div>
  </div>
</template>
