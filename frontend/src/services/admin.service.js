import api from "../utils/axios";

/**
 * Admin Service
 * Service layer for admin-related API calls
 */

// User Management APIs
export const adminUserService = {
  /**
   * Get paginated list of users with optional filters
   * @param {Object} filters - { search, role, status }
   * @param {number} page - Page number
   * @param {number} perPage - Items per page
   */
  getUsers(filters = {}, page = 1, perPage = 15) {
    return api.get("/admin/users", {
      params: {
        ...filters,
        page,
        per_page: perPage,
      },
    });
  },

  /**
   * Get user details by ID
   * @param {number} id - User ID
   */
  getUser(id) {
    return api.get(`/admin/users/${id}`);
  },

  /**
   * Block a user
   * @param {number} id - User ID
   */
  blockUser(id) {
    return api.post(`/admin/users/${id}/block`);
  },

  /**
   * Unblock a user
   * @param {number} id - User ID
   */
  unblockUser(id) {
    return api.post(`/admin/users/${id}/unblock`);
  },

  /**
   * Create a new user
   * @param {Object} data - User data
   */
  createUser(data) {
    return api.post("/admin/users", data);
  },

  /**
   * Reset user password
   * @param {number} id - User ID
   * @param {string} password - New password
   */
  resetUserPassword(id, password) {
    return api.post(`/admin/users/${id}/reset-password`, {
      password,
      password_confirmation: password,
    });
  },
};

// Category Management APIs
export const adminCategoryService = {
  /**
   * Get paginated list of system categories with optional filters
   * @param {Object} filters - { search, type }
   * @param {number} page - Page number
   * @param {number} perPage - Items per page
   */
  getCategories(filters = {}, page = 1, perPage = 15) {
    return api.get("/admin/categories", {
      params: {
        ...filters,
        page,
        per_page: perPage,
      },
    });
  },

  /**
   * Get category details by ID
   * @param {number} id - Category ID
   */
  getCategory(id) {
    return api.get(`/admin/categories/${id}`);
  },

  /**
   * Create a new system category
   * @param {Object} data - Category data
   */
  createCategory(data) {
    return api.post("/admin/categories", data);
  },

  /**
   * Update a system category
   * @param {number} id - Category ID
   * @param {Object} data - Category data
   */
  updateCategory(id, data) {
    return api.put(`/admin/categories/${id}`, data);
  },

  /**
   * Delete a system category
   * @param {number} id - Category ID
   */
  deleteCategory(id) {
    return api.delete(`/admin/categories/${id}`);
  },
};

// Ingredient Management APIs
export const adminIngredientService = {
  /**
   * Get paginated list of global ingredients with optional filters
   * @param {Object} filters - { search }
   * @param {number} page - Page number
   * @param {number} perPage - Items per page
   */
  getIngredients(filters = {}, page = 1, perPage = 15) {
    return api.get("/admin/ingredients", {
      params: {
        ...filters,
        page,
        per_page: perPage,
      },
    });
  },

  /**
   * Get ingredient details by ID
   * @param {number} id - Ingredient ID
   */
  getIngredient(id) {
    return api.get(`/admin/ingredients/${id}`);
  },

  /**
   * Create a new global ingredient
   * @param {Object} data - Ingredient data
   */
  createIngredient(data) {
    return api.post("/admin/ingredients", data);
  },

  /**
   * Update a global ingredient
   * @param {number} id - Ingredient ID
   * @param {Object} data - Ingredient data
   */
  updateIngredient(id, data) {
    return api.put(`/admin/ingredients/${id}`, data);
  },

  /**
   * Delete a global ingredient
   * @param {number} id - Ingredient ID
   */
  deleteIngredient(id) {
    return api.delete(`/admin/ingredients/${id}`);
  },
};

// System Settings APIs
export const adminSettingService = {
  /**
   * Get all system settings
   */
  getSettings() {
    return api.get("/admin/settings");
  },

  /**
   * Update system settings
   * @param {Object} data - Payload { settings: [] }
   */
  updateSettings(data) {
    return api.post("/admin/settings", data);
  },
};
