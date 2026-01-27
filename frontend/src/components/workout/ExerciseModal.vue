<!--
  ExerciseModal.vue

  Modal for creating and editing exercises.
  Props:
    - open: Modal visibility
    - loading: Loading state
    - exercise: Exercise object to edit (null for create)
  Model:
    - form: Form data
  Events:
    - update:open: Sync visibility
    - submit: Emitted on save
-->
<script setup>
import { computed } from "vue";

const props = defineProps({
  open: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  exercise: { type: Object, default: null },
});

const emit = defineEmits(["update:open", "submit"]);

const form = defineModel("form", {
  type: Object,
  default: () => ({
    name: "",
    description: "",
    muscle_group: "",
    type: "reps", // reps or time
    calories_per_unit: 0,
    video_url: "",
  }),
});

const isEditing = computed(() => !!props.exercise);

const muscleGroups = [
  "Ngực",
  "Lưng",
  "Chân",
  "Vai",
  "Tay trước",
  "Tay sau",
  "Bụng",
  "Cardio",
  "Toàn thân",
];

const handleOk = () => {
  emit("submit", form.value);
};

const handleCancel = () => {
  emit("update:open", false);
};
</script>

<template>
  <a-modal
    :open="open"
    :title="isEditing ? 'Cập nhật bài tập' : 'Thêm bài tập mới'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    @cancel="handleCancel"
    :confirm-loading="loading"
    width="600px"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Tên bài tập" required>
        <a-input v-model:value="form.name" placeholder="Ví dụ: Hít đất" />
      </a-form-item>

      <a-row :gutter="16">
        <a-col :span="12">
          <a-form-item label="Nhóm cơ" required>
            <a-select
              v-model:value="form.muscle_group"
              placeholder="Chọn nhóm cơ"
              class="w-full"
            >
              <a-select-option
                v-for="group in muscleGroups"
                :key="group"
                :value="group"
              >
                {{ group }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
        <a-col :span="12">
          <a-form-item label="Loại bài tập" required>
            <a-radio-group v-model:value="form.type" button-style="solid">
              <a-radio-button value="reps">Số lần (Reps)</a-radio-button>
              <a-radio-button value="time">Thời gian</a-radio-button>
            </a-radio-group>
          </a-form-item>
        </a-col>
      </a-row>

      <a-row :gutter="16">
        <a-col :span="12">
          <a-form-item label="Calo tiêu thụ (mỗi Rep/Phút)">
            <a-input-number
              v-model:value="form.calories_per_unit"
              :min="0"
              :step="0.1"
              class="w-full"
              addon-after="kcal"
            />
          </a-form-item>
        </a-col>
        <a-col :span="12">
          <a-form-item label="Video hướng dẫn (URL)">
            <a-input
              v-model:value="form.video_url"
              placeholder="https://youtube.com/..."
            />
          </a-form-item>
        </a-col>
      </a-row>

      <a-form-item label="Mô tả">
        <a-textarea
          v-model:value="form.description"
          placeholder="Mô tả cách thực hiện..."
          :rows="3"
        />
      </a-form-item>
    </a-form>
  </a-modal>
</template>
