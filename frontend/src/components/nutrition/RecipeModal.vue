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
    :title="recipe ? 'Edit Recipe' : 'New Recipe'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
    width="600px"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Recipe Name" required>
        <a-input
          v-model:value="form.name"
          placeholder="e.g., Grilled Chicken Salad"
        />
      </a-form-item>
      <a-form-item label="Description">
        <a-textarea
          v-model:value="form.description"
          placeholder="Brief description"
          :rows="2"
        />
      </a-form-item>
      <div class="grid grid-cols-3 gap-4">
        <a-form-item label="Servings">
          <a-input-number
            v-model:value="form.servings"
            :min="1"
            class="w-full!"
          />
        </a-form-item>
        <a-form-item label="Prep Time (mins)">
          <a-input-number
            v-model:value="form.prep_time"
            :min="0"
            class="w-full!"
          />
        </a-form-item>
        <a-form-item label="Cook Time (mins)">
          <a-input-number
            v-model:value="form.cooking_time"
            :min="0"
            class="w-full!"
          />
        </a-form-item>
      </div>
      <a-form-item label="Instructions">
        <a-textarea
          v-model:value="form.instructions"
          placeholder="Step by step instructions..."
          :rows="4"
        />
      </a-form-item>
      <a-divider>Nutrition Info (per serving)</a-divider>
      <div class="grid grid-cols-4 gap-4">
        <a-form-item label="Calories">
          <a-input-number
            v-model:value="form.calories"
            :min="0"
            class="w-full!"
          />
        </a-form-item>
        <a-form-item label="Protein (g)">
          <a-input-number
            v-model:value="form.protein"
            :min="0"
            class="w-full!"
          />
        </a-form-item>
        <a-form-item label="Carbs (g)">
          <a-input-number v-model:value="form.carbs" :min="0" class="w-full!" />
        </a-form-item>
        <a-form-item label="Fat (g)">
          <a-input-number v-model:value="form.fat" :min="0" class="w-full!" />
        </a-form-item>
      </div>
    </a-form>
  </a-modal>
</template>
