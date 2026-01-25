/**
 * Finance Store
 *
 * Manages finance module state:
 * - Wallets
 * - Transactions
 * - Savings Goals
 * - Categories
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { financeService } from "../services/finance.service";
import { message } from "ant-design-vue";

export const useFinanceStore = defineStore("finance", () => {
  // State
  const wallets = ref([]);
  const transactions = ref([]);
  const transactionPagination = ref({
    current: 1,
    pageSize: 10,
    total: 0,
  });
  const categories = ref([]);
  const savingsGoals = ref([]);
  const loading = ref(false);

  // Computed
  const totalBalance = computed(() =>
    wallets.value.reduce((sum, w) => sum + Number(w.balance || 0), 0),
  );

  const incomeCategories = computed(() =>
    categories.value.filter((c) => c.type === "income"),
  );

  const expenseCategories = computed(() =>
    categories.value.filter((c) => c.type === "expense"),
  );

  // Wallet Actions
  const fetchWallets = async () => {
    loading.value = true;
    try {
      const res = await financeService.getWallets();
      wallets.value = res.data.data || [];
    } catch (e) {
      console.error("Failed to fetch wallets", e);
    } finally {
      loading.value = false;
    }
  };

  const createWallet = async (data) => {
    try {
      await financeService.createWallet(data);
      message.success("Wallet created successfully");
      await fetchWallets();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to create wallet");
      return false;
    }
  };

  const updateWallet = async (id, data) => {
    try {
      await financeService.updateWallet(id, data);
      message.success("Wallet updated successfully");
      await fetchWallets();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to update wallet");
      return false;
    }
  };

  const deleteWallet = async (id) => {
    try {
      await financeService.deleteWallet(id);
      message.success("Wallet deleted successfully");
      await fetchWallets();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Failed to delete wallet");
      return false;
    }
  };

  // Category Actions
  const fetchCategories = async (type = null) => {
    try {
      const res = await financeService.getCategories(type ? { type } : {});
      categories.value = res.data.data || [];
    } catch (e) {
      console.error("Failed to fetch categories", e);
    }
  };

  // Transaction Actions
  const fetchTransactions = async (params = {}) => {
    loading.value = true;
    try {
      const res = await financeService.getTransactions(params);
      transactions.value = res.data.data || [];
      const meta = res.data.meta;
      if (meta) {
        transactionPagination.value = {
          current: meta.current_page,
          pageSize: Number(res.data.meta.per_page || 10), // Backend might not return per_page in meta if using standard Laravel resource collection sometimes, but Controller does return it in meta.
          total: meta.total,
        };
      }
    } catch (e) {
      console.error("Failed to fetch transactions", e);
    } finally {
      loading.value = false;
    }
  };

  const createTransaction = async (data) => {
    try {
      await financeService.createTransaction(data);
      message.success("Transaction created successfully");
      await fetchTransactions();
      await fetchWallets(); // Refresh balances
      return true;
    } catch (e) {
      message.error(
        e.response?.data?.message || "Failed to create transaction",
      );
      return false;
    }
  };

  const updateTransaction = async (id, data) => {
    try {
      await financeService.updateTransaction(id, data);
      message.success("Transaction updated successfully");
      await fetchTransactions();
      await fetchWallets();
      return true;
    } catch (e) {
      message.error(
        e.response?.data?.message || "Failed to update transaction",
      );
      return false;
    }
  };

  const deleteTransaction = async (id) => {
    try {
      await financeService.deleteTransaction(id);
      message.success("Transaction deleted successfully");
      await fetchTransactions();
      await fetchWallets();
      return true;
    } catch (e) {
      message.error(
        e.response?.data?.message || "Failed to delete transaction",
      );
      return false;
    }
  };

  // Savings Goals Actions
  const fetchSavingsGoals = async () => {
    try {
      const res = await financeService.getSavingsGoals();
      savingsGoals.value = res.data.data || [];
    } catch (e) {
      console.error("Failed to fetch savings goals", e);
    }
  };

  const createSavingsGoal = async (data) => {
    try {
      await financeService.createSavingsGoal(data);
      message.success("Savings goal created successfully");
      await fetchSavingsGoals();
      return true;
    } catch (e) {
      message.error(
        e.response?.data?.message || "Failed to create savings goal",
      );
      return false;
    }
  };

  const updateSavingsGoal = async (id, data) => {
    try {
      await financeService.updateSavingsGoal(id, data);
      message.success("Savings goal updated successfully");
      await fetchSavingsGoals();
      return true;
    } catch (e) {
      message.error(
        e.response?.data?.message || "Failed to update savings goal",
      );
      return false;
    }
  };

  const deleteSavingsGoal = async (id) => {
    try {
      await financeService.deleteSavingsGoal(id);
      message.success("Savings goal deleted successfully");
      await fetchSavingsGoals();
      return true;
    } catch (e) {
      message.error(
        e.response?.data?.message || "Failed to delete savings goal",
      );
      return false;
    }
  };

  return {
    // State
    wallets,
    transactions,
    transactionPagination,
    categories,
    savingsGoals,
    loading,
    // Computed
    totalBalance,
    incomeCategories,
    expenseCategories,
    // Actions
    fetchWallets,
    createWallet,
    updateWallet,
    deleteWallet,
    fetchCategories,
    fetchTransactions,
    createTransaction,
    updateTransaction,
    deleteTransaction,
    fetchSavingsGoals,
    createSavingsGoal,
    updateSavingsGoal,
    deleteSavingsGoal,
  };
});
