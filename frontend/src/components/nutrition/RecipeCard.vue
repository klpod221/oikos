<script setup>
import {
  EditOutlined,
  DeleteOutlined,
  CoffeeOutlined,
} from "@ant-design/icons-vue";

defineProps({
  recipe: { type: Object, required: true },
});

defineEmits(["edit", "delete"]);
</script>

<template>
  <div
    class="bg-white border border-slate-200 rounded-xl p-5 hover:shadow-md transition-shadow"
  >
    <div class="flex items-start justify-between mb-3">
      <div
        class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center"
      >
        <CoffeeOutlined class="text-green-600 text-xl" />
      </div>
      <a-dropdown>
        <a-button type="text" size="small">•••</a-button>
        <template #overlay>
          <a-menu>
            <a-menu-item @click="$emit('edit', recipe)">
              <EditOutlined class="mr-2" /> Edit
            </a-menu-item>
            <a-menu-item class="text-red-500!">
              <a-popconfirm
                title="Delete this recipe?"
                @confirm="$emit('delete', recipe.id)"
              >
                <DeleteOutlined class="mr-2" /> Delete
              </a-popconfirm>
            </a-menu-item>
          </a-menu>
        </template>
      </a-dropdown>
    </div>
    <h3 class="font-semibold text-slate-800">{{ recipe.name }}</h3>
    <p class="text-sm text-slate-500 mb-3 line-clamp-2">
      {{ recipe.description || "No description" }}
    </p>
    <div class="flex gap-4 text-sm text-slate-500">
      <span>{{ recipe.servings }} servings</span>
      <span
        >{{ (recipe.prep_time || 0) + (recipe.cooking_time || 0) }} mins</span
      >
    </div>
    <div v-if="recipe.calories" class="mt-2 text-xs text-slate-400">
      {{ recipe.calories }} kcal | P: {{ recipe.protein }}g | C:
      {{ recipe.carbs }}g | F: {{ recipe.fat }}g
    </div>
  </div>
</template>
