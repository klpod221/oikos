<!--
  CategoryModal.vue

  Modal for creating/editing user categories.
-->
<script setup>
import { computed } from "vue";

const props = defineProps({
  open: Boolean,
  form: Object,
  category: Object, // The category being edited (or null for create)
  loading: Boolean,
});

const emit = defineEmits(["update:open", "update:form", "submit"]);

const isOpen = computed({
  get: () => props.open,
  set: (val) => emit("update:open", val),
});

const formData = computed({
  get: () => props.form,
  set: (val) => emit("update:form", val),
});

const handleOk = () => {
  emit("submit", formData.value);
};

const handleCancel = () => {
  isOpen.value = false;
};
</script>

<template>
  <a-modal
    v-model:open="isOpen"
    :title="category ? 'Sửa danh mục' : 'Tạo danh mục mới'"
    @ok="handleOk"
    @cancel="handleCancel"
    :confirm-loading="loading"
  >
    <a-form layout="vertical" class="mt-4">
      <a-form-item label="Tên danh mục" required>
        <a-input
          v-model:value="formData.name"
          placeholder="Nhập tên danh mục"
        />
      </a-form-item>

      <a-form-item label="Loại" required>
        <a-select v-model:value="formData.type" class="w-full">
          <a-select-option value="income">Thu nhập</a-select-option>
          <a-select-option value="expense">Chi tiêu</a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="Icon (emoji)">
        <a-input
          v-model:value="formData.icon"
          placeholder="Ví dụ: 🍔, 💰, 🏠"
          maxlength="2"
        />
      </a-form-item>

      <a-form-item label="Màu sắc">
        <div class="flex items-center gap-2">
          <input
            v-model="formData.color"
            type="color"
            class="w-12 h-10 border border-slate-300 rounded cursor-pointer"
          />
          <a-input v-model:value="formData.color" placeholder="#3b82f6" />
        </div>
      </a-form-item>
    </a-form>
  </a-modal>
</template>
