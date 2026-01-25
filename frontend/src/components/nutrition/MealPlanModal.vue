<!--
  MealPlanModal.vue
  
  Modal for creating or editing a meal plan entry.
-->
<script setup>
import { ref, watch, computed } from "vue";
import { useNutritionStore } from "../../stores/nutrition";
import { storeToRefs } from "pinia";

const props = defineProps({
  open: Boolean,
  form: Object, // { date, meal_type, recipe_id, description, status }
  mode: { type: String, default: "create" }, // 'create' | 'edit'
});

const emit = defineEmits(["update:open", "update:form", "submit", "delete"]);

const nutrition = useNutritionStore();
const { allRecipes } = storeToRefs(nutrition);

const localForm = ref({ ...props.form });
const useRecipe = ref(true); // Toggle between choosing recipe or custom description

// Fetch all recipes for dropdown
if (nutrition.allRecipes.length === 0) {
    nutrition.fetchAllRecipes();
}

// Sync prop form to local
watch(
  () => props.form,
  (newVal) => {
    localForm.value = { ...newVal };
    // Determine input mode
    if (newVal.recipe_id) {
      useRecipe.value = true;
    } else if (newVal.description && !newVal.recipe_id) {
      useRecipe.value = false;
    } else {
      useRecipe.value = true; // Default
    }
  },
  { deep: true }
);

// Sync local to prop (optional, but good for parent)
watch(
  localForm,
  (newVal) => {
    emit("update:form", newVal);
  },
  { deep: true }
);

const handleCancel = () => {
  emit("update:open", false);
};

const handleOk = () => {
  // Validate basic
  if (!localForm.value.date || !localForm.value.meal_type) {
    return;
  }
  
  // Format data
  const payload = {
    date: localForm.value.date,
    meal_type: localForm.value.meal_type,
    status: localForm.value.status || "planned",
  };

  if (useRecipe.value) {
    payload.recipe_id = localForm.value.recipe_id;
    payload.description = null; // Clear description if using recipe
  } else {
    payload.recipe_id = null;
    payload.description = localForm.value.description;
  }

  emit("submit", payload);
};

const handleDelete = () => {
  emit("delete");
};

// Filter options for select
const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const recipeOptions = computed(() => {
  return allRecipes.value.map((r) => ({
    value: r.id,
    label: r.name,
  }));
});
</script>

<template>
  <a-modal
    :open="open"
    :title="mode === 'create' ? 'Thêm bữa ăn' : 'Chi tiết bữa ăn'"
    @ok="handleOk"
    @cancel="handleCancel"
    ok-text="Lưu"
    cancel-text="Hủy"
  >
    <a-form layout="vertical">
      <!-- Date & Meal Type -->
      <div class="grid grid-cols-2 gap-4">
        <a-form-item label="Ngày" required>
          <a-date-picker
            v-model:value="localForm.date"
            value-format="YYYY-MM-DD"
            class="w-full"
            :allow-clear="false"
          />
        </a-form-item>
        <a-form-item label="Bữa ăn" required>
          <a-select v-model:value="localForm.meal_type">
            <a-select-option value="breakfast">Bữa sáng</a-select-option>
            <a-select-option value="lunch">Bữa trưa</a-select-option>
            <a-select-option value="dinner">Bữa tối</a-select-option>
            <a-select-option value="snack">Bữa phụ</a-select-option>
          </a-select>
        </a-form-item>
      </div>

      <!-- Toggle Recipe/Custom -->
      <a-form-item label="Chọn món ăn">
        <a-radio-group v-model:value="useRecipe" class="mb-2">
          <a-radio :value="true">Chọn công thức</a-radio>
          <a-radio :value="false">Nhập thủ công</a-radio>
        </a-radio-group>
        
        <!-- Recipe Select -->
        <a-select
          v-if="useRecipe"
          v-model:value="localForm.recipe_id"
          show-search
          placeholder="Tìm công thức..."
          :options="recipeOptions"
          :filter-option="filterOption"
          class="w-full"
        />
        
        <!-- Custom Description -->
        <a-input
          v-else
          v-model:value="localForm.description"
          placeholder="Ví dụ: Bún bò huế, Phở gà..."
        />
      </a-form-item>

      <a-form-item label="Trạng thái">
         <a-select v-model:value="localForm.status">
            <a-select-option value="planned">Dự kiến</a-select-option>
            <a-select-option value="completed">Đã ăn</a-select-option>
            <a-select-option value="skipped">Bỏ qua</a-select-option>
          </a-select>
      </a-form-item>
    </a-form>

    <!-- Footer override for delete button -->
    <template #footer>
        <div class="flex justify-between items-center">
            <div>
                 <a-button 
                    v-if="mode === 'edit'" 
                    danger 
                    type="text" 
                    @click="handleDelete"
                >
                    Xóa
                </a-button>
            </div>
            <div class="flex gap-2">
                <a-button @click="handleCancel">Hủy</a-button>
                <a-button type="primary" @click="handleOk">Lưu</a-button>
            </div>
        </div>
    </template>
  </a-modal>
</template>
