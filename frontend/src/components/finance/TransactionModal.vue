<!--
  TransactionModal.vue

  Modal form for creating or editing a transaction.
  Props:
    - open: Modal visibility
    - loading: Loading state
    - wallets: Array of available wallets
    - categories: Array of available categories
  Model:
    - form: Form data object
  Events:
    - update:open: Sync visibility
    - submit: Emitted on save
-->
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
    :title="form.id ? 'Chỉnh sửa giao dịch' : 'Thêm giao dịch'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Loại">
        <a-radio-group v-model:value="form.type" button-style="solid">
          <a-radio-button value="expense">Chi tiêu</a-radio-button>
          <a-radio-button value="income">Thu nhập</a-radio-button>
        </a-radio-group>
      </a-form-item>
      <a-form-item label="Số tiền" required>
        <a-input-number
          v-model:value="form.amount"
          :min="0"
          class="w-full!"
          :formatter="
            (value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
          "
          :parser="(value) => value.replace(/\$\s?|(,*)/g, '')"
        />
      </a-form-item>
      <a-form-item label="Ví" required>
        <a-select v-model:value="form.wallet_id" placeholder="Chọn ví">
          <a-select-option v-for="w in wallets" :key="w.id" :value="w.id">
            {{ w.name }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item label="Danh mục" required>
        <a-select
          v-model:value="form.category_id"
          placeholder="Chọn danh mục"
          allow-clear
        >
          <a-select-option v-for="c in categories" :key="c.id" :value="c.id">
            {{ c.icon }} {{ c.name }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item label="Mô tả">
        <a-input
          v-model:value="form.description"
          placeholder="Nhập mô tả cho giao dịch"
        />
      </a-form-item>
      <a-form-item label="Ngày">
        <a-input v-model:value="form.transaction_date" type="date" />
      </a-form-item>
    </a-form>
  </a-modal>
</template>
