<!--
  ShoppingListCard.vue

  Displays a shopping list with items.
  Props:
    - list: Shopping list object
  Events:
    - toggleItem: Emitted when item checked/unchecked
-->
<script setup>
import { computed } from "vue";
import {
  ShoppingCartOutlined,
  CheckCircleOutlined,
} from "@ant-design/icons-vue";
import { useNutritionStore } from "../../stores/nutrition";

const props = defineProps({
  list: { type: Object, required: true },
});

const nutrition = useNutritionStore();

const completedCount = computed(() => {
  return props.list.items?.filter((i) => i.is_purchased).length || 0;
});

const totalCount = computed(() => {
  return props.list.items?.length || 0;
});

const progress = computed(() => {
  if (totalCount.value === 0) return 0;
  return Math.round((completedCount.value / totalCount.value) * 100);
});

async function toggleItem(item) {
  await nutrition.updateShoppingListItem(props.list.id, item.id, {
    is_purchased: !item.is_purchased,
  });
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString("vi-VN", {
    day: "numeric",
    month: "short",
  });
}
</script>

<template>
  <div
    class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition-shadow"
  >
    <div class="flex items-start justify-between mb-3">
      <div class="flex items-center gap-2">
        <div
          class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"
        >
          <ShoppingCartOutlined class="text-emerald-600 text-lg" />
        </div>
        <div>
          <h3 class="font-semibold text-slate-800">
            {{ list.name || "Danh sách mua sắm" }}
          </h3>
          <p class="text-xs text-slate-500">
            {{ formatDate(list.created_at) }}
          </p>
        </div>
      </div>
      <a-tag :color="progress === 100 ? 'success' : 'processing'">
        {{ completedCount }}/{{ totalCount }}
      </a-tag>
    </div>

    <a-progress
      :percent="progress"
      :show-info="false"
      size="small"
      stroke-color="#10b981"
      class="mb-3"
    />

    <div class="space-y-2 max-h-48 overflow-y-auto">
      <div
        v-for="item in list.items"
        :key="item.id"
        class="flex items-center gap-2 p-2 rounded hover:bg-slate-50 cursor-pointer"
        @click="toggleItem(item)"
      >
        <a-checkbox :checked="item.is_purchased" />
        <span
          class="flex-1 text-sm"
          :class="
            item.is_purchased ? 'line-through text-slate-400' : 'text-slate-700'
          "
        >
          {{ item.ingredient_name }}
        </span>
        <span class="text-xs text-slate-500">
          {{ item.quantity }} {{ item.unit }}
        </span>
      </div>
      <div
        v-if="!list.items?.length"
        class="text-center py-4 text-slate-400 text-sm"
      >
        Không có mục nào
      </div>
    </div>
  </div>
</template>
