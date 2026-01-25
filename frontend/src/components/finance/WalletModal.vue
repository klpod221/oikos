<!--
  WalletModal.vue

  Modal form for creating or editing a wallet.
  Props:
    - open: Modal visibility
    - wallet: Wallet object to edit (null for create)
    - loading: Loading state
  Model:
    - form: Form data object
  Events:
    - update:open: Sync visibility
    - submit: Emitted on save
-->
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
    :title="wallet ? 'Chỉnh sửa ví' : 'Thêm ví mới'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Tên ví" required>
        <a-input v-model:value="form.name" placeholder="Ví của tôi" />
      </a-form-item>
      <div class="grid grid-cols-2 gap-2">
        <a-form-item :label="wallet ? 'Số dư hiện tại' : 'Số dư ban đầu'">
          <a-input-number
            v-model:value="form.initial_balance"
            :min="0"
            class="w-full!"
            :formatter="
              (value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
            "
            :parser="(value) => value.replace(/\$\s?|(,*)/g, '')"
          />
        </a-form-item>
        <a-form-item label="Tiền tệ">
          <a-select v-model:value="form.currency">
            <a-select-option value="VND">VND</a-select-option>
            <a-select-option value="USD">USD</a-select-option>
            <a-select-option value="EUR">EUR</a-select-option>
          </a-select>
        </a-form-item>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <a-form-item label="Biểu tượng">
          <a-select v-model:value="form.icon">
            <a-select-option value="💰">💰 Tiền</a-select-option>
            <a-select-option value="💳">💳 Thẻ</a-select-option>
            <a-select-option value="🏦">🏦 Ngân hàng</a-select-option>
            <a-select-option value="📱">📱 Ví điện tử</a-select-option>
            <a-select-option value="💵">💵 Tiền mặt</a-select-option>
            <a-select-option value="🪙">🪙 Tiết kiệm</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="Màu sắc">
          <a-input v-model:value="form.color" type="color" class="h-8!" />
        </a-form-item>
      </div>
      <a-form-item label="Mô tả">
        <a-textarea
          v-model:value="form.description"
          placeholder="Mô tả tùy chọn"
          :rows="2"
        />
      </a-form-item>
      <a-form-item>
        <a-checkbox v-model:checked="form.is_default"
          >Đặt làm ví mặc định</a-checkbox
        >
      </a-form-item>
    </a-form>
  </a-modal>
</template>
