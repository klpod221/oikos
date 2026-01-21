import api from "../utils/axios";

export const nutritionService = {
  // Ingredients
  getIngredients: (params = {}) => api.get("/ingredients", { params }),
  getIngredient: (id) => api.get(`/ingredients/${id}`),
  createIngredient: (data) => api.post("/ingredients", data),
  updateIngredient: (id, data) => api.put(`/ingredients/${id}`, data),
  deleteIngredient: (id) => api.delete(`/ingredients/${id}`),

  // Recipes
  getRecipes: (params = {}) => api.get("/recipes", { params }),
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
};
