<script setup>
import { ref, onMounted } from "vue";
import { useNutritionStore } from "../../stores/nutrition";
import {
  PlusOutlined,
  ExperimentOutlined,
  CoffeeOutlined,
  CalendarOutlined,
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
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-4"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Nutrition</h1>
        <p class="text-slate-500">
          Manage your ingredients, recipes, and meal plans
        </p>
      </div>
    </div>

    <!-- Tabs -->
    <a-tabs v-model:activeKey="activeTab">
      <!-- Ingredients Tab -->
      <a-tab-pane key="ingredients">
        <template #tab
          ><span><ExperimentOutlined /> Ingredients</span></template
        >
        <div class="mb-4 flex justify-end">
          <a-button type="primary" @click="openIngredientModal">
            <template #icon><PlusOutlined /></template>
            Add Ingredient
          </a-button>
        </div>
        <IngredientTable
          :ingredients="nutrition.ingredients"
          :loading="nutrition.loading"
        />
        <div
          v-if="nutrition.ingredients.length === 0 && !nutrition.loading"
          class="text-center py-12 text-slate-500"
        >
          <ExperimentOutlined class="text-4xl mb-4 opacity-50" />
          <p>No ingredients yet. Add your first ingredient!</p>
        </div>
      </a-tab-pane>

      <!-- Recipes Tab -->
      <a-tab-pane key="recipes">
        <template #tab
          ><span><CoffeeOutlined /> Recipes</span></template
        >
        <div class="mb-4 flex justify-end">
          <a-button type="primary" @click="openRecipeModal()">
            <template #icon><PlusOutlined /></template>
            New Recipe
          </a-button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
            <p>No recipes yet. Create your first recipe!</p>
          </div>
        </div>
      </a-tab-pane>

      <!-- Meal Plans Tab -->
      <a-tab-pane key="mealplans">
        <template #tab
          ><span><CalendarOutlined /> Meal Plans</span></template
        >
        <div class="text-center py-12 text-slate-500">
          <CalendarOutlined class="text-4xl mb-4 opacity-50" />
          <p>Meal planning feature coming soon!</p>
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
