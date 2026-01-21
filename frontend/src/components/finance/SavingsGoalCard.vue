<script setup>
defineProps({
  goal: { type: Object, required: true },
});

const getProgress = (goal) => {
  if (!goal.target_amount || goal.target_amount <= 0) return 0;
  return Math.min(100, (goal.current_amount / goal.target_amount) * 100);
};

import { formatCurrency, formatDate } from "../../utils/formatters";
import { EditOutlined, DeleteOutlined } from "@ant-design/icons-vue";

defineEmits(["edit", "delete"]);
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-xl p-5">
    <div class="flex items-start justify-between mb-3">
      <div
        class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
        :style="{ backgroundColor: (goal.color || '#10b981') + '20' }"
      >
        {{ goal.icon || "🎯" }}
      </div>
      <div class="flex items-center gap-2">
        <a-tag
          :color="
            goal.status === 'completed'
              ? 'green'
              : goal.status === 'cancelled'
                ? 'red'
                : 'blue'
          "
        >
          {{ goal.status }}
        </a-tag>
        <a-dropdown>
          <a-button type="text" size="small">•••</a-button>
          <template #overlay>
            <a-menu>
              <a-menu-item @click="$emit('edit', goal)">
                <EditOutlined class="mr-2" /> Edit
              </a-menu-item>
              <a-menu-item class="text-red-500!">
                <a-popconfirm
                  title="Delete this goal?"
                  @confirm="$emit('delete', goal.id)"
                >
                  <DeleteOutlined class="mr-2" /> Delete
                </a-popconfirm>
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>
      </div>
    </div>
    <h3 class="font-semibold text-slate-800">{{ goal.name }}</h3>
    <p class="text-sm text-slate-500 mb-3">
      {{ goal.description || "No description" }}
    </p>

    <div class="mb-2">
      <div class="flex justify-between text-sm mb-1">
        <span>{{ formatCurrency(goal.current_amount, goal.currency) }}</span>
        <span class="text-slate-500">{{
          formatCurrency(goal.target_amount, goal.currency)
        }}</span>
      </div>
      <a-progress
        :percent="getProgress(goal)"
        :show-info="false"
        :stroke-color="goal.color || '#10b981'"
      />
    </div>

    <div v-if="goal.deadline" class="text-xs text-slate-400">
      Deadline: {{ formatDate(goal.deadline) }}
    </div>
  </div>
</template>
