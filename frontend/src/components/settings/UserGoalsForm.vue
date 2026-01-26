<!--
  UserGoalsForm.vue
  
  Form for setting health and fitness goals (calories, macros, workout targets).
-->
<script setup>
import { ref, onMounted } from "vue";
import { useIntegrationStore } from "../../stores/integration";

const integration = useIntegrationStore();

const formState = ref({
  goal_type: "maintain",
  target_calories: null,
  target_protein: null,
  target_carbs: null,
  target_fat: null,
  weekly_workout_target: null,
});

const goalTypes = [
  { value: "maintain", label: "Duy trì cân nặng" },
  { value: "lose_weight", label: "Giảm cân" },
  { value: "gain_muscle", label: "Tăng cơ" },
  { value: "improve_fitness", label: "Cải thiện sức khỏe" },
];

onMounted(async () => {
  await integration.fetchUserGoals();
  if (integration.userGoals) {
    formState.value = {
      goal_type: integration.userGoals.goal_type || "maintain",
      target_calories: integration.userGoals.target_calories,
      target_protein: integration.userGoals.target_protein,
      target_carbs: integration.userGoals.target_carbs,
      target_fat: integration.userGoals.target_fat,
      weekly_workout_target: integration.userGoals.weekly_workout_target,
    };
  }
});

async function onFinish() {
  await integration.saveUserGoals(formState.value);
}
</script>

<template>
  <div class="max-w-xl">
    <a-form layout="vertical" :model="formState" @finish="onFinish">
      <a-form-item
        label="Loại mục tiêu"
        name="goal_type"
        :rules="[{ required: true, message: 'Vui lòng chọn loại mục tiêu!' }]"
      >
        <a-select
          v-model:value="formState.goal_type"
          :options="goalTypes"
          size="large"
          class="w-full"
        />
      </a-form-item>

      <a-form-item label="Calo mục tiêu (kcal/ngày)" name="target_calories">
        <a-input-number
          v-model:value="formState.target_calories"
          :min="0"
          :max="10000"
          size="large"
          class="w-full"
          placeholder="2000"
        />
      </a-form-item>

      <a-divider>Macro Nutrients (g/ngày)</a-divider>

      <a-row :gutter="16">
        <a-col :xs="24" :md="8">
          <a-form-item label="Protein" name="target_protein">
            <a-input-number
              v-model:value="formState.target_protein"
              :min="0"
              size="large"
              class="w-full"
              placeholder="150"
            />
          </a-form-item>
        </a-col>
        <a-col :xs="24" :md="8">
          <a-form-item label="Carbs" name="target_carbs">
            <a-input-number
              v-model:value="formState.target_carbs"
              :min="0"
              size="large"
              class="w-full"
              placeholder="200"
            />
          </a-form-item>
        </a-col>
        <a-col :xs="24" :md="8">
          <a-form-item label="Fat" name="target_fat">
            <a-input-number
              v-model:value="formState.target_fat"
              :min="0"
              size="large"
              class="w-full"
              placeholder="60"
            />
          </a-form-item>
        </a-col>
      </a-row>

      <a-form-item
        label="Mục tiêu tập luyện (buổi/tuần)"
        name="weekly_workout_target"
      >
        <a-input-number
          v-model:value="formState.weekly_workout_target"
          :min="0"
          :max="14"
          size="large"
          class="w-full"
          placeholder="4"
        />
      </a-form-item>

      <a-form-item>
        <a-button
          type="primary"
          html-type="submit"
          :loading="integration.loading"
          size="large"
        >
          Lưu mục tiêu
        </a-button>
      </a-form-item>
    </a-form>
  </div>
</template>
