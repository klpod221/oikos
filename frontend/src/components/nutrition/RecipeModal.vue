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
defineProps({
  open: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  recipe: { type: Object, default: null },
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
  }),
});

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
    width="600px"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
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
          :rows="2"
        />
      </a-form-item>
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
      <a-form-item label="Hướng dẫn">
        <a-textarea
          v-model:value="form.instructions"
          placeholder="Hướng dẫn từng bước..."
          :rows="4"
        />
      </a-form-item>
      <a-divider>Thông tin dinh dưỡng (mỗi khẩu phần)</a-divider>
      <div class="grid grid-cols-4 gap-2">
        <a-form-item label="Calo">
          <a-input-number
            v-model:value="form.calories"
            :min="0"
            class="w-full!"
          />
        </a-form-item>
        <a-form-item label="Đạm (g)">
          <a-input-number
            v-model:value="form.protein"
            :min="0"
            class="w-full!"
          />
        </a-form-item>
        <a-form-item label="Tinh bột (g)">
          <a-input-number v-model:value="form.carbs" :min="0" class="w-full!" />
        </a-form-item>
        <a-form-item label="Béo (g)">
          <a-input-number v-model:value="form.fat" :min="0" class="w-full!" />
        </a-form-item>
      </div>
    </a-form>
  </a-modal>
</template>
