<!--
  ScheduleModal.vue

  Modal for creating a workout schedule.
  Props:
    - open: Modal visibility
    - loading: Loading state
    - routines: Available routines list
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
  routines: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:open", "submit"]);

const form = defineModel("form", {
  type: Object,
  default: () => ({
    routine_id: null,
    name: "",
    schedule_config: {
      type: "weekly",
      days: [],
    },
    start_date: null,
    end_date: null,
    is_active: true,
  }),
});

const routineOptions = computed(() => {
  return props.routines.map((r) => ({
    value: r.id,
    label: r.name,
  }));
});

const weekDays = [
  { value: 0, label: "CN" },
  { value: 1, label: "T2" },
  { value: 2, label: "T3" },
  { value: 3, label: "T4" },
  { value: 4, label: "T5" },
  { value: 5, label: "T6" },
  { value: 6, label: "T7" },
];

const handleOk = () => {
  emit("submit", form.value);
};
</script>

<template>
  <a-modal
    :open="open"
    title="Tạo lịch tập"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
    width="500px"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Routine" required>
        <a-select
          v-model:value="form.routine_id"
          placeholder="Chọn routine"
          :options="routineOptions"
          class="w-full"
        />
      </a-form-item>

      <a-form-item label="Tên lịch">
        <a-input v-model:value="form.name" placeholder="Ví dụ: Lịch tập tuần" />
      </a-form-item>

      <a-form-item label="Loại lịch">
        <a-radio-group
          v-model:value="form.schedule_config.type"
          button-style="solid"
        >
          <a-radio-button value="weekly">Hàng tuần</a-radio-button>
          <a-radio-button value="interval">Theo khoảng</a-radio-button>
        </a-radio-group>
      </a-form-item>

      <a-form-item
        v-if="form.schedule_config.type === 'weekly'"
        label="Ngày trong tuần"
      >
        <a-checkbox-group v-model:value="form.schedule_config.days">
          <a-checkbox
            v-for="day in weekDays"
            :key="day.value"
            :value="day.value"
          >
            {{ day.label }}
          </a-checkbox>
        </a-checkbox-group>
      </a-form-item>

      <a-form-item v-else label="Khoảng cách (ngày)">
        <a-input-number
          v-model:value="form.schedule_config.interval_days"
          :min="1"
          class="w-full"
        />
      </a-form-item>

      <a-row :gutter="16">
        <a-col :span="12">
          <a-form-item label="Ngày bắt đầu">
            <a-date-picker v-model:value="form.start_date" class="w-full" />
          </a-form-item>
        </a-col>
        <a-col :span="12">
          <a-form-item label="Ngày kết thúc">
            <a-date-picker v-model:value="form.end_date" class="w-full" />
          </a-form-item>
        </a-col>
      </a-row>
    </a-form>
  </a-modal>
</template>
