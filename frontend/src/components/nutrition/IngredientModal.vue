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
    title="Add Ingredient"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <div class="grid grid-cols-2 gap-4">
        <a-form-item label="Name" required>
          <a-input
            v-model:value="form.name"
            placeholder="e.g., Chicken Breast"
          />
        </a-form-item>
        <a-form-item label="Unit">
          <a-select v-model:value="form.unit">
            <a-select-option value="g">gram (g)</a-select-option>
            <a-select-option value="ml">milliliter (ml)</a-select-option>
            <a-select-option value="pc">piece (pc)</a-select-option>
            <a-select-option value="cup">cup</a-select-option>
            <a-select-option value="tbsp">tablespoon</a-select-option>
          </a-select>
        </a-form-item>
      </div>
      <div class="grid grid-cols-3 gap-4">
        <a-form-item label="Calories">
          <a-input-number
            v-model:value="form.calories"
            :min="0"
            class="w-full!"
            addon-after="kcal"
          />
        </a-form-item>
        <a-form-item label="Protein">
          <a-input-number
            v-model:value="form.protein"
            :min="0"
            class="w-full!"
            addon-after="g"
          />
        </a-form-item>
        <a-form-item label="Carbs">
          <a-input-number
            v-model:value="form.carbs"
            :min="0"
            class="w-full!"
            addon-after="g"
          />
        </a-form-item>
        <a-form-item label="Fat">
          <a-input-number
            v-model:value="form.fat"
            :min="0"
            class="w-full!"
            addon-after="g"
          />
        </a-form-item>
        <a-form-item label="Fiber">
          <a-input-number
            v-model:value="form.fiber"
            :min="0"
            class="w-full!"
            addon-after="g"
          />
        </a-form-item>
        <a-form-item label="Sugar">
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
