import { defineStore } from "pinia";
import { ref } from "vue";
import { nutritionService } from "../services/nutrition.service";
import { message } from "ant-design-vue";

export const useNutritionStore = defineStore("nutrition", () => {
  // Ingredient State
  const ingredients = ref([]);
  const ingredientFilters = ref({
    search: "",
    sort_by: "",
    sort_order: "desc",
  });
  const ingredientPagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });

  // Recipe State
  const recipes = ref([]);
  const recipeFilters = ref({
    search: "",
    sort_by: "",
    sort_order: "desc",
  });
  const recipePagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });

  const loading = ref(false);

  // Ingredients Actions
  const fetchIngredients = async (page = 1) => {
    loading.value = true;
    try {
      const res = await nutritionService.getIngredients(
        ingredientFilters.value,
        page,
        ingredientPagination.value.perPage
      );
      ingredients.value = res.data.data || [];
      if (res.data.meta) {
        ingredientPagination.value = {
          currentPage: res.data.meta.current_page,
          lastPage: res.data.meta.last_page,
          perPage: res.data.meta.per_page || 15,
          total: res.data.meta.total,
        };
      }
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
      await fetchIngredients(ingredientPagination.value.currentPage);
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to add ingredient");
      return false;
    }
  };

  // Recipes Actions
  const fetchRecipes = async (page = 1) => {
    loading.value = true;
    try {
      const res = await nutritionService.getRecipes(
        recipeFilters.value,
        page,
        recipePagination.value.perPage
      );
      recipes.value = res.data.data || [];
      if (res.data.meta) {
        recipePagination.value = {
          currentPage: res.data.meta.current_page,
          lastPage: res.data.meta.last_page,
          perPage: res.data.meta.per_page || 15,
          total: res.data.meta.total,
        };
      }
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
      await fetchRecipes(recipePagination.value.currentPage);
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
      await fetchRecipes(recipePagination.value.currentPage);
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to delete recipe");
      return false;
    }
  };

  return {
    // Ingredient State
    ingredients,
    ingredientFilters,
    ingredientPagination,
    // Recipe State
    recipes,
    recipeFilters,
    recipePagination,
    // Common
    loading,
    // Actions
    fetchIngredients,
    createIngredient,
    fetchRecipes,
    createRecipe,
    deleteRecipe,
  };
});
