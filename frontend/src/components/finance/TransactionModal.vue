<script setup>
defineProps({
  open: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  wallets: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:open", "submit"]);

const form = defineModel("form", {
  type: Object,
  default: () => ({
    wallet_id: null,
    category_id: null,
    type: "expense",
    amount: 0,
    description: "",
    transaction_date: new Date().toISOString().split("T")[0],
  }),
});

const handleOk = () => {
  emit("submit", form.value);
};
</script>

<template>
  <a-modal
    :open="open"
    :title="form.id ? 'Edit Transaction' : 'New Transaction'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Type">
        <a-radio-group v-model:value="form.type" button-style="solid">
          <a-radio-button value="expense">Expense</a-radio-button>
          <a-radio-button value="income">Income</a-radio-button>
        </a-radio-group>
      </a-form-item>
      <a-form-item label="Amount" required>
        <a-input-number v-model:value="form.amount" :min="0" class="w-full!" />
      </a-form-item>
      <a-form-item label="Wallet" required>
        <a-select v-model:value="form.wallet_id" placeholder="Select wallet">
          <a-select-option v-for="w in wallets" :key="w.id" :value="w.id">
            {{ w.name }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item label="Category">
        <a-select
          v-model:value="form.category_id"
          placeholder="Select category"
          allow-clear
        >
          <a-select-option v-for="c in categories" :key="c.id" :value="c.id">
            {{ c.icon }} {{ c.name }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item label="Description">
        <a-input
          v-model:value="form.description"
          placeholder="What's this for?"
        />
      </a-form-item>
      <a-form-item label="Date">
        <a-input v-model:value="form.transaction_date" type="date" />
      </a-form-item>
    </a-form>
  </a-modal>
</template>
