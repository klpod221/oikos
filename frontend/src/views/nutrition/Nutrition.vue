<!--
  Nutrition.vue

  Main nutrition module view.
  Tabs:
  - Ingredients: Manage personal ingredients? (Actually uses global for now)
  - Recipes: Manage recipes
  - Meal Plans: Placeholder
-->
<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useNutritionStore } from "../../stores/nutrition";
import {
  PlusOutlined,
  ExperimentOutlined,
  CoffeeOutlined,
  CalendarOutlined,
  SearchOutlined,
  ShoppingCartOutlined,
  HistoryOutlined,
} from "@ant-design/icons-vue";

// Components
import IngredientTable from "../../components/nutrition/IngredientTable.vue";
import IngredientModal from "../../components/nutrition/IngredientModal.vue";
import RecipeCard from "../../components/nutrition/RecipeCard.vue";
import RecipeModal from "../../components/nutrition/RecipeModal.vue";
import RecipeDetailModal from "../../components/nutrition/RecipeDetailModal.vue";
import MealPlanCalendar from "../../components/nutrition/MealPlanCalendar.vue";
import ShoppingListCard from "../../components/nutrition/ShoppingListCard.vue";
import NutritionLogModal from "../../components/nutrition/NutritionLogModal.vue";

import { debounce } from "../../utils/debounce";

const nutrition = useNutritionStore();
const activeTab = ref("ingredients");

// Modal states
const ingredientModalOpen = ref(false);
const editingIngredient = ref(null);
const recipeModalOpen = ref(false);
const recipeDetailModalOpen = ref(false);
const editingRecipe = ref(null);
const viewingRecipe = ref(null);
const nutritionLogModalOpen = ref(false);
const nutritionLogForm = ref({
  log_type: "recipe",
  recipe_id: null,
  ingredient_id: null,
  quantity: 1,
  meal_type: "lunch",
  logged_at: new Date().toISOString().slice(0, 16),
});

// Search filters
const ingredientSearch = ref("");
const recipeSearch = ref("");

// Forms
const ingredientForm = ref({
  name: "",
  unit: "g",
  calories: 0,
  protein: 0,
  carbs: 0,
  fat: 0,
  fiber: 0,
  sugar: 0,
});
const recipeForm = ref({
  name: "",
  description: "",
  servings: 1,
  prep_time: 0,
  cooking_time: 0,
  instructions: "",
  calories: 0,
  protein: 0,
  carbs: 0,
  fat: 0,
});

onMounted(async () => {
  await Promise.all([
    nutrition.fetchIngredients(),
    nutrition.fetchRecipes(),
    nutrition.fetchShoppingLists(),
    nutrition.fetchNutritionLogs(),
  ]);
});

// Watch ingredient search
const debouncedIngredientSearch = debounce((query) => {
  nutrition.ingredientFilters.search = query;
  nutrition.fetchIngredients(1);
}, 500);

watch(ingredientSearch, () => {
  debouncedIngredientSearch(ingredientSearch.value);
});

// Watch recipe search
const debouncedRecipeSearch = debounce((query) => {
  nutrition.recipeFilters.search = query;
  nutrition.fetchRecipes(1);
}, 500);

watch(recipeSearch, () => {
  debouncedRecipeSearch(recipeSearch.value);
});

// Ingredient handlers
// Ingredient handlers
const openIngredientModal = (ingredient = null) => {
  // Check if ingredient is a click event or synthetic event
  const isEvent =
    ingredient && (ingredient instanceof Event || ingredient.type === "click");
  const targetIngredient = isEvent ? null : ingredient;

  editingIngredient.value = targetIngredient;
  ingredientForm.value = targetIngredient
    ? { ...targetIngredient }
    : {
        name: "",
        unit: "g",
        calories: 0,
        protein: 0,
        carbs: 0,
        fat: 0,
        fiber: 0,
        sugar: 0,
      };
  ingredientModalOpen.value = true;
};

const handleIngredientSubmit = async (data) => {
  const success = editingIngredient.value
    ? await nutrition.updateIngredient(editingIngredient.value.id, data)
    : await nutrition.createIngredient(data);
  if (success) ingredientModalOpen.value = false;
};

const handleIngredientDelete = async (id) => {
  await nutrition.deleteIngredient(id);
};

