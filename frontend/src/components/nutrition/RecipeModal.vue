<!--
  RecipeModal.vue

  Modal form for creating or editing a recipe.
  Props:
    - open: Modal visibility
    - loading: Loading state
    - recipe: Recipe object (null for create)
  Model:
    - form: Form data object
  Events:
    - update:open: Sync visibility
    - submit: Emitted on save
-->
<script setup>
import { computed, watch } from "vue";
import { DeleteOutlined, PlusOutlined } from "@ant-design/icons-vue";

const props = defineProps({
  open: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  recipe: { type: Object, default: null },
  ingredients: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:open", "submit"]);

const form = defineModel("form", {
  type: Object,
  default: () => ({
    name: "",
    description: "",
    servings: 1,
    prep_time: 0,
    cooking_time: 0,
    instructions: "",
    calories: 0,
    protein: 0,
    carbs: 0,
    fat: 0,
    ingredients: [],
  }),
});

// Helper validation (ensure ingredients array exists)
watch(() => props.open, (val) => {
    if (val && !form.value.ingredients) {
        form.value.ingredients = [];
    }
    // If editing, map pivot data if available
    if (val && props.recipe && props.recipe.ingredients) {
        form.value.ingredients = props.recipe.ingredients.map(i => ({
            id: i.id,
            quantity: Number(i.pivot?.quantity || 0),
            unit: i.pivot?.unit || i.unit,
        }));
    }
}, { immediate: true });


const ingredientOptions = computed(() => {
  return props.ingredients.map(i => ({ value: i.id, label: i.name, unit: i.unit }));
});

const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const handleIngredientChange = (index, id) => {
    const ing = props.ingredients.find(i => i.id === id);
    if (ing) {
        form.value.ingredients[index].unit = ing.unit;
    }
};

const addIngredient = () => {
    if (!form.value.ingredients) form.value.ingredients = [];
    form.value.ingredients.push({ id: null, quantity: 1, unit: '' });
};

const removeIngredient = (index) => {
    form.value.ingredients.splice(index, 1);
};

const handleOk = () => {
  emit("submit", form.value);
};
</script>

<template>
  <a-modal
    :open="open"
    :title="recipe ? 'Chỉnh sửa công thức' : 'Tạo công thức mới'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
    width="1000px"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column: General Info -->
        <div>
          <a-form-item label="Tên công thức" required>
            <a-input
              v-model:value="form.name"
              placeholder="Ví dụ: Salad ức gà nướng"
            />
          </a-form-item>
          <a-form-item label="Mô tả">
            <a-textarea
              v-model:value="form.description"
              placeholder="Mô tả ngắn gọn"
              :rows="3"
            />
          </a-form-item>
          <a-form-item label="Hướng dẫn">
            <a-textarea
              v-model:value="form.instructions"
              placeholder="Hướng dẫn từng bước..."
              :rows="12"
            />
          </a-form-item>
        </div>

        <!-- Right Column: Details & Ingredients -->
        <div>
           <div class="grid grid-cols-3 gap-2">
            <a-form-item label="Khẩu phần">
              <a-input-number
                v-model:value="form.servings"
                :min="1"
                class="w-full!"
              />
            </a-form-item>
            <a-form-item label="Chuẩn bị (phút)">
              <a-input-number
                v-model:value="form.prep_time"
                :min="0"
                class="w-full!"
              />
            </a-form-item>
            <a-form-item label="Nấu (phút)">
              <a-input-number
                v-model:value="form.cooking_time"
                :min="0"
                class="w-full!"
              />
            </a-form-item>
          </div>

           <a-divider style="margin: 12px 0">Dinh dưỡng (mỗi khẩu phần)</a-divider>
           <div class="grid grid-cols-4 gap-2">
            <a-form-item label="Calo">
              <a-input-number v-model:value="form.calories" :min="0" class="w-full!" />
            </a-form-item>
            <a-form-item label="Đạm (g)">
              <a-input-number v-model:value="form.protein" :min="0" class="w-full!" />
            </a-form-item>
            <a-form-item label="Carb (g)">
              <a-input-number v-model:value="form.carbs" :min="0" class="w-full!" />
            </a-form-item>
            <a-form-item label="Béo (g)">
              <a-input-number v-model:value="form.fat" :min="0" class="w-full!" />
            </a-form-item>
          </div>

          <a-divider style="margin: 12px 0">Nguyên liệu</a-divider>
          <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
             <div v-for="(item, index) in form.ingredients" :key="index" class="flex gap-2 mb-2 items-start">
              <a-select 
                  v-model:value="item.id" 
                  show-search
                  placeholder="Chọn nguyên liệu" 
                  :options="ingredientOptions"
                  :filter-option="filterOption"
                  class="flex-1"
                  @change="handleIngredientChange(index, $event)"
              >
              </a-select>
              <a-input-number 
                  v-model:value="item.quantity" 
                  placeholder="SL" 
                  :min="0" 
                  class="w-20!" 
              />
              <span class="leading-[32px] text-slate-500 w-8 text-xs">{{ item.unit || '...' }}</span>
              <a-button danger size="small" type="text" @click="removeIngredient(index)">
                  <template #icon><DeleteOutlined /></template>
              </a-button>
            </div>
            <a-button type="dashed" block @click="addIngredient" class="mt-2">
              <template #icon><PlusOutlined /></template>
              Thêm nguyên liệu
            </a-button>
          </div>
        </div>
      </div>
    </a-form>
  </a-modal>
</template>
