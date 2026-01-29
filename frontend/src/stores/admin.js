/**
 * Admin Store
 *
 * Manages administrative state:
 * - Users (list, block, unblock)
 * - Categories (system categories management)
 * - Ingredients (global ingredients management)
 */
import { defineStore } from "pinia";
import { ref } from "vue";
import {
  adminUserService,
  adminCategoryService,
  adminIngredientService,
} from "../services/admin.service";

export const useAdminStore = defineStore("admin", () => {
  // User Management State
  const users = ref([]);
  const userFilters = ref({
    search: "",
    role: "",
    status: "",
    sort_by: "",
    sort_order: "desc",
  });
  const userPagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });

  // Category Management State
  const categories = ref([]);
  const categoryFilters = ref({
    search: "",
    type: "",
    sort_by: "",
    sort_order: "desc",
  });
  const categoryPagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });

  // Ingredient Management State
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

  // Common State
  const loading = ref(false);
  const error = ref(null);

  // User Management Actions
  const fetchUsers = async (page = 1) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await adminUserService.getUsers(
        userFilters.value,
        page,
        userPagination.value.perPage,
      );
      users.value = res.data.data;
      userPagination.value = {
        currentPage: res.data.meta.current_page,
        lastPage: res.data.meta.last_page,
        perPage: res.data.meta.per_page,
        total: res.data.meta.total,
      };
    } catch (e) {
      error.value =
        e.response?.data?.message || "Không thể tải danh sách người dùng";
      console.error("Failed to fetch users:", e);
    } finally {
      loading.value = false;
    }
  };

  const blockUser = async (userId) => {
    loading.value = true;
    error.value = null;
    try {
      await adminUserService.blockUser(userId);
      await fetchUsers(userPagination.value.currentPage);
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Không thể khóa người dùng";
      console.error("Failed to block user:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  const unblockUser = async (userId) => {
    loading.value = true;
    error.value = null;
    try {
      await adminUserService.unblockUser(userId);
      await fetchUsers(userPagination.value.currentPage);
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Không thể mở khóa người dùng";
      console.error("Failed to unblock user:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  const createUser = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      await adminUserService.createUser(data);
      await fetchUsers(1); // Refresh list, go to first page
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Không thể tạo người dùng";
      console.error("Failed to create user:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  const resetUserPassword = async (userId, password) => {
    loading.value = true;
    error.value = null;
    try {
      await adminUserService.resetUserPassword(userId, password);
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Không thể đặt lại mật khẩu";
      console.error("Failed to reset password:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  // Category Management Actions
  const fetchCategories = async (page = 1) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await adminCategoryService.getCategories(
        categoryFilters.value,
        page,
        categoryPagination.value.perPage,
      );
      categories.value = res.data.data;
      categoryPagination.value = {
        currentPage: res.data.meta.current_page,
        lastPage: res.data.meta.last_page,
        perPage: res.data.meta.per_page || 15,
        total: res.data.meta.total,
      };
    } catch (e) {
      error.value =
        e.response?.data?.message || "Không thể tải danh sách danh mục";
      console.error("Failed to fetch categories:", e);
    } finally {
      loading.value = false;
    }
  };

  const createCategory = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      await adminCategoryService.createCategory(data);
      await fetchCategories(categoryPagination.value.currentPage);
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Không thể tạo danh mục";
      console.error("Failed to create category:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  const updateCategory = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      await adminCategoryService.updateCategory(id, data);
      await fetchCategories(categoryPagination.value.currentPage);
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Không thể cập nhật danh mục";
      console.error("Failed to update category:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  const deleteCategory = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      await adminCategoryService.deleteCategory(id);
      await fetchCategories(categoryPagination.value.currentPage);
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Không thể xóa danh mục";
      console.error("Failed to delete category:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  // Ingredient Management Actions
  const fetchIngredients = async (page = 1) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await adminIngredientService.getIngredients(
        ingredientFilters.value,
        page,
        ingredientPagination.value.perPage,
      );
      ingredients.value = res.data.data;
      ingredientPagination.value = {
        currentPage: res.data.meta.current_page,
        lastPage: res.data.meta.last_page,
        perPage: res.data.meta.per_page || 15,
        total: res.data.meta.total,
      };
    } catch (e) {
      error.value =
        e.response?.data?.message || "Không thể tải danh sách nguyên liệu";
      console.error("Failed to fetch ingredients:", e);
    } finally {
      loading.value = false;
    }
  };

  const createIngredient = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      await adminIngredientService.createIngredient(data);
      await fetchIngredients(ingredientPagination.value.currentPage);
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Không thể tạo nguyên liệu";
      console.error("Failed to create ingredient:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  const updateIngredient = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      await adminIngredientService.updateIngredient(id, data);
      await fetchIngredients(ingredientPagination.value.currentPage);
      return true;
    } catch (e) {
      error.value =
        e.response?.data?.message || "Không thể cập nhật nguyên liệu";
      console.error("Failed to update ingredient:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  const deleteIngredient = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      await adminIngredientService.deleteIngredient(id);
      await fetchIngredients(ingredientPagination.value.currentPage);
      return true;
    } catch (e) {
      error.value = e.response?.data?.message || "Không thể xóa nguyên liệu";
      console.error("Failed to delete ingredient:", e);
      return false;
    } finally {
      loading.value = false;
    }
  };

  return {
    // User Management
    users,
    userFilters,
    userPagination,
    fetchUsers,
    blockUser,
    unblockUser,
    createUser,
    resetUserPassword,

    // Category Management
    categories,
    categoryFilters,
    categoryPagination,
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,

    // Ingredient Management
    ingredients,
    ingredientFilters,
    ingredientPagination,
    fetchIngredients,
    createIngredient,
    updateIngredient,
    deleteIngredient,

    // Common
    loading,
    error,
  };
});
