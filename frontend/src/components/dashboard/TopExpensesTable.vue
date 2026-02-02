<!--
  TopExpensesTable.vue

  Displays a table of the top expense categories.
  Props:
    - topExpenses: Array of expense objects { category_name, icon, amount, transactions }
-->
<script setup>
import { formatCurrency } from "../../utils/formatters";

defineProps({
  topExpenses: { type: Array, required: true },
});
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-2 lg:p-6">
    <h3
      class="text-sm sm:text-base lg:text-lg font-semibold text-slate-800 mb-2 sm:mb-3 lg:mb-4"
    >
      Chi tiêu hàng đầu
    </h3>
    <a-table
      :data-source="topExpenses"
      :pagination="false"
      :show-header="true"
      :scroll="{ x: 'max-content' }"
      size="small"
    >
      <a-table-column title="Danh mục" key="category">
        <template #default="{ record }">
          <div class="flex items-center gap-2">
            <span class="text-lg sm:text-xl">{{ record.icon }}</span>
            <span class="font-medium text-sm sm:text-base">{{
              record.category_name
            }}</span>
          </div>
        </template>
      </a-table-column>
      <a-table-column title="Số tiền" key="amount" align="right">
        <template #default="{ record }">
          <span class="font-semibold text-red-600 text-sm sm:text-base">
            {{ formatCurrency(record.amount, "VND") }}
          </span>
        </template>
      </a-table-column>
      <a-table-column title="Số lần" key="transactions" align="center">
        <template #default="{ record }">
          <a-tag color="blue" class="text-xs sm:text-sm">{{
            record.transactions
          }}</a-tag>
        </template>
      </a-table-column>
    </a-table>
  </div>
</template>
