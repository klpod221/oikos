<!--
  IncomeExpenseChart.vue

  Displays a bar chart comparing income vs expenses over time.
  Props:
    - dailyTrend: Array of daily data objects { date, income, expense }
-->
<script setup>
import { computed } from "vue";
import { Bar } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from "chart.js";
import { formatCurrency } from "../../utils/formatters";

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
);

const props = defineProps({
  dailyTrend: { type: Array, required: true },
});

const chartData = computed(() => ({
  labels: props.dailyTrend.map((d) => d.date),
  datasets: [
    {
      label: "Thu nhập",
      data: props.dailyTrend.map((d) => d.income),
      backgroundColor: "rgba(34, 197, 94, 0.6)",
      borderColor: "rgb(34, 197, 94)",
      borderWidth: 2,
    },
    {
      label: "Chi tiêu",
      data: props.dailyTrend.map((d) => d.expense),
      backgroundColor: "rgba(239, 68, 68, 0.6)",
      borderColor: "rgb(239, 68, 68)",
      borderWidth: 2,
    },
  ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "top",
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          return (
            context.dataset.label +
            ": " +
            formatCurrency(context.parsed.y, "VND")
          );
        },
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: function (value) {
          return formatCurrency(value, "VND");
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
      Xu hướng hàng ngày
    </h3>
    <div class="h-48 sm:h-60 lg:h-80">
      <Bar :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>
