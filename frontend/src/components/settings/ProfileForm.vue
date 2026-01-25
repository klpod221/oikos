<!--
  ProfileForm.vue

  Form for updating user profile (Name, Email) and Avatar.
  Handles file upload for avatar.
-->
<script setup>
import { ref, onMounted, computed } from "vue";
import { useSettingsStore } from "../../stores/settings";
import { useAuthStore } from "../../stores/auth";
import {
  UserOutlined,
  MailOutlined,
  UploadOutlined,
} from "@ant-design/icons-vue";

const settingsStore = useSettingsStore();
const authStore = useAuthStore();
const fileInput = ref(null);
const loading = ref(false);

const formState = ref({
  name: "",
  email: "",
});

const userAvatar = computed(() => authStore.user?.avatar);

onMounted(async () => {
  if (authStore.user) {
    formState.value = {
      name: authStore.user.name,
      email: authStore.user.email,
    };
  }
});

const onFinish = async (values) => {
  loading.value = true;
  await settingsStore.updateProfile(values);
  loading.value = false;
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileChange = async (event) => {
  const file = event.target.files[0];
  if (file) {
    await settingsStore.updateAvatar(file);
  }
};
</script>

<template>
  <div class="max-w-xl">
    <div class="mb-8 flex flex-col items-center sm:flex-row gap-6">
      <div class="relative group">
        <a-avatar :size="100" :src="userAvatar" class="bg-blue-500 text-3xl">
          <template #icon v-if="!userAvatar"><UserOutlined /></template>
        </a-avatar>
        <div
          class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
          @click="triggerFileInput"
        >
          <UploadOutlined class="text-white text-2xl" />
        </div>
        <input
          type="file"
          ref="fileInput"
          class="hidden"
          accept="image/*"
          @change="handleFileChange"
        />
      </div>
      <div>
        <h3 class="text-lg font-medium">Ảnh đại diện</h3>
        <p class="text-slate-500 text-sm mb-2">
          Nhấn vào ảnh để tải ảnh mới. JPG, PNG hoặc GIF, tối đa 2MB.
        </p>
      </div>
    </div>

    <a-form layout="vertical" :model="formState" @finish="onFinish">
      <a-form-item
        label="Họ và tên"
        name="name"
        :rules="[{ required: true, message: 'Vui lòng nhập tên của bạn!' }]"
      >
        <a-input v-model:value="formState.name" size="large">
          <template #prefix><UserOutlined class="text-slate-400" /></template>
        </a-input>
      </a-form-item>

      <a-form-item
        label="Địa chỉ email"
        name="email"
        :rules="[
          { required: true, message: 'Vui lòng nhập email!' },
          { type: 'email', message: 'Vui lòng nhập email hợp lệ!' },
        ]"
      >
        <a-input v-model:value="formState.email" size="large">
          <template #prefix><MailOutlined class="text-slate-400" /></template>
        </a-input>
      </a-form-item>

      <a-form-item>
        <a-button
          type="primary"
          html-type="submit"
          :loading="loading"
          size="large"
        >
          Lưu thay đổi
        </a-button>
      </a-form-item>
    </a-form>
  </div>
</template>
