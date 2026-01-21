<script setup>
const props = defineProps({
  open: { type: Boolean, default: false },
  wallet: { type: Object, default: null },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(["update:open", "submit"]);

const form = defineModel("form", {
  type: Object,
  default: () => ({
    name: "",
    initial_balance: 0,
    currency: "VND",
    description: "",
    is_default: false,
    icon: "💰",
    color: "#3b82f6",
  }),
});

const handleOk = () => {
  emit("submit", form.value);
};
</script>

<template>
  <a-modal
    :open="open"
    :title="wallet ? 'Edit Wallet' : 'New Wallet'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Name" required>
        <a-input v-model:value="form.name" placeholder="My Wallet" />
      </a-form-item>
      <div class="grid grid-cols-2 gap-4">
        <a-form-item :label="wallet ? 'Current Balance' : 'Initial Balance'">
          <a-input-number
            v-model:value="form.initial_balance"
            :min="0"
            class="w-full!"
          />
        </a-form-item>
        <a-form-item label="Currency">
          <a-select v-model:value="form.currency">
            <a-select-option value="VND">VND</a-select-option>
            <a-select-option value="USD">USD</a-select-option>
            <a-select-option value="EUR">EUR</a-select-option>
          </a-select>
        </a-form-item>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <a-form-item label="Icon">
          <a-select v-model:value="form.icon">
            <a-select-option value="💰">💰 Money</a-select-option>
            <a-select-option value="💳">💳 Card</a-select-option>
            <a-select-option value="🏦">🏦 Bank</a-select-option>
            <a-select-option value="📱">📱 E-Wallet</a-select-option>
            <a-select-option value="💵">💵 Cash</a-select-option>
            <a-select-option value="🪙">🪙 Savings</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="Color">
          <a-input v-model:value="form.color" type="color" class="h-8!" />
        </a-form-item>
      </div>
      <a-form-item label="Description">
        <a-textarea
          v-model:value="form.description"
          placeholder="Optional description"
          :rows="2"
        />
      </a-form-item>
      <a-form-item>
        <a-checkbox v-model:checked="form.is_default"
          >Set as default wallet</a-checkbox
        >
      </a-form-item>
    </a-form>
  </a-modal>
</template>
