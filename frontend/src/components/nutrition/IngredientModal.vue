<!--
  IngredientModal.vue

  Modal form for creating or editing an ingredient.
  Props:
    - open: Modal visibility
    - loading: Loading state
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
});

const emit = defineEmits(["update:open", "submit"]);

const form = defineModel("form", {
  type: Object,
  default: () => ({
    name: "",
    unit: "g",
    calories: 0,
    protein: 0,
    carbs: 0,
    fat: 0,
    fiber: 0,
    sugar: 0,
  }),
});

const handleOk = () => {
  emit("submit", form.value);
};
</script>

<template>
  <a-modal
    :open="open"
    title="Thêm nguyên liệu"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <div class="grid grid-cols-2 gap-2">
        <a-form-item label="Tên nguyên liệu" required>
          <a-input v-model:value="form.name" placeholder="Ví dụ: Ức gà" />
        </a-form-item>
        <a-form-item label="Đơn vị">
          <a-select v-model:value="form.unit">
            <a-select-option value="g">gram (g)</a-select-option>
            <a-select-option value="ml">mililit (ml)</a-select-option>
            <a-select-option value="pc">cái/chiếc (pc)</a-select-option>
            <a-select-option value="cup">cốc (cup)</a-select-option>
            <a-select-option value="tbsp">thìa canh (tbsp)</a-select-option>
          </a-select>
        </a-form-item>
      </div>
      <div class="grid grid-cols-3 gap-2">
        <a-form-item label="Calo">
          <a-input-number
            v-model:value="form.calories"
            :min="0"
            class="w-full!"
            addon-after="kcal"
          />
        </a-form-item>
        <a-form-item label="Đạm">
          <a-input-number
            v-model:value="form.protein"
            :min="0"
            class="w-full!"
            addon-after="g"
          />
        </a-form-item>
        <a-form-item label="Tinh bột">
          <a-input-number
            v-model:value="form.carbs"
            :min="0"
            class="w-full!"
            addon-after="g"
          />
        </a-form-item>
        <a-form-item label="Chất béo">
          <a-input-number
            v-model:value="form.fat"
            :min="0"
            class="w-full!"
            addon-after="g"
          />
        </a-form-item>
        <a-form-item label="Chất xơ">
          <a-input-number
            v-model:value="form.fiber"
            :min="0"
            class="w-full!"
            addon-after="g"
          />
        </a-form-item>
        <a-form-item label="Đường">
          <a-input-number
            v-model:value="form.sugar"
            :min="0"
            class="w-full!"
            addon-after="g"
          />
        </a-form-item>
      </div>
    </a-form>
  </a-modal>
</template>
