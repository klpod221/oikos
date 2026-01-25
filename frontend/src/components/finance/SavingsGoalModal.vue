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
    :title="goal ? 'Chỉnh sửa mục tiêu' : 'Mục tiêu mới'"
    @update:open="emit('update:open', $event)"
    @ok="handleOk"
    :confirm-loading="loading"
  >
    <a-form :model="form" layout="vertical" class="mt-4">
      <a-form-item label="Tên mục tiêu" required>
        <a-input v-model:value="form.name" placeholder="Ví dụ: Laptop mới" />
      </a-form-item>

      <div class="grid grid-cols-2 gap-2">
        <a-form-item label="Số tiền mục tiêu">
          <a-input-number
            v-model:value="form.target_amount"
            :min="0"
            class="w-full!"
            :formatter="
              (value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
            "
            :parser="(value) => value.replace(/\$\s?|(,*)/g, '')"
          />
        </a-form-item>
        <a-form-item label="Đã tiết kiệm được">
          <a-input-number
            v-model:value="form.current_amount"
            :min="0"
            class="w-full!"
            :formatter="
              (value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
            "
            :parser="(value) => value.replace(/\$\s?|(,*)/g, '')"
          />
        </a-form-item>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <a-form-item label="Tiền tệ">
          <a-select v-model:value="form.currency">
            <a-select-option value="VND">VND</a-select-option>
            <a-select-option value="USD">USD</a-select-option>
            <a-select-option value="EUR">EUR</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="Ngày đến hạn">
          <a-input v-model:value="form.deadline" type="date" class="w-full!" />
        </a-form-item>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <a-form-item label="Icon">
          <a-select v-model:value="form.icon">
            <a-select-option value="🎯">🎯 Mục tiêu</a-select-option>
            <a-select-option value="💻">💻 Thiết bị</a-select-option>
            <a-select-option value="✈️">✈️ Du lịch</a-select-option>
            <a-select-option value="🏠">🏠 Nhà cửa</a-select-option>
            <a-select-option value="🚗">🚗 Xe cộ</a-select-option>
            <a-select-option value="🎓">🎓 Giáo dục</a-select-option>
            <a-select-option value="🛡️">🛡️ Khẩn cấp</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="Màu sắc">
          <a-input v-model:value="form.color" type="color" class="h-8!" />
        </a-form-item>
      </div>

      <a-form-item label="Trạng thái">
        <a-select v-model:value="form.status">
          <a-select-option value="in_progress">Đang thực hiện</a-select-option>
          <a-select-option value="completed">Đã hoàn thành</a-select-option>
          <a-select-option value="cancelled">Đã hủy</a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="Mô tả">
        <a-textarea
          v-model:value="form.description"
          placeholder="Tại sao bạn lại tiết kiệm cho mục tiêu này?"
          :rows="2"
        />
      </a-form-item>
    </a-form>
  </a-modal>
</template>