// Recipe handlers
const openRecipeModal = (recipe = null) => {
  // Check if recipe is a click event
  const isEvent =
    recipe && (recipe instanceof Event || recipe.type === "click");
  const targetRecipe = isEvent ? null : recipe;

  editingRecipe.value = targetRecipe;
  recipeForm.value = targetRecipe
    ? { ...targetRecipe }
    : {
        name: "",
        description: "",
        servings: 1,
        prep_time: 0,
        cooking_time: 0,
        instructions: "",
        calories: 0,
        protein: 0,
        carbs: 0,
        fat: 0,
      };
  recipeModalOpen.value = true;
};

const openRecipeDetail = (recipe) => {
  viewingRecipe.value = recipe;
  recipeDetailModalOpen.value = true;
};

const handleRecipeSubmit = async (data) => {
  const success = editingRecipe.value
    ? await nutrition.updateRecipe(editingRecipe.value.id, data)
    : await nutrition.createRecipe(data);
  if (success) recipeModalOpen.value = false;
};

const handleRecipeDelete = async (id) => {
  await nutrition.deleteRecipe(id);
};

const ingredientPaginationConfig = computed(() => ({
  current: nutrition.ingredientPagination.currentPage,
  pageSize: nutrition.ingredientPagination.perPage,
  total: nutrition.ingredientPagination.total,
  showSizeChanger: true,
  showTotal: (total) => `Tổng ${total} nguyên liệu`,
  position: ["bottomCenter"],
}));

const handleRecipePageChange = (page) => {
  nutrition.fetchRecipes(page);
};

const handleIngredientTableChange = (pagination, filters, sorter) => {
  if (sorter.field && sorter.order) {
    nutrition.ingredientFilters.sort_by = sorter.field;
    nutrition.ingredientFilters.sort_order =
      sorter.order === "ascend" ? "asc" : "desc";
  } else {
    nutrition.ingredientFilters.sort_by = "";
    nutrition.ingredientFilters.sort_order = "desc";
  }
  nutrition.fetchIngredients(pagination.current || 1);
};

// Nutrition Log handlers
const openNutritionLogModal = () => {
  nutritionLogForm.value = {
    log_type: "recipe",
    recipe_id: null,
    ingredient_id: null,
    quantity: 1,
    meal_type: "lunch",
    logged_at: new Date().toISOString().slice(0, 16),
  };
  nutritionLogModalOpen.value = true;
};

const handleNutritionLogSubmit = async (data) => {
  const success = await nutrition.logNutrition(data);
  if (success) {
    nutritionLogModalOpen.value = false;
    await nutrition.fetchNutritionLogs();
  }
};
</script>

