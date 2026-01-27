import api from "../utils/axios";

/**
 * Nutrition Service
 *
 * API endpoints for nutrition module.
 */
export const nutritionService = {
  // Ingredients
  getIngredients: (filters = {}, page = 1, perPage = 15) =>
    api.get("/ingredients", {
      params: { ...filters, page, per_page: perPage },
    }),
  getIngredient: (id) => api.get(`/ingredients/${id}`),
  createIngredient: (data) => api.post("/ingredients", data),
  updateIngredient: (id, data) => api.put(`/ingredients/${id}`, data),
  deleteIngredient: (id) => api.delete(`/ingredients/${id}`),

  // Recipes
  getRecipes: (filters = {}, page = 1, perPage = 15) =>
    api.get("/recipes", {
      params: { ...filters, page, per_page: perPage },
    }),
  getRecipe: (id) => api.get(`/recipes/${id}`),
  createRecipe: (data) => api.post("/recipes", data),
  updateRecipe: (id, data) => api.put(`/recipes/${id}`, data),
  deleteRecipe: (id) => api.delete(`/recipes/${id}`),

  // Meal Plans
  getMealPlans: (params = {}) => api.get("/meal-plans", { params }),
  getMealPlan: (id) => api.get(`/meal-plans/${id}`),
  createMealPlan: (data) => api.post("/meal-plans", data),
  updateMealPlan: (id, data) => api.put(`/meal-plans/${id}`, data),
  deleteMealPlan: (id) => api.delete(`/meal-plans/${id}`),

  // Shopping Lists
  previewShoppingList: (data) =>
    api.post("/nutrition/shopping-list/preview", data),
  createShoppingList: (data) => api.post("/nutrition/shopping-list", data),
  getShoppingLists: () => api.get("/nutrition/shopping-lists"),
  updateShoppingListItem: (listId, itemId, data) =>
    api.patch(`/nutrition/shopping-lists/${listId}/items/${itemId}`, data),

  // Nutrition Logs
  logNutrition: (data) => api.post("/nutrition/logs", data),
  getNutritionLogs: (params = {}) => api.get("/nutrition/logs", { params }),

  // Macros Progress
  getMacrosProgress: (params = {}) =>
    api.get("/nutrition/macros/progress", { params }),
};
