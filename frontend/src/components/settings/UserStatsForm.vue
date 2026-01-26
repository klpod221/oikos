<!--
  UserStatsForm.vue
  
  Form for updating user physical stats (weight, height, age, gender, activity level).
  Used for BMR/TDEE calculations.
-->
<script setup>
import { ref, onMounted } from "vue";
import { useIntegrationStore } from "../../stores/integration";

const integration = useIntegrationStore();

const formState = ref({
  weight: null,
  height: null,
  age: null,
  gender: "male",
  activity_level: "sedentary",
});

const activityLevels = [
  { value: "sedentary", label: "Ít vận động (văn phòng)" },
  { value: "lightly_active", label: "Vận động nhẹ (1-3 buổi/tuần)" },
  { value: "moderately_active", label: "Vận động vừa (3-5 buổi/tuần)" },
  { value: "very_active", label: "Vận động nhiều (6-7 buổi/tuần)" },
  { value: "extra_active", label: "Vận động rất nhiều (2 lần/ngày)" },
];

onMounted(async () => {
  await integration.fetchUserStats();
  if (integration.userStats) {
    formState.value = {
      weight: integration.userStats.weight,
      height: integration.userStats.height,
      age: integration.userStats.age,
      gender: integration.userStats.gender || "male",
      activity_level: integration.userStats.activity_level || "sedentary",
    };
  }
});

async function onFinish() {
  await integration.saveUserStats(formState.value);
}
</script>

<template>
  <div class="max-w-xl">
    <a-form layout="vertical" :model="formState" @finish="onFinish">
      <a-row :gutter="16">
        <a-col :xs="24" :md="12">
          <a-form-item
            label="Cân nặng (kg)"
            name="weight"
            :rules="[{ required: true, message: 'Vui lòng nhập cân nặng!' }]"
          >
            <a-input-number
              v-model:value="formState.weight"
              :min="1"
              :max="300"
              size="large"
              class="w-full"
              placeholder="65"
            />
          </a-form-item>
        </a-col>
        <a-col :xs="24" :md="12">
          <a-form-item
            label="Chiều cao (cm)"
            name="height"
            :rules="[{ required: true, message: 'Vui lòng nhập chiều cao!' }]"
          >
            <a-input-number
              v-model:value="formState.height"
              :min="1"
              :max="250"
              size="large"
              class="w-full"
              placeholder="170"
            />
          </a-form-item>
        </a-col>
      </a-row>

      <a-row :gutter="16">
        <a-col :xs="24" :md="12">
          <a-form-item
            label="Tuổi"
            name="age"
            :rules="[{ required: true, message: 'Vui lòng nhập tuổi!' }]"
          >
            <a-input-number
              v-model:value="formState.age"
              :min="1"
              :max="150"
              size="large"
              class="w-full"
              placeholder="25"
            />
          </a-form-item>
        </a-col>
        <a-col :xs="24" :md="12">
          <a-form-item
            label="Giới tính"
            name="gender"
            :rules="[{ required: true, message: 'Vui lòng chọn giới tính!' }]"
          >
            <a-select
              v-model:value="formState.gender"
              size="large"
              class="w-full"
            >
              <a-select-option value="male">Nam</a-select-option>
              <a-select-option value="female">Nữ</a-select-option>
              <a-select-option value="other">Khác</a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
      </a-row>

      <a-form-item
        label="Mức độ vận động"
        name="activity_level"
        :rules="[{ required: true, message: 'Vui lòng chọn mức độ vận động!' }]"
      >
        <a-select
          v-model:value="formState.activity_level"
          :options="activityLevels"
          size="large"
          class="w-full"
        />
      </a-form-item>

      <a-form-item>
        <a-button
          type="primary"
          html-type="submit"
          :loading="integration.loading"
          size="large"
        >
          Lưu thay đổi
        </a-button>
      </a-form-item>
    </a-form>
  </div>
</template>