<template>
  <div class="space-y-2">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-2"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Dinh dưỡng</h1>
        <p class="text-slate-500">
          Quản lý nguyên liệu, công thức và kế hoạch ăn uống
        </p>
      </div>
    </div>

    <!-- Tabs -->
    <a-tabs v-model:activeKey="activeTab">
      <!-- Ingredients Tab -->
      <a-tab-pane key="ingredients">
        <template #tab
          ><span><ExperimentOutlined /> Nguyên liệu</span></template
        >
        <div class="mb-4 flex flex-col sm:flex-row gap-2 justify-between">
          <a-input
            v-model:value="ingredientSearch"
            placeholder="Tìm kiếm nguyên liệu..."
            allow-clear
            class="w-full sm:w-64"
          >
            <template #prefix>
              <SearchOutlined class="text-slate-400" />
            </template>
          </a-input>
          <a-button type="primary" @click="openIngredientModal">
            <template #icon><PlusOutlined /></template>
            Thêm nguyên liệu
          </a-button>
        </div>
        <IngredientTable
          :ingredients="nutrition.ingredients"
          :loading="nutrition.loading"
          :pagination="ingredientPaginationConfig"
          @change="handleIngredientTableChange"
          @edit="openIngredientModal"
          @delete="handleIngredientDelete"
        />
        <div
          v-if="nutrition.ingredients.length === 0 && !nutrition.loading"
          class="text-center py-12 text-slate-500"
        >
          <ExperimentOutlined class="text-4xl mb-4 opacity-50" />
          <p>Chưa có nguyên liệu. Hãy thêm nguyên liệu đầu tiên!</p>
        </div>
      </a-tab-pane>

      <!-- Recipes Tab -->
      <a-tab-pane key="recipes">
        <template #tab
          ><span><CoffeeOutlined /> Công thức</span></template
        >
        <div class="mb-4 flex flex-col sm:flex-row gap-2 justify-between">
          <a-input
            v-model:value="recipeSearch"
            placeholder="Tìm kiếm công thức..."
            allow-clear
            class="w-full sm:w-64"
          >
            <template #prefix>
              <SearchOutlined class="text-slate-400" />
            </template>
          </a-input>
          <a-button type="primary" @click="openRecipeModal()">
            <template #icon><PlusOutlined /></template>
            Công thức mới
          </a-button>
        </div>
        <div
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3"
        >
          <RecipeCard
            v-for="recipe in nutrition.recipes"
            :key="recipe.id"
            :recipe="recipe"
            @edit="openRecipeModal"
            @delete="handleRecipeDelete"
            @view="openRecipeDetail"
          />
          <div
            v-if="nutrition.recipes.length === 0 && !nutrition.loading"
            class="col-span-full text-center py-12 text-slate-500"
          >
            <CoffeeOutlined class="text-4xl mb-4 opacity-50" />
            <p class="text-sm">
              Chưa có công thức. Hãy tạo công thức đầu tiên!
            </p>
          </div>
        </div>
        <div
          v-if="nutrition.recipes.length > 0"
          class="mt-4 flex justify-center"
        >
          <a-pagination
            v-model:current="nutrition.recipePagination.currentPage"
            :total="nutrition.recipePagination.total"
            :page-size="nutrition.recipePagination.perPage"
            :show-total="(total) => `Tổng ${total} công thức`"
            @change="handleRecipePageChange"
          />
        </div>
      </a-tab-pane>

      <!-- Meal Plans Tab -->
      <a-tab-pane key="mealplans">
        <template #tab
          ><span><CalendarOutlined /> Kế hoạch ăn uống</span></template
        >
        <MealPlanCalendar />
      </a-tab-pane>

      <!-- Shopping Lists Tab -->
      <a-tab-pane key="shopping">
        <template #tab
          ><span><ShoppingCartOutlined /> Danh sách mua sắm</span></template
        >
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <ShoppingListCard
            v-for="list in nutrition.shoppingLists"
            :key="list.id"
            :list="list"
          />
          <div
            v-if="nutrition.shoppingLists.length === 0"
            class="col-span-full text-center py-12 text-slate-500"
          >
            <ShoppingCartOutlined class="text-4xl mb-4 opacity-50" />
            <p class="text-sm">Chưa có danh sách mua sắm</p>
          </div>
        </div>
      </a-tab-pane>

      <!-- Nutrition Logs Tab -->
      <a-tab-pane key="logs">
        <template #tab
          ><span><HistoryOutlined /> Nhật ký dinh dưỡng</span></template
        >
        <div class="mb-4 flex justify-end">
          <a-button type="primary" @click="openNutritionLogModal()">
            <template #icon><PlusOutlined /></template>
            Ghi nhận
          </a-button>
        </div>
        <div class="space-y-3">
          <div
            v-for="log in nutrition.nutritionLogs"
            :key="log.id"
            class="bg-white border border-slate-200 rounded-xl p-4"
          >
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-medium text-slate-800">
                  {{ log.recipe?.name || log.ingredient?.name || "Unknown" }}
                </h4>
                <p class="text-sm text-slate-500">
                  {{ log.meal_type }} • {{ log.quantity }} phần
                </p>
              </div>
              <span class="text-xs text-slate-400">{{
                new Date(log.logged_at).toLocaleDateString("vi-VN")
              }}</span>
            </div>
          </div>
          <div
            v-if="nutrition.nutritionLogs.length === 0"
            class="text-center py-12 text-slate-500"
          >
            <HistoryOutlined class="text-4xl mb-4 opacity-50" />
            <p class="text-sm">Chưa có nhật ký</p>
          </div>
        </div>
      </a-tab-pane>
    </a-tabs>

    <!-- Modals -->
    <IngredientModal
      v-model:open="ingredientModalOpen"
      v-model:form="ingredientForm"
      :ingredient="editingIngredient"
      :loading="nutrition.loading"
      @submit="handleIngredientSubmit"
    />
    <RecipeModal
      v-model:open="recipeModalOpen"
      v-model:form="recipeForm"
      :recipe="editingRecipe"
      :loading="nutrition.loading"
      :ingredients="nutrition.ingredients"
      @submit="handleRecipeSubmit"
    />
    <RecipeDetailModal
      v-model:open="recipeDetailModalOpen"
      :recipe="viewingRecipe"
    />
    <NutritionLogModal
      v-model:open="nutritionLogModalOpen"
      v-model:form="nutritionLogForm"
      :loading="nutrition.loading"
      @submit="handleNutritionLogSubmit"
    />
  </div>
</template>
