<script setup>
import { ref, onMounted, watch } from "vue";
import { useNutritionStore } from "../../stores/nutrition";
import {
  PlusOutlined,
  ExperimentOutlined,
  CoffeeOutlined,
  CalendarOutlined,
  SearchOutlined,
} from "@ant-design/icons-vue";

// Components
import IngredientTable from "../../components/nutrition/IngredientTable.vue";
import IngredientModal from "../../components/nutrition/IngredientModal.vue";
import RecipeCard from "../../components/nutrition/RecipeCard.vue";
import RecipeModal from "../../components/nutrition/RecipeModal.vue";

const nutrition = useNutritionStore();
const activeTab = ref("ingredients");

// Modal states
const ingredientModalOpen = ref(false);
const recipeModalOpen = ref(false);
const editingRecipe = ref(null);

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
  await Promise.all([nutrition.fetchIngredients(), nutrition.fetchRecipes()]);
});

// Watch ingredient search
watch(ingredientSearch, () => {
  nutrition.ingredientFilters.search = ingredientSearch.value;
  nutrition.fetchIngredients(1);
});

// Watch recipe search
watch(recipeSearch, () => {
  nutrition.recipeFilters.search = recipeSearch.value;
  nutrition.fetchRecipes(1);
});

// Ingredient handlers
const openIngredientModal = () => {
  ingredientForm.value = {
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
  const success = await nutrition.createIngredient(data);
  if (success) ingredientModalOpen.value = false;
};

// Recipe handlers
const openRecipeModal = (recipe = null) => {
  editingRecipe.value = recipe;
  recipeForm.value = recipe
    ? { ...recipe }
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

const handleRecipeSubmit = async (data) => {
  const success = await nutrition.createRecipe(data);
  if (success) recipeModalOpen.value = false;
};

const handleRecipeDelete = async (id) => {
  await nutrition.deleteRecipe(id);
};

const handleIngredientPageChange = (page) => {
  nutrition.fetchIngredients(page);
};

const handleRecipePageChange = (page) => {
  nutrition.fetchRecipes(page);
};

const handleIngredientTableChange = (pagination, filters, sorter) => {
  if (sorter.field && sorter.order) {
    nutrition.ingredientFilters.sort_by = sorter.field;
    nutrition.ingredientFilters.sort_order = sorter.order === "ascend" ? "asc" : "desc";
  } else {
    nutrition.ingredientFilters.sort_by = "";
    nutrition.ingredientFilters.sort_order = "desc";
  }
  nutrition.fetchIngredients(pagination.current || 1);
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
          @change="handleIngredientTableChange"
        />
        <div
          v-if="nutrition.ingredients.length === 0 && !nutrition.loading"
          class="text-center py-12 text-slate-500"
        >
          <ExperimentOutlined class="text-4xl mb-4 opacity-50" />
          <p>Chưa có nguyên liệu. Hãy thêm nguyên liệu đầu tiên!</p>
        </div>
        <div
          v-if="nutrition.ingredients.length > 0"
          class="mt-4 flex justify-center"
        >
          <a-pagination
            v-model:current="nutrition.ingredientPagination.currentPage"
            :total="nutrition.ingredientPagination.total"
            :page-size="nutrition.ingredientPagination.perPage"
            :show-total="(total) => `Tổng ${total} nguyên liệu`"
            @change="handleIngredientPageChange"
          />
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
          <RecipeCard
            v-for="recipe in nutrition.recipes"
            :key="recipe.id"
            :recipe="recipe"
            @edit="openRecipeModal"
            @delete="handleRecipeDelete"
          />
          <div
            v-if="nutrition.recipes.length === 0 && !nutrition.loading"
            class="col-span-full text-center py-12 text-slate-500"
          >
            <CoffeeOutlined class="text-4xl mb-4 opacity-50" />
            <p class="text-sm">Chưa có công thức. Hãy tạo công thức đầu tiên!</p>
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
        <div class="text-center py-12 text-slate-500">
          <CalendarOutlined class="text-4xl mb-4 opacity-50" />
          <p>Tính năng lập kế hoạch ăn uống đang được phát triển!</p>
        </div>
      </a-tab-pane>
    </a-tabs>

    <!-- Modals -->
    <IngredientModal
      v-model:open="ingredientModalOpen"
      v-model:form="ingredientForm"
      :loading="nutrition.loading"
      @submit="handleIngredientSubmit"
    />
    <RecipeModal
      v-model:open="recipeModalOpen"
      v-model:form="recipeForm"
      :recipe="editingRecipe"
      :loading="nutrition.loading"
      @submit="handleRecipeSubmit"
    />
  </div>
</template>
