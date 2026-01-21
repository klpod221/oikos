import { defineStore } from "pinia";
import { ref } from "vue";
import { nutritionService } from "../services/nutrition.service";
import { message } from "ant-design-vue";

export const useNutritionStore = defineStore("nutrition", () => {
  // State
  const ingredients = ref([]);
  const recipes = ref([]);
  const mealPlans = ref([]);
  const loading = ref(false);

  // Ingredients Actions
  const fetchIngredients = async (params = {}) => {
    loading.value = true;
    try {
      const res = await nutritionService.getIngredients(params);
      ingredients.value = res.data.data || [];
    } catch (e) {
      console.error("Failed to fetch ingredients", e);
    } finally {
      loading.value = false;
    }
  };

  const createIngredient = async (data) => {
    try {
      await nutritionService.createIngredient(data);
      message.success("Ingredient added");
      await fetchIngredients();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to add ingredient");
      return false;
    }
  };

  // Recipes Actions
  const fetchRecipes = async (params = {}) => {
    loading.value = true;
    try {
      const res = await nutritionService.getRecipes(params);
      recipes.value = res.data.data || [];
    } catch (e) {
      console.error("Failed to fetch recipes", e);
    } finally {
      loading.value = false;
    }
  };

  const createRecipe = async (data) => {
    try {
      await nutritionService.createRecipe(data);
      message.success("Recipe created");
      await fetchRecipes();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to create recipe");
      return false;
    }
  };

  const deleteRecipe = async (id) => {
    try {
      await nutritionService.deleteRecipe(id);
      message.success("Recipe deleted");
      await fetchRecipes();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to delete recipe");
      return false;
    }
  };

  // Meal Plans Actions
  const fetchMealPlans = async (params = {}) => {
    loading.value = true;
    try {
      const res = await nutritionService.getMealPlans(params);
      mealPlans.value = res.data.data || [];
    } catch (e) {
      console.error("Failed to fetch meal plans", e);
    } finally {
      loading.value = false;
    }
  };

  return {
    // State
    ingredients,
    recipes,
    mealPlans,
    loading,
    // Actions
    fetchIngredients,
    createIngredient,
    fetchRecipes,
    createRecipe,
    deleteRecipe,
    fetchMealPlans,
  };
});
