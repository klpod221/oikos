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
    class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 lg:p-5 hover:shadow-md transition-shadow"
  >
    <div class="flex items-start justify-between mb-2 sm:mb-3">
      <div
        class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center text-xl sm:text-2xl"
        :style="{ backgroundColor: wallet.color + '20' }"
      >
        {{ wallet.icon || "💰" }}
      </div>
      <div class="flex items-center gap-2">
        <a-tag v-if="wallet.is_default" color="blue" class="m-0! text-xs"
          >Mặc định</a-tag
        >
        <a-dropdown>
          <a-button type="text" size="small">•••</a-button>
          <template #overlay>
            <a-menu>
              <a-menu-item @click="$emit('edit', wallet)">
                <EditOutlined class="mr-2" /> Sửa
              </a-menu-item>
              <a-menu-item class="text-red-500!">
                <a-popconfirm
                  title="Xóa ví này?"
                  @confirm="$emit('delete', wallet.id)"
                >
                  <DeleteOutlined class="mr-2" /> Xóa
                </a-popconfirm>
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>
      </div>
    </div>
    <h3 class="font-semibold text-slate-800 text-sm sm:text-base">{{ wallet.name }}</h3>
    <p class="text-xs sm:text-sm text-slate-500 mb-2 sm:mb-3">
      {{ wallet.description || "Không có mô tả" }}
    </p>
    <div class="text-xl sm:text-2xl font-bold text-slate-800">
      {{ formatCurrency(wallet.balance, wallet.currency) }}
    </div>
  </div>
</template>
