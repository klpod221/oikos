<!--
  Finance.vue

  Main finance module view.
  Tabs:
  - Wallets: Manage wallets
  - Transactions: List/Filter transactions
  - Savings Goals: Manage goals
  Features:
  - Create/Edit/Delete for all entities
-->
<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useFinanceStore } from "../../stores/finance";
import {
  SearchOutlined,
  PlusOutlined,
  WalletOutlined,
  SwapOutlined,
  TrophyOutlined,
} from "@ant-design/icons-vue";

// Components
import WalletCard from "../../components/finance/WalletCard.vue";
import WalletModal from "../../components/finance/WalletModal.vue";
import TransactionTable from "../../components/finance/TransactionTable.vue";
import TransactionModal from "../../components/finance/TransactionModal.vue";
import SavingsGoalCard from "../../components/finance/SavingsGoalCard.vue";
import SavingsGoalModal from "../../components/finance/SavingsGoalModal.vue";
import { formatCurrency } from "../../utils/formatters";

const finance = useFinanceStore();
const activeTab = ref("wallets");

// Modal states
const walletModalOpen = ref(false);
const transactionModalOpen = ref(false);
const savingsGoalModalOpen = ref(false);
const editingWallet = ref(null);
const editingTransaction = ref(null);
const editingGoal = ref(null);

// Transaction Filters
const searchQuery = ref("");
const typeFilter = ref(undefined);
const walletFilter = ref(undefined);
const dateRange = ref([]);
const sortState = ref({ field: null, order: null });

// Computed Pagination for Table
const paginationConfig = computed(() => ({
  current: finance.transactionPagination.current,
  pageSize: finance.transactionPagination.pageSize,
  total: finance.transactionPagination.total,
  showSizeChanger: true,
  showTotal: (total) => `Tổng ${total} giao dịch`,
}));

// Watch filters
watch([searchQuery, typeFilter, walletFilter, dateRange], () => {
  fetchTransactions(1);
});

const fetchTransactions = (page = 1) => {
  const params = {
    page,
    per_page: finance.transactionPagination.pageSize,
  };

  // Only add filters if they have values
  if (searchQuery.value) params.search = searchQuery.value;
  if (typeFilter.value) params.type = typeFilter.value;
  if (walletFilter.value) params.wallet_id = walletFilter.value;

  // Only add sort if we have a valid field
  if (sortState.value.field && sortState.value.order) {
    const sortField = sortState.value.field === 'date' ? 'transaction_date' : sortState.value.field;
    // Only send if it's a valid column
    if (['transaction_date', 'amount', 'created_at'].includes(sortField)) {
      params.sort_by = sortField;
      params.sort_order = sortState.value.order;
    }
  }

  if (dateRange.value && dateRange.value.length === 2) {
    params.start_date = dateRange.value[0].format("YYYY-MM-DD");
    params.end_date = dateRange.value[1].format("YYYY-MM-DD");
  }

  finance.fetchTransactions(params);
};

const handleTableChange = (pagination, filters, sorter) => {
  // Handle Sort
  if (sorter.field && sorter.order) {
    sortState.value = {
      field: sorter.field,
      order: sorter.order === "ascend" ? "asc" : "desc",
    };
  } else {
      // No sort applied
      sortState.value = { field: null, order: null };
  }
  
  // Handle Pagination (if page size changes, reset to page 1, else go to current)
  // But usually we just take pagination.current
  // If we are just changing page:
  if (pagination.pageSize !== finance.transactionPagination.pageSize) {
    finance.transactionPagination.pageSize = pagination.pageSize;
    fetchTransactions(1); // Reset to first page on size change
  } else {
    fetchTransactions(pagination.current);
  }
};

// Forms
const walletForm = ref({
  name: "",
  initial_balance: 0,
  currency: "VND",
  description: "",
  is_default: false,
  icon: "💰",
  color: "#3b82f6",
});
const transactionForm = ref({
  wallet_id: null,
  category_id: null,
  type: "expense",
  amount: 0,
  description: "",
  transaction_date: new Date().toISOString().split("T")[0],
});
const savingsGoalForm = ref({
  name: "",
  description: "",
  target_amount: 0,
  current_amount: 0,
  currency: "VND",
  start_date: null,
  deadline: null,
  status: "in_progress",
  icon: "🎯",
  color: "#10b981",
});

