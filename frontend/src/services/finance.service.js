import api from "../utils/axios";

export const financeService = {
  // Wallets
  getWallets: () => api.get("/wallets"),
  getWallet: (id) => api.get(`/wallets/${id}`),
  createWallet: (data) => api.post("/wallets", data),
  updateWallet: (id, data) => api.put(`/wallets/${id}`, data),
  deleteWallet: (id) => api.delete(`/wallets/${id}`),

  // Categories
  getCategories: (params = {}) => api.get("/categories", { params }),
  createCategory: (data) => api.post("/categories", data),
  updateCategory: (id, data) => api.put(`/categories/${id}`, data),
  deleteCategory: (id) => api.delete(`/categories/${id}`),

  // Transactions
  getTransactions: (params = {}) => api.get("/transactions", { params }),
  getTransaction: (id) => api.get(`/transactions/${id}`),
  createTransaction: (data) => api.post("/transactions", data),
  updateTransaction: (id, data) => api.put(`/transactions/${id}`, data),
  deleteTransaction: (id) => api.delete(`/transactions/${id}`),

  // Savings Goals
  getSavingsGoals: () => api.get("/savings-goals"),
  getSavingsGoal: (id) => api.get(`/savings-goals/${id}`),
  createSavingsGoal: (data) => api.post("/savings-goals", data),
  updateSavingsGoal: (id, data) => api.put(`/savings-goals/${id}`, data),
  deleteSavingsGoal: (id) => api.delete(`/savings-goals/${id}`),
};
