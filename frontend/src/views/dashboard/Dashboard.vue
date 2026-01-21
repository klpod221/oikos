<script setup>
import { onMounted, computed } from "vue";
import { useFinanceStore } from "../../stores/finance";
import { useAuthStore } from "../../stores/auth";
import {
  WalletOutlined,
  ArrowUpOutlined,
  ArrowDownOutlined,
  PlusOutlined,
  RightOutlined,
} from "@ant-design/icons-vue";
import { useRouter } from "vue-router";

const router = useRouter();
const finance = useFinanceStore();
const auth = useAuthStore();

onMounted(async () => {
  await Promise.all([
    finance.fetchWallets(),
    finance.fetchTransactions({ limit: 5 }),
  ]);
  auth.fetchUser();
});

// Calculate monthly stats (placeholder - would need date filtering from API)
const monthlyIncome = computed(() => {
  return finance.transactions
    .filter((t) => t.type === "income")
    .reduce((sum, t) => sum + Number(t.amount), 0);
});

const monthlyExpense = computed(() => {
  return finance.transactions
    .filter((t) => t.type === "expense")
    .reduce((sum, t) => sum + Number(t.amount), 0);
});
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-4"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
        <p class="text-slate-500">Welcome back, {{ auth.user?.name }}</p>
      </div>
      <a-button type="primary" @click="router.push('/finance')">
        <template #icon><PlusOutlined /></template>
        New Transaction
      </a-button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Total Balance -->
      <div
        class="bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white"
      >
        <div class="flex items-center justify-between mb-4">
          <span class="text-blue-100">Total Balance</span>
          <WalletOutlined class="text-2xl opacity-80" />
        </div>
        <div class="text-3xl font-bold">
          ${{ finance.totalBalance.toFixed(2) }}
        </div>
        <div class="text-blue-200 text-sm mt-2">
          {{ finance.wallets.length }} wallet(s)
        </div>
      </div>

      <!-- Income -->
      <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
          <span class="text-slate-500">Income</span>
          <div
            class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center"
          >
            <ArrowUpOutlined class="text-green-600" />
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-800">
          ${{ monthlyIncome.toFixed(2) }}
        </div>
        <div class="text-green-600 text-sm mt-2">This period</div>
      </div>

      <!-- Expenses -->
      <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
          <span class="text-slate-500">Expenses</span>
          <div
            class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center"
          >
            <ArrowDownOutlined class="text-red-600" />
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-800">
          ${{ monthlyExpense.toFixed(2) }}
        </div>
        <div class="text-red-600 text-sm mt-2">This period</div>
      </div>
    </div>

    <!-- Wallets Overview -->
    <div class="bg-white border border-slate-200 rounded-xl">
      <div
        class="p-4 border-b border-slate-200 flex justify-between items-center"
      >
        <h2 class="font-semibold text-slate-800">My Wallets</h2>
        <a-button type="link" @click="router.push('/finance')">
          View All <RightOutlined />
        </a-button>
      </div>
      <div class="p-4">
        <div
          v-if="finance.wallets.length === 0"
          class="text-center py-8 text-slate-400"
        >
          No wallets yet
        </div>
        <div
          v-else
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"
        >
          <div
            v-for="wallet in finance.wallets.slice(0, 3)"
            :key="wallet.id"
            class="p-4 bg-slate-50 rounded-lg"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center"
              >
                <WalletOutlined class="text-blue-600" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-medium text-slate-800 truncate">
                  {{ wallet.name }}
                </div>
                <div class="text-lg font-bold text-slate-800">
                  ${{ Number(wallet.balance).toFixed(2) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white border border-slate-200 rounded-xl">
      <div
        class="p-4 border-b border-slate-200 flex justify-between items-center"
      >
        <h2 class="font-semibold text-slate-800">Recent Transactions</h2>
        <a-button type="link" @click="router.push('/finance')">
          View All <RightOutlined />
        </a-button>
      </div>
      <a-list
        :data-source="finance.transactions.slice(0, 5)"
        :loading="finance.loading"
        :locale="{ emptyText: 'No transactions yet' }"
      >
        <template #renderItem="{ item }">
          <a-list-item class="px-4!">
            <a-list-item-meta>
              <template #avatar>
                <div
                  class="w-10 h-10 rounded-full flex items-center justify-center text-lg font-semibold"
                  :class="
                    item.type === 'income'
                      ? 'bg-green-100 text-green-600'
                      : 'bg-red-100 text-red-600'
                  "
                >
                  <ArrowUpOutlined v-if="item.type === 'income'" />
                  <ArrowDownOutlined v-else />
                </div>
              </template>
              <template #title>{{
                item.description || "Transaction"
              }}</template>
              <template #description
                >{{ item.category?.name || "Uncategorized" }} •
                {{ item.transaction_date }}</template
              >
            </a-list-item-meta>
            <template #actions>
              <span
                class="font-semibold"
                :class="
                  item.type === 'income' ? 'text-green-600' : 'text-red-600'
                "
              >
                {{ item.type === "income" ? "+" : "-" }}${{
                  Number(item.amount).toFixed(2)
                }}
              </span>
            </template>
          </a-list-item>
        </template>
      </a-list>
    </div>
  </div>
</template>
