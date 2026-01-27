<!--
  PasswordForm.vue

  Form for changing user password.
  Validation:
  - Checks if old password is provided.
  - Checks if new password is at least 8 chars.
  - Checks if confirmation matches new password.
-->
<script setup>
import { ref } from "vue";
import { useSettingsStore } from "../../stores/settings";
import { LockOutlined } from "@ant-design/icons-vue";

const settingsStore = useSettingsStore();
const loading = ref(false);
const formRef = ref();

const formState = ref({
  old_password: "",
  new_password: "",
  new_password_confirmation: "",
});

const onFinish = async (values) => {
  loading.value = true;
  const success = await settingsStore.changePassword(values);
  loading.value = false;

  if (success) {
    formRef.value.resetFields();
  }
};

const validatePass2 = async (_rule, value) => {
  if (value === "") {
    return Promise.reject("Vui lòng nhập lại mật khẩu");
  } else if (value !== formState.value.new_password) {
    return Promise.reject("Hai mật khẩu không khớp!");
  } else {
    return Promise.resolve();
  }
};
</script>

<template>
  <div class="max-w-xl">
    <a-form
      ref="formRef"
      layout="vertical"
      :model="formState"
      @finish="onFinish"
    >
      <a-form-item
        label="Mật khẩu hiện tại"
        name="old_password"
        :rules="[
          { required: true, message: 'Vui lòng nhập mật khẩu hiện tại!' },
        ]"
      >
        <a-input-password v-model:value="formState.old_password" size="large">
          <template #prefix><LockOutlined class="text-slate-400" /></template>
        </a-input-password>
      </a-form-item>

      <a-form-item
        label="Mật khẩu mới"
        name="new_password"
        :rules="[
          { required: true, message: 'Vui lòng nhập mật khẩu mới!' },
          { min: 8, message: 'Mật khẩu phải có ít nhất 8 ký tự!' },
        ]"
      >
        <a-input-password v-model:value="formState.new_password" size="large">
          <template #prefix><LockOutlined class="text-slate-400" /></template>
        </a-input-password>
      </a-form-item>

      <a-form-item
        label="Xác nhận mật khẩu mới"
        name="new_password_confirmation"
        :rules="[{ validator: validatePass2, trigger: 'change' }]"
      >
        <a-input-password
          v-model:value="formState.new_password_confirmation"
          size="large"
        >
          <template #prefix><LockOutlined class="text-slate-400" /></template>
        </a-input-password>
      </a-form-item>

      <a-form-item>
        <a-button
          type="primary"
          html-type="submit"
          :loading="loading"
          size="large"
        >
          Đổi mật khẩu
        </a-button>
      </a-form-item>
    </a-form>
  </div>
</template>