onMounted(async () => {
  await Promise.all([
    finance.fetchWallets(),
    finance.fetchTransactions(),
    finance.fetchCategories(),
    finance.fetchSavingsGoals(),
  ]);
});

// Category options based on transaction type
const categoryOptions = computed(() =>
  transactionForm.value.type === "income"
    ? finance.incomeCategories
    : finance.expenseCategories,
);

// Wallet handlers
const openWalletModal = (wallet = null) => {
  editingWallet.value = wallet;
  walletForm.value = wallet
    ? { ...wallet, initial_balance: wallet.balance }
    : {
        name: "",
        initial_balance: 0,
        currency: "VND",
        description: "",
        is_default: false,
        icon: "💰",
        color: "#3b82f6",
      };
  walletModalOpen.value = true;
};

const handleWalletSubmit = async (data) => {
  const success = editingWallet.value
    ? await finance.updateWallet(editingWallet.value.id, data)
    : await finance.createWallet(data);
  if (success) walletModalOpen.value = false;
};

const handleWalletDelete = async (id) => {
  await finance.deleteWallet(id);
};

// Transaction handlers
// Transaction handlers
const openTransactionModal = (transaction = null) => {
  editingTransaction.value = transaction;
  transactionForm.value = transaction
    ? { ...transaction }
    : {
        wallet_id: finance.wallets[0]?.id || null,
        category_id: null,
        type: "expense",
        amount: 0,
        description: "",
        transaction_date: new Date().toISOString().split("T")[0],
      };
  transactionModalOpen.value = true;
};

const handleTransactionSubmit = async (data) => {
  const success = editingTransaction.value
    ? await finance.updateTransaction(editingTransaction.value.id, data)
    : await finance.createTransaction(data);
  if (success) transactionModalOpen.value = false;
};

const handleTransactionDelete = async (id) => {
  await finance.deleteTransaction(id);
};

// Savings Goal handlers
const openSavingsGoalModal = (goal = null) => {
  editingGoal.value = goal;
  savingsGoalModalOpen.value = true;
};

const handleSavingsGoalSubmit = async (data) => {
  const success = editingGoal.value
    ? await finance.updateSavingsGoal(editingGoal.value.id, data)
    : await finance.createSavingsGoal(data);
  if (success) savingsGoalModalOpen.value = false;
};

const handleSavingsGoalDelete = async (id) => {
  await finance.deleteSavingsGoal(id);
};
</script>

