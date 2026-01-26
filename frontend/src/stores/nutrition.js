/**
 * Nutrition Store
 *
 * Manages nutrition module state:
 * - Ingredients
 * - Recipes
 * - Meal Plans (future)
 */
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
  const allRecipes = ref([]); // For dropdowns
  const recipeFilters = ref({
    search: "",
    sort_by: "",
    sort_order: "desc",
  });
  const recipePagination = ref({
    currentPage: 1,
    lastPage: 1,
    total: 0,
  });

  // Meal Plan State
  const mealPlans = ref([]);

  const loading = ref(false);

  // Ingredients Actions
  const fetchIngredients = async (page = 1) => {
    loading.value = true;
    try {
      const res = await nutritionService.getIngredients(
        ingredientFilters.value,
        page,
        ingredientPagination.value.perPage,
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

  const updateIngredient = async (id, data) => {
    try {
      await nutritionService.updateIngredient(id, data);
      message.success("Ingredient updated");
      await fetchIngredients(ingredientPagination.value.currentPage);
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to update ingredient");
      return false;
    }
  };

  const deleteIngredient = async (id) => {
    try {
      await nutritionService.deleteIngredient(id);
      message.success("Ingredient deleted");
      await fetchIngredients(ingredientPagination.value.currentPage);
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to delete ingredient");
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
        recipePagination.value.perPage,
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

  const fetchAllRecipes = async () => {
    try {
      const res = await nutritionService.getRecipes(
        { sort_by: "name", sort_order: "asc" },
        1,
        1000, // Fetch all (limit 1000)
      );
      allRecipes.value = res.data.data || [];
    } catch (e) {
      console.error("Failed to fetch all recipes", e);
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

  const updateRecipe = async (id, data) => {
    try {
      await nutritionService.updateRecipe(id, data);
      message.success("Recipe updated");
      await fetchRecipes(recipePagination.value.currentPage);
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to update recipe");
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
      return false;
    }
  };

  // Meal Plan Actions
  const fetchMealPlans = async (start, end) => {
    loading.value = true;
    try {
      const res = await nutritionService.getMealPlans({
        start_date: start,
        end_date: end,
      });
      mealPlans.value = res.data.data || [];
    } catch (e) {
      console.error("Failed to fetch meal plans", e);
    } finally {
      loading.value = false;
    }
  };

  const createMealPlan = async (data) => {
    try {
      await nutritionService.createMealPlan(data);
      message.success("Đã thêm kế hoạch ăn uống");
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi thêm kế hoạch");
      return false;
    }
  };

  const updateMealPlan = async (id, data) => {
    try {
      await nutritionService.updateMealPlan(id, data);
      message.success("Đã cập nhật kế hoạch");
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi cập nhật kế hoạch");
      return false;
    }
  };

  const deleteMealPlan = async (id) => {
    try {
      await nutritionService.deleteMealPlan(id);
      message.success("Đã xóa kế hoạch");
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi xóa kế hoạch");
      return false;
    }
  };

  // Shopping Lists State
  const shoppingLists = ref([]);
  const shoppingListPreview = ref(null);

  // Nutrition Logs State
  const nutritionLogs = ref([]);
  const macrosProgress = ref({
    protein: { current: 0, target: 150, percentage: 0 },
    carbs: { current: 0, target: 200, percentage: 0 },
    fat: { current: 0, target: 60, percentage: 0 },
  });

  // Shopping Lists Actions
  const previewShoppingList = async (data) => {
    try {
      const res = await nutritionService.previewShoppingList(data);
      shoppingListPreview.value = res.data;
      return res.data;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi tạo preview");
      return null;
    }
  };

  const createShoppingList = async (data) => {
    try {
      await nutritionService.createShoppingList(data);
      message.success("Đã tạo danh sách mua sắm");
      await fetchShoppingLists();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi tạo danh sách");
      return false;
    }
  };

  const fetchShoppingLists = async () => {
    try {
      const res = await nutritionService.getShoppingLists();
      shoppingLists.value = res.data?.data || res.data || [];
    } catch (e) {
      console.error("Failed to fetch shopping lists", e);
    }
  };

  const updateShoppingListItem = async (listId, itemId, data) => {
    try {
      await nutritionService.updateShoppingListItem(listId, itemId, data);
      await fetchShoppingLists();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi cập nhật");
      return false;
    }
  };

  // Nutrition Logs Actions
  const logNutrition = async (data) => {
    try {
      await nutritionService.logNutrition(data);
      message.success("Đã ghi nhận dinh dưỡng");
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi ghi nhận");
      return false;
    }
  };

  const fetchNutritionLogs = async (params = {}) => {
    try {
      const res = await nutritionService.getNutritionLogs(params);
      nutritionLogs.value = res.data?.data || res.data || [];
    } catch (e) {
      console.error("Failed to fetch nutrition logs", e);
    }
  };

  const fetchMacrosProgress = async (date) => {
    try {
      const res = await nutritionService.getMacrosProgress({ date });
      macrosProgress.value = res.data || macrosProgress.value;
    } catch (e) {
      console.error("Failed to fetch macros progress", e);
    }
  };

  return {
    // Ingredient State
    ingredients,
    ingredientFilters,
    ingredientPagination,
    // Recipe State
    recipes,
    allRecipes,
    recipeFilters,
    recipePagination,
    // Common
    loading,
    // Actions
    fetchIngredients,
    createIngredient,
    updateIngredient,
    deleteIngredient,
    fetchRecipes,
    fetchAllRecipes,
    createRecipe,
    updateRecipe,
    deleteRecipe,

    // Meal Plan State
    mealPlans,
    // Meal Plan Actions
    fetchMealPlans,
    createMealPlan,
    updateMealPlan,
    deleteMealPlan,

    // Shopping Lists
    shoppingLists,
    shoppingListPreview,
    previewShoppingList,
    createShoppingList,
    fetchShoppingLists,
    updateShoppingListItem,

    // Nutrition Logs
    nutritionLogs,
    macrosProgress,
    logNutrition,
    fetchNutritionLogs,
    fetchMacrosProgress,
  };
});
