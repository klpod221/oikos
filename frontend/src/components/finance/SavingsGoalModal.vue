<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  open: { type: Boolean, default: false },
  goal: { type: Object, default: null },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(["update:open", "submit"]);

const form = ref({
  name: "",
  description: "",
  target_amount: 0,
  current_amount: 0,
  currency: "VND",
  start_date: null,
  deadline: null,
  status: "in_progress",
  icon: "🎯",
  color: "#10b981",
});

watch(
  () => props.open,
  (newVal) => {
    if (newVal) {
      if (props.goal) {
        form.value = { ...props.goal };
      } else {
        form.value = {
          name: "",
          description: "",
          target_amount: 0,
          current_amount: 0,
          currency: "VND",
          start_date: new Date().toISOString().split("T")[0],
          deadline: null,
          status: "in_progress",
          icon: "🎯",
          color: "#10b981",
        };
      }
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
    :title="goal ? 'Edit Goal' : 'New Goal'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Goal Name" required>
        <a-input v-model:value="form.name" placeholder="e.g., New Laptop" />
      </a-form-item>

      <div class="grid grid-cols-2 gap-4">
        <a-form-item label="Target Amount">
          <a-input-number
            v-model:value="form.target_amount"
            :min="0"
            class="w-full!"
          />
        </a-form-item>
        <a-form-item label="Current Saved">
          <a-input-number
            v-model:value="form.current_amount"
            :min="0"
            class="w-full!"
          />
        </a-form-item>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <a-form-item label="Currency">
          <a-select v-model:value="form.currency">
            <a-select-option value="VND">VND</a-select-option>
            <a-select-option value="USD">USD</a-select-option>
            <a-select-option value="EUR">EUR</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="Deadline">
          <a-input v-model:value="form.deadline" type="date" class="w-full!" />
        </a-form-item>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <a-form-item label="Icon">
          <a-select v-model:value="form.icon">
            <a-select-option value="🎯">🎯 Target</a-select-option>
            <a-select-option value="💻">💻 Gadget</a-select-option>
            <a-select-option value="✈️">✈️ Travel</a-select-option>
            <a-select-option value="🏠">🏠 Home</a-select-option>
            <a-select-option value="🚗">🚗 Car</a-select-option>
            <a-select-option value="🎓">🎓 Education</a-select-option>
            <a-select-option value="🛡️">🛡️ Emergency</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="Color">
          <a-input v-model:value="form.color" type="color" class="h-8!" />
        </a-form-item>
      </div>

      <a-form-item label="Status">
        <a-select v-model:value="form.status">
          <a-select-option value="in_progress">In Progress</a-select-option>
          <a-select-option value="completed">Completed</a-select-option>
          <a-select-option value="cancelled">Cancelled</a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="Description">
        <a-textarea
          v-model:value="form.description"
          placeholder="Why are you saving for this?"
          :rows="2"
        />
      </a-form-item>
    </a-form>
  </a-modal>
</template>
