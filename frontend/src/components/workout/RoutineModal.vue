<!--
  RoutineModal.vue

  Modal form for creating or editing a workout routine.
  Props:
    - open: Modal visibility
    - loading: Loading state
    - routine: Routine object (null for create)
    - exercises: Available exercises list
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
  routine: { type: Object, default: null },
  exercises: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:open", "submit"]);

const form = defineModel("form", {
  type: Object,
  default: () => ({
    name: "",
    description: "",
    estimated_duration: 30,
    exercises: [],
  }),
});

// Initialize form when modal opens
watch(
  () => props.open,
  (val) => {
    if (val && !form.value.exercises) {
      form.value.exercises = [];
    }
    // If editing, map existing exercises
    if (val && props.routine && props.routine.exercises) {
      form.value.exercises = props.routine.exercises.map((e) => ({
        exercise_id: e.id,
        order: e.pivot?.order || 0,
        sets: e.pivot?.sets || 3,
        reps: e.pivot?.reps || 10,
        duration_seconds: e.pivot?.duration_seconds || null,
        rest_seconds: e.pivot?.rest_seconds || 60,
      }));
    }
  },
  { immediate: true },
);

const exerciseOptions = computed(() => {
  return props.exercises.map((e) => ({
    value: e.id,
    label: e.name,
    muscle_group: e.muscle_group,
  }));
});

const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const addExercise = () => {
  if (!form.value.exercises) form.value.exercises = [];
  form.value.exercises.push({
    exercise_id: null,
    order: form.value.exercises.length + 1,
    sets: 3,
    reps: 10,
    duration_seconds: null,
    rest_seconds: 60,
  });
};

const removeExercise = (index) => {
  form.value.exercises.splice(index, 1);
  // Re-order
  form.value.exercises.forEach((e, i) => (e.order = i + 1));
};

const handleOk = () => {
  // Transform exercises to match backend expected format
  const payload = {
    ...form.value,
    exercises: form.value.exercises.map((item) => ({
      exercise_id: item.exercise_id,
      order: item.order,
      // Backend expects target_value (reps or duration in seconds)
      target_value: item.duration_seconds || item.reps || 10,
      rest_time: item.rest_seconds ?? 30,
    })),
  };
  emit("submit", payload);
};
</script>

<template>
  <a-modal
    :open="open"
    :title="routine ? 'Chỉnh sửa routine' : 'Tạo routine mới'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
    width="800px"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-row :gutter="16">
        <a-col :span="16">
          <a-form-item label="Tên routine" required>
            <a-input
              v-model:value="form.name"
              placeholder="Ví dụ: Full Body Workout"
            />
          </a-form-item>
        </a-col>
        <a-col :span="8">
          <a-form-item label="Thời gian (phút)">
            <a-input-number
              v-model:value="form.estimated_duration"
              :min="1"
              class="w-full"
            />
          </a-form-item>
        </a-col>
      </a-row>

      <a-form-item label="Mô tả">
        <a-textarea
          v-model:value="form.description"
          placeholder="Mô tả ngắn gọn về routine này"
          :rows="2"
        />
      </a-form-item>

      <a-divider>Danh sách bài tập</a-divider>

      <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
        <div
          v-for="(item, index) in form.exercises"
          :key="index"
          class="mb-3 p-3 bg-white rounded border border-slate-200"
        >
          <div class="flex gap-2 mb-2">
            <a-select
              v-model:value="item.exercise_id"
              show-search
              placeholder="Chọn bài tập"
              :options="exerciseOptions"
              :filter-option="filterOption"
              class="flex-1"
            />
            <a-button
              danger
              size="small"
              type="text"
              @click="removeExercise(index)"
            >
              <template #icon><DeleteOutlined /></template>
            </a-button>
          </div>
          <div class="grid grid-cols-4 gap-2">
            <a-form-item label="Sets" class="mb-0">
              <a-input-number
                v-model:value="item.sets"
                :min="1"
                size="small"
                class="w-full"
              />
            </a-form-item>
            <a-form-item label="Reps" class="mb-0">
              <a-input-number
                v-model:value="item.reps"
                :min="1"
                size="small"
                class="w-full"
              />
            </a-form-item>
            <a-form-item label="Thời gian (s)" class="mb-0">
              <a-input-number
                v-model:value="item.duration_seconds"
                :min="0"
                size="small"
                class="w-full"
              />
            </a-form-item>
            <a-form-item label="Nghỉ (s)" class="mb-0">
              <a-input-number
                v-model:value="item.rest_seconds"
                :min="0"
                size="small"
                class="w-full"
              />
            </a-form-item>
          </div>
        </div>
        <a-button type="dashed" block @click="addExercise" class="mt-2">
          <template #icon><PlusOutlined /></template>
          Thêm bài tập
        </a-button>
      </div>
    </a-form>
  </a-modal>
</template>
