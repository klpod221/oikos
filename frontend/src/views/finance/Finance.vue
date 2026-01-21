<script setup>
import { ref, onMounted, computed } from "vue";
import { useFinanceStore } from "../../stores/finance";
import {
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
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-4"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Finance</h1>
        <p class="text-slate-500">Manage your wallets and transactions</p>
      </div>
      <div class="flex gap-2">
        <a-button @click="openTransactionModal()">
          <template #icon><SwapOutlined /></template>
          New Transaction
        </a-button>
        <a-button @click="openSavingsGoalModal()">
          <template #icon><TrophyOutlined /></template>
          New Goal
        </a-button>
        <a-button type="primary" @click="openWalletModal()">
          <template #icon><PlusOutlined /></template>
          New Wallet
        </a-button>
      </div>
    </div>

    <!-- Total Balance Card -->
    <div
      class="bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-blue-100 text-sm">Total Balance</p>
          <h2 class="text-3xl font-bold mt-1">
            {{ formatCurrency(finance.totalBalance, "VND") }}
          </h2>
        </div>
        <WalletOutlined class="text-4xl opacity-50" />
      </div>
    </div>

    <!-- Tabs -->
    <a-tabs v-model:activeKey="activeTab">
      <!-- Wallets Tab -->
      <a-tab-pane key="wallets" tab="Wallets">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
            <p>No wallets yet. Create your first wallet!</p>
          </div>
        </div>
      </a-tab-pane>

      <!-- Transactions Tab -->
      <a-tab-pane key="transactions" tab="Transactions">
        <TransactionTable
          :transactions="finance.transactions"
          :loading="finance.loading"
          @edit="openTransactionModal"
          @delete="handleTransactionDelete"
        />
        <div
          v-if="finance.transactions.length === 0 && !finance.loading"
          class="text-center py-12 text-slate-500"
        >
          <SwapOutlined class="text-4xl mb-4 opacity-50" />
          <p>No transactions yet. Add your first transaction!</p>
        </div>
      </a-tab-pane>

      <!-- Savings Goals Tab -->
      <a-tab-pane key="savings" tab="Savings Goals">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
            <p>No savings goals yet. Start saving for your dreams!</p>
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
