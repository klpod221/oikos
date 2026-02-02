<!--
  RecipeCard.vue

  Displays a recipe summary card with actions.
  Props:
    - recipe: Recipe object
  Events:
    - edit: Emitted when edit selected
    - delete: Emitted when delete confirmed
-->
<script setup>
import {
  EditOutlined,
  DeleteOutlined,
  CoffeeOutlined,
} from "@ant-design/icons-vue";

defineProps({
  recipe: { type: Object, required: true },
});

defineEmits(["edit", "delete", "view"]);
</script>

<template>
  <div
    class="bg-white border border-slate-200 rounded-xl p-3 sm:p-2 lg:p-5 hover:shadow-md transition-shadow cursor-pointer group"
    @click="$emit('view', recipe)"
  >
    <div class="flex items-start justify-between mb-2 sm:mb-3">
      <div
        class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-green-100 flex items-center justify-center"
      >
        <CoffeeOutlined class="text-green-600 text-lg sm:text-xl" />
      </div>
      <a-dropdown>
        <a-button
          type="text"
          size="small"
          @click.stop
          class="opacity-0 group-hover:opacity-100 transition-opacity"
          >•••</a-button
        >
        <template #overlay>
          <a-menu>
            <a-menu-item @click="$emit('edit', recipe)">
              <EditOutlined class="mr-2" /> Sửa
            </a-menu-item>
            <a-menu-item class="text-red-500!">
              <a-popconfirm
                title="Xóa công thức này?"
                @confirm="$emit('delete', recipe.id)"
              >
                <DeleteOutlined class="mr-2" /> Xóa
              </a-popconfirm>
            </a-menu-item>
          </a-menu>
        </template>
      </a-dropdown>
    </div>
    <h3 class="font-semibold text-slate-800 text-sm sm:text-base">
      {{ recipe.name }}
    </h3>
    <p class="text-xs sm:text-sm text-slate-500 mb-2 sm:mb-3 line-clamp-2">
      {{ recipe.description || "Không có mô tả" }}
    </p>
    <div class="flex gap-2 text-xs sm:text-sm text-slate-500">
      <span>{{ recipe.servings }} khẩu phần</span>
      <span
        >{{ (recipe.prep_time || 0) + (recipe.cooking_time || 0) }} phút</span
      >
      <span>• {{ recipe.ingredients?.length || 0 }} nguyên liệu</span>
    </div>
    <div v-if="recipe.calories" class="mt-2 text-xs text-slate-400">
      {{ recipe.calories }} kcal | P: {{ recipe.protein }}g | C:
      {{ recipe.carbs }}g | F: {{ recipe.fat }}g
    </div>
  </div>
</template>