<template>
  <div class="space-y-3 sm:space-y-4">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 sm:gap-3"
    >
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Tài chính</h1>
        <p class="text-xs sm:text-sm text-slate-500">Quản lý ví và giao dịch của bạn</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <a-button @click="openTransactionModal()" size="middle">
          <template #icon><SwapOutlined /></template>
          <span class="hidden! sm:inline!">Giao dịch mới</span>
          <span class="ml-1 sm:hidden!">Giao dịch</span>
        </a-button>
        <a-button @click="openSavingsGoalModal()" size="middle">
          <template #icon><TrophyOutlined /></template>
          <span class="hidden! sm:inline!">Mục tiêu mới</span>
          <span class="ml-1 sm:hidden!">Mục tiêu</span>
        </a-button>
        <a-button type="primary" @click="openWalletModal()" size="middle">
          <template #icon><PlusOutlined /></template>
          <span class="hidden! sm:inline!">Ví mới</span>
          <span class="ml-1 sm:hidden!">Ví</span>
        </a-button>
      </div>
    </div>

    <!-- Total Balance Card -->
    <div
      class="bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl p-4 sm:p-6 text-white"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-blue-100 text-xs sm:text-sm">Tổng số dư</p>
          <h2 class="text-2xl sm:text-3xl font-bold mt-1">
            {{ formatCurrency(finance.totalBalance, "VND") }}
          </h2>
        </div>
        <WalletOutlined class="text-3xl sm:text-4xl opacity-50" />
      </div>
    </div>

    <!-- Tabs -->
    <a-tabs v-model:activeKey="activeTab">
      <!-- Wallets Tab -->
      <a-tab-pane key="wallets" tab="Ví của tôi">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
          <WalletCard
            v-for="wallet in finance.wallets"
            :key="wallet.id"
            :wallet="wallet"
            @edit="openWalletModal"
            @delete="handleWalletDelete"
          />
          <div
            v-if="finance.wallets.length === 0 && !finance.loading"
            class="col-span-full text-center py-12 text-slate-500"
          >
            <WalletOutlined class="text-4xl mb-4 opacity-50" />
            <p class="text-sm">Chưa có ví nào. Hãy tạo ví đầu tiên của bạn!</p>
          </div>
        </div>
      </a-tab-pane>

      <!-- Transactions Tab -->
      <a-tab-pane key="transactions" tab="Giao dịch">
        <!-- Filters -->
        <div class="mb-4">
          <a-row :gutter="[8, 8]">
            <a-col :xs="24" :sm="12" :md="6">
              <a-input
                v-model:value="searchQuery"
                placeholder="Tìm kiếm giao dịch..."
                allow-clear
              >
                <template #prefix>
                  <SearchOutlined class="text-slate-400" />
                </template>
              </a-input>
            </a-col>
            <a-col :xs="12" :sm="6" :md="4">
              <a-select
                v-model:value="typeFilter"
                placeholder="Loại"
                allow-clear
                class="w-full"
              >
                <a-select-option value="income">Thu nhập</a-select-option>
                <a-select-option value="expense">Chi tiêu</a-select-option>
              </a-select>
            </a-col>
            <a-col :xs="12" :sm="6" :md="4">
              <a-select
                v-model:value="walletFilter"
                placeholder="Ví"
                allow-clear
                class="w-full"
              >
                <a-select-option
                  v-for="wallet in finance.wallets"
                  :key="wallet.id"
                  :value="wallet.id"
                >
                  {{ wallet.name }}
                </a-select-option>
              </a-select>
            </a-col>
            <a-col :xs="24" :sm="12" :md="6">
                <a-range-picker v-model:value="dateRange" class="w-full" />
            </a-col>
          </a-row>
        </div>

        <TransactionTable
          :transactions="finance.transactions"
          :loading="finance.loading"
          :pagination="paginationConfig"
          @change="handleTableChange"
          @edit="openTransactionModal"
          @delete="handleTransactionDelete"
        />
        <div
          v-if="finance.transactions.length === 0 && !finance.loading"
          class="text-center py-12 text-slate-500"
        >
          <SwapOutlined class="text-4xl mb-4 opacity-50" />
          <p>Chưa có giao dịch. Thêm giao dịch đầu tiên của bạn!</p>
        </div>
      </a-tab-pane>

      <!-- Savings Goals Tab -->
      <a-tab-pane key="savings" tab="Mục tiêu tiết kiệm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
          <SavingsGoalCard
            v-for="goal in finance.savingsGoals"
            :key="goal.id"
            :goal="goal"
            @edit="openSavingsGoalModal"
            @delete="handleSavingsGoalDelete"
          />
          <div
            v-if="finance.savingsGoals.length === 0 && !finance.loading"
            class="col-span-full text-center py-12 text-slate-500"
          >
            <TrophyOutlined class="text-4xl mb-4 opacity-50" />
            <p class="text-sm">Chưa có mục tiêu. Hãy bắt đầu tiết kiệm cho ước mơ của bạn!</p>
          </div>
        </div>
      </a-tab-pane>
    </a-tabs>

    <!-- Modals -->
    <WalletModal
      v-model:open="walletModalOpen"
      v-model:form="walletForm"
      :wallet="editingWallet"
      :loading="finance.loading"
      @submit="handleWalletSubmit"
    />
    <TransactionModal
      v-model:open="transactionModalOpen"
      v-model:form="transactionForm"
      :wallets="finance.wallets"
      :categories="categoryOptions"
      :loading="finance.loading"
      @submit="handleTransactionSubmit"
    />
    <SavingsGoalModal
      v-model:open="savingsGoalModalOpen"
      v-model:form="savingsGoalForm"
      :goal="editingGoal"
      :loading="finance.loading"
      @submit="handleSavingsGoalSubmit"
    />
  </div>
</template>
