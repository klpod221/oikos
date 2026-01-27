<!--
  NutritionLogModal.vue

  Modal for logging food intake (recipe or ingredients).
  Props:
    - open: Modal visibility
    - loading: Loading state
  Model:
    - form: Form data
  Events:
    - update:open: Sync visibility
    - submit: Emitted on save
-->
<script setup>
import { computed, watch } from "vue";
import { useNutritionStore } from "../../stores/nutrition";

const props = defineProps({
  open: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(["update:open", "submit"]);

const nutrition = useNutritionStore();

const form = defineModel("form", {
  type: Object,
  default: () => ({
    log_type: "recipe",
    recipe_id: null,
    ingredient_id: null,
    quantity: 1,
    unit: "serving",
    meal_type: "lunch",
    logged_at: new Date().toISOString().slice(0, 16),
  }),
});

const mealTypes = [
  { value: "breakfast", label: "Bữa sáng" },
  { value: "lunch", label: "Bữa trưa" },
  { value: "dinner", label: "Bữa tối" },
  { value: "snack", label: "Snack" },
];

const recipeOptions = computed(() => {
  return nutrition.allRecipes.map((r) => ({
    value: r.id,
    label: r.name,
  }));
});

const ingredientOptions = computed(() => {
  return nutrition.ingredients.map((i) => ({
    value: i.id,
    label: i.name,
    unit: i.unit,
  }));
});

const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

watch(
  () => props.open,
  async (val) => {
    if (val) {
      await nutrition.fetchAllRecipes();
      await nutrition.fetchIngredients();
    }
  },
);

const handleOk = () => {
  emit("submit", form.value);
};
</script>

<template>
  <a-modal
    :open="open"
    title="Ghi nhận dinh dưỡng"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
    width="500px"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Loại">
        <a-radio-group v-model:value="form.log_type" button-style="solid">
          <a-radio-button value="recipe">Công thức</a-radio-button>
          <a-radio-button value="ingredient">Nguyên liệu</a-radio-button>
        </a-radio-group>
      </a-form-item>

      <a-form-item v-if="form.log_type === 'recipe'" label="Công thức" required>
        <a-select
          v-model:value="form.recipe_id"
          show-search
          placeholder="Chọn công thức"
          :options="recipeOptions"
          :filter-option="filterOption"
          class="w-full"
        />
      </a-form-item>

      <a-form-item v-else label="Nguyên liệu" required>
        <a-select
          v-model:value="form.ingredient_id"
          show-search
          placeholder="Chọn nguyên liệu"
          :options="ingredientOptions"
          :filter-option="filterOption"
          class="w-full"
        />
      </a-form-item>

      <a-row :gutter="16">
        <a-col :span="12">
          <a-form-item label="Số lượng">
            <a-input-number
              v-model:value="form.quantity"
              :min="0.1"
              :step="0.5"
              class="w-full"
            />
          </a-form-item>
        </a-col>
        <a-col :span="12">
          <a-form-item label="Bữa ăn">
            <a-select
              v-model:value="form.meal_type"
              :options="mealTypes"
              class="w-full"
            />
          </a-form-item>
        </a-col>
      </a-row>

      <a-form-item label="Thời gian">
        <a-input v-model:value="form.logged_at" type="datetime-local" />
      </a-form-item>
    </a-form>
  </a-modal>
</template>
