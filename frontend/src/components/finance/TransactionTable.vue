<!--
  TransactionTable.vue

  Displays a table of transactions with pagination and actions.
  Props:
    - transactions: Array of transaction objects
    - loading: Loading state
    - pagination: Pagination config object
  Events:
    - change: Emitted on table change (pagination, filters, sorter)
    - edit: Emitted when edit clicked
    - delete: Emitted when delete confirmed
-->
<script setup>
import {
  DeleteOutlined,
  EditOutlined,
  ArrowUpOutlined,
  ArrowDownOutlined,
} from "@ant-design/icons-vue";
import { formatCurrency, formatDate } from "../../utils/formatters";

defineProps({
  transactions: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  pagination: { type: Object, default: () => ({}) },
});

defineEmits(["delete", "edit", "change"]);
</script>

<template>
  <a-table
    :data-source="transactions"
    :loading="loading"
    :pagination="pagination"
    @change="(pag, filters, sorter) => $emit('change', pag, filters, sorter)"
    row-key="id"
    :scroll="{ x: 'max-content' }"
    size="small"
  >
    <a-table-column
      title="Ngày"
      data-index="transaction_date"
      key="date"
      width="100"
      :sorter="true"
    >
      <template #default="{ record }">
        <span class="text-xs sm:text-sm">{{ formatDate(record.transaction_date) }}</span>
      </template>
    </a-table-column>
    <a-table-column title="Mô tả" data-index="description" key="description">
      <template #default="{ record }">
        <div class="flex items-center gap-2 sm:gap-3">
          <div
            class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs sm:text-sm"
            :class="
              record.type === 'income'
                ? 'bg-green-100 text-green-600'
                : 'bg-red-100 text-red-600'
            "
          >
            <ArrowUpOutlined v-if="record.type === 'income'" />
            <ArrowDownOutlined v-else />
          </div>
          <div>
            <div class="font-medium text-xs sm:text-sm">
              {{ record.description || "Không có mô tả" }}
            </div>
            <div class="text-xs text-slate-400">
              {{ record.category?.name || "Chưa phân loại" }}
            </div>
          </div>
        </div>
      </template>
    </a-table-column>
    <a-table-column 
      title="Ví" 
      key="wallet" 
      width="120"
      :responsive="['md']"
    >
      <template #default="{ record }">
        <span class="text-xs sm:text-sm">{{ record.wallet?.name || "-" }}</span>
      </template>
    </a-table-column>
    <a-table-column
      title="Số tiền"
      data-index="amount"
      key="amount"
      width="110"
      align="right"
      :sorter="true"
    >
      <template #default="{ record }">
        <span
          class="font-semibold text-xs sm:text-sm"
          :class="record.type === 'income' ? 'text-green-600' : 'text-red-600'"
        >
          {{ record.type === "income" ? "+" : "-"
          }}{{
            formatCurrency(record.amount, record.wallet?.currency || "VND")
          }}
        </span>
      </template>
    </a-table-column>
    <a-table-column title="" key="actions" width="60" fixed="right">
      <template #default="{ record }">
        <div class="flex gap-1 justify-end">
          <a-button type="text" size="small" @click="$emit('edit', record)">
            <EditOutlined class="text-xs sm:text-sm" />
          </a-button>
          <a-popconfirm
            title="Xóa giao dịch này?"
            @confirm="$emit('delete', record.id)"
          >
            <a-button type="text" danger size="small">
              <DeleteOutlined class="text-xs sm:text-sm" />
            </a-button>
          </a-popconfirm>
        </div>
      </template>
    </a-table-column>
  </a-table>
</template>
