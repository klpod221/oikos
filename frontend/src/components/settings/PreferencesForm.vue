<!--
  PreferencesForm.vue

  Form for updating user preferences (Currency, Language, Theme, Units).
  Loads initial settings from store on mount.
-->
<script setup>
import { ref, onMounted } from "vue";
import { useSettingsStore } from "../../stores/settings";

import { EnvironmentOutlined } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";

const settingsStore = useSettingsStore();
const loading = ref(false);

const formState = ref({
  currency: "VND",
  gold_unit: "lượng",
  silver_unit: "lượng",
  language: "vi",
  theme: "system",
  latitude: null,
  longitude: null,
});

onMounted(async () => {
  await settingsStore.fetchSettings();
  if (settingsStore.settings) {
    formState.value = { ...settingsStore.settings };
  }
});

const getCurrentLocation = () => {
  if (!navigator.geolocation) {
    message.error("Trình duyệt không hỗ trợ Geolocation");
    return;
  }

  message.loading("Đang lấy vị trí...", 1);
  navigator.geolocation.getCurrentPosition(
    (position) => {
      formState.value.latitude = parseFloat(
        position.coords.latitude.toFixed(4),
      );
      formState.value.longitude = parseFloat(
        position.coords.longitude.toFixed(4),
      );
      message.success("Đã cập nhật vị trí");
    },
    (error) => {
      console.error(error);
      message.error("Không thể lấy vị trí: " + error.message);
    },
  );
};

const onFinish = async (values) => {
  loading.value = true;
  await settingsStore.updatePreferences(values);
  loading.value = false;
};
</script>

<template>
  <div class="max-w-xl">
    <a-alert
      message="Cài đặt hiển thị"
      description="Tùy chỉnh cách hiển thị dữ liệu trên bảng điều khiển."
      type="info"
      show-icon
      class="mb-6"
    />

    <a-form layout="vertical" :model="formState" @finish="onFinish">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a-form-item label="Tiền tệ cơ sở" name="currency">
          <a-select v-model:value="formState.currency" size="large">
            <a-select-option value="VND">VND (Việt Nam Đồng)</a-select-option>
            <a-select-option value="USD">USD (Đô la Mỹ)</a-select-option>
            <a-select-option value="EUR">EUR (Euro)</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Ngôn ngữ" name="language">
          <a-select v-model:value="formState.language" size="large">
            <a-select-option value="vi">Tiếng Việt</a-select-option>
            <a-select-option value="en">English</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Đơn vị vàng" name="gold_unit">
          <a-select v-model:value="formState.gold_unit" size="large">
            <a-select-option value="lượng">Lượng</a-select-option>
            <a-select-option value="chỉ">Chỉ</a-select-option>
            <a-select-option value="oz">Ounce (oz)</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Đơn vị bạc" name="silver_unit">
          <a-select v-model:value="formState.silver_unit" size="large">
            <a-select-option value="lượng">Lượng</a-select-option>
            <a-select-option value="chỉ">Chỉ</a-select-option>
            <a-select-option value="oz">Ounce (oz)</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Giao diện" name="theme">
          <a-select v-model:value="formState.theme" size="large">
            <a-select-option value="system">Theo hệ thống</a-select-option>
            <a-select-option value="light">Sáng</a-select-option>
            <a-select-option value="dark">Tối</a-select-option>
          </a-select>
        </a-form-item>
      </div>

      <a-divider orientation="left">Cấu hình Thời tiết</a-divider>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a-form-item label="Vĩ độ (Latitude)" name="latitude">
          <a-input-number
            v-model:value="formState.latitude"
            class="w-full"
            size="large"
            :min="-90"
            :max="90"
            :step="0.0001"
          />
        </a-form-item>
        <a-form-item label="Kinh độ (Longitude)" name="longitude">
          <a-input-number
            v-model:value="formState.longitude"
            class="w-full"
            size="large"
            :min="-180"
            :max="180"
            :step="0.0001"
          />
        </a-form-item>
      </div>

      <div class="text-right mb-4">
        <a-button @click="getCurrentLocation" size="middle">
          <template #icon><EnvironmentOutlined /></template>
          Lấy vị trí hiện tại của tôi
        </a-button>
      </div>

      <a-form-item class="mt-4">
        <a-button
          type="primary"
          html-type="submit"
          :loading="loading"
          size="large"
        >
          Lưu tùy chọn
        </a-button>
      </a-form-item>
    </a-form>
  </div>
</template>
