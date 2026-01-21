<script setup>
import { EditOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import { formatCurrency } from "../../utils/formatters";

defineProps({
  wallet: { type: Object, required: true },
});

defineEmits(["edit", "delete"]);
</script>

<template>
  <div
    class="bg-white border border-slate-200 rounded-xl p-5 hover:shadow-md transition-shadow"
  >
    <div class="flex items-start justify-between mb-3">
      <div
        class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
        :style="{ backgroundColor: wallet.color + '20' }"
      >
        {{ wallet.icon || "💰" }}
      </div>
      <div class="flex items-center gap-2">
        <a-tag v-if="wallet.is_default" color="blue" class="m-0!"
          >Default</a-tag
        >
        <a-dropdown>
          <a-button type="text" size="small">•••</a-button>
          <template #overlay>
            <a-menu>
              <a-menu-item @click="$emit('edit', wallet)">
                <EditOutlined class="mr-2" /> Edit
              </a-menu-item>
              <a-menu-item class="text-red-500!">
                <a-popconfirm
                  title="Delete this wallet?"
                  @confirm="$emit('delete', wallet.id)"
                >
                  <DeleteOutlined class="mr-2" /> Delete
                </a-popconfirm>
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>
      </div>
    </div>
    <h3 class="font-semibold text-slate-800">{{ wallet.name }}</h3>
    <p class="text-sm text-slate-500 mb-3">
      {{ wallet.description || "No description" }}
    </p>
    <div class="text-2xl font-bold text-slate-800">
      {{ formatCurrency(wallet.balance, wallet.currency) }}
    </div>
  </div>
</template>
