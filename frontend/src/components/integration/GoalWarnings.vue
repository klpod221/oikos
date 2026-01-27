<!--
  GoalWarnings.vue

  Displays goal warnings/alerts from integration module.
  Props:
    - warnings: Array of warning objects
-->
<script setup>
import { computed } from "vue";
import {
  WarningOutlined,
  ExclamationCircleOutlined,
  CheckCircleOutlined,
} from "@ant-design/icons-vue";

const props = defineProps({
  warnings: { type: Array, default: () => [] },
});

const warningType = (warning) => {
  if (warning.severity === "high") return "error";
  if (warning.severity === "medium") return "warning";
  return "info";
};

const iconComponent = (warning) => {
  if (warning.severity === "high") return ExclamationCircleOutlined;
  if (warning.severity === "medium") return WarningOutlined;
  return CheckCircleOutlined;
};
</script>

<template>
  <div v-if="warnings.length > 0" class="space-y-2">
    <a-alert
      v-for="warning in warnings"
      :key="warning.id || warning.message"
      :type="warningType(warning)"
      :message="warning.title || 'Cảnh báo'"
      :description="warning.message"
      show-icon
      closable
    />
  </div>
</template>
