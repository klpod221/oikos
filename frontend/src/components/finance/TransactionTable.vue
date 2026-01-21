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
});

defineEmits(["delete"]);
</script>

<template>
  <a-table
    :data-source="transactions"
    :loading="loading"
    :pagination="{ pageSize: 10 }"
    row-key="id"
  >
    <a-table-column
      title="Date"
      data-index="transaction_date"
      key="date"
      width="120"
    >
      <template #default="{ record }">
        {{ formatDate(record.transaction_date) }}
      </template>
    </a-table-column>
    <a-table-column
      title="Description"
      data-index="description"
      key="description"
    >
      <template #default="{ record }">
        <div class="flex items-center gap-3">
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center text-sm"
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
            <div class="font-medium">
              {{ record.description || "No description" }}
            </div>
            <div class="text-xs text-slate-400">
              {{ record.category?.name || "Uncategorized" }}
            </div>
          </div>
        </div>
      </template>
    </a-table-column>
    <a-table-column title="Wallet" key="wallet" width="150">
      <template #default="{ record }">
        {{ record.wallet?.name || "-" }}
      </template>
    </a-table-column>
    <a-table-column title="Amount" key="amount" width="120" align="right">
      <template #default="{ record }">
        <span
          class="font-semibold"
          :class="record.type === 'income' ? 'text-green-600' : 'text-red-600'"
        >
          {{ record.type === "income" ? "+" : "-"
          }}{{
            formatCurrency(record.amount, record.wallet?.currency || "VND")
          }}
        </span>
      </template>
    </a-table-column>
    <a-table-column title="" key="actions" width="60">
      <template #default="{ record }">
        <div class="flex gap-1 justify-end">
          <a-button type="text" size="small" @click="$emit('edit', record)">
            <EditOutlined />
          </a-button>
          <a-popconfirm
            title="Delete this transaction?"
            @confirm="$emit('delete', record.id)"
          >
            <a-button type="text" danger size="small">
              <DeleteOutlined />
            </a-button>
          </a-popconfirm>
        </div>
      </template>
    </a-table-column>
  </a-table>
</template>
