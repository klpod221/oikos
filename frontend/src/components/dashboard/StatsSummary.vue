<script setup>
import { computed } from "vue";
import { formatCurrency } from "../../utils/formatters";
import {
  WalletOutlined,
  ArrowUpOutlined,
  ArrowDownOutlined,
  SaveOutlined,
} from "@ant-design/icons-vue";

const props = defineProps({
  summary: { type: Object, required: true },
});

const netSavingsColor = computed(() => {
  return props.summary.net_savings >= 0 ? "text-green-600" : "text-red-600";
});
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
    <!-- Total Income -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 lg:p-6">
      <div class="flex items-center justify-between mb-3 sm:mb-4">
        <span class="text-slate-500 text-xs sm:text-sm">Tổng thu nhập</span>
        <div
          class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-green-100 flex items-center justify-center"
        >
          <ArrowUpOutlined class="text-green-600 text-sm sm:text-base" />
        </div>
      </div>
      <div class="text-base sm:text-lg lg:text-2xl font-bold text-slate-800">
        {{ formatCurrency(summary.total_income, "VND") }}
      </div>
      <div class="text-green-600 text-xs sm:text-sm mt-1 sm:mt-2">
        {{ summary.transaction_count }} giao dịch
      </div>
    </div>

    <!-- Total Expense -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 lg:p-6">
      <div class="flex items-center justify-between mb-3 sm:mb-4">
        <span class="text-slate-500 text-xs sm:text-sm">Tổng chi tiêu</span>
        <div
          class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-red-100 flex items-center justify-center"
        >
          <ArrowDownOutlined class="text-red-600 text-sm sm:text-base" />
        </div>
      </div>
      <div class="text-base sm:text-lg lg:text-2xl font-bold text-slate-800">
        {{ formatCurrency(summary.total_expense, "VND") }}
      </div>
      <div class="text-red-600 text-xs sm:text-sm mt-1 sm:mt-2">Đã chi trong kỳ này</div>
    </div>

    <!-- Net Savings -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 lg:p-6">
      <div class="flex items-center justify-between mb-3 sm:mb-4">
        <span class="text-slate-500 text-xs sm:text-sm">Tiết kiệm ròng</span>
        <div
          class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
          :class="
            summary.net_savings >= 0
              ? 'bg-green-100 text-green-600'
              : 'bg-red-100 text-red-600'
          "
        >
          <SaveOutlined class="text-sm sm:text-base" />
        </div>
      </div>
      <div class="text-base sm:text-lg lg:text-2xl font-bold text-slate-800">
        {{ formatCurrency(summary.net_savings, "VND") }}
      </div>
      <div class="text-xs sm:text-sm mt-1 sm:mt-2" :class="netSavingsColor">
        Số dư {{ summary.net_savings >= 0 ? "dương" : "âm" }}
      </div>
    </div>

    <!-- Transaction Count -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 lg:p-6">
      <div class="flex items-center justify-between mb-3 sm:mb-4">
        <span class="text-slate-500 text-xs sm:text-sm">Giao dịch</span>
        <div
          class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-100 flex items-center justify-center"
        >
          <WalletOutlined class="text-blue-600 text-sm sm:text-base" />
        </div>
      </div>
      <div class="text-base sm:text-lg lg:text-2xl font-bold text-slate-800">
        {{ summary.transaction_count }}
      </div>
      <div class="text-blue-600 text-xs sm:text-sm mt-1 sm:mt-2">Tổng số lượng</div>
    </div>
  </div>
</template>
