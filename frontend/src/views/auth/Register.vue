<!--
  Register.vue

  Registration page component.
  Handles new user registration.
-->
<script setup>
import { reactive, ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import { authService } from "../../services/auth.service";
import { message } from "ant-design-vue";
import {
  UserOutlined,
  LockOutlined,
  MailOutlined,
} from "@ant-design/icons-vue";

const router = useRouter();
const auth = useAuthStore();

const registrationAllowed = ref(true);
const loadingSettings = ref(true);

const form = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

onMounted(async () => {
  try {
    const response = await authService.getPublicSettings();
    // Convert to boolean just in case
    let allowed = response.data.allow_registration;
    if (allowed === "true") allowed = true;
    if (allowed === "false") allowed = false;

    if (allowed !== undefined) {
      registrationAllowed.value = allowed;
    }
  } catch (e) {
    // Fallback to true or handle error
  } finally {
    loadingSettings.value = false;
  }
});

const onSubmit = async () => {
  if (form.password !== form.password_confirmation) {
    message.error("Mật khẩu không khớp");
    return;
  }
  if (await auth.register(form)) {
    message.success("Tạo tài khoản thành công!");
    router.push("/");
  } else {
    message.error(auth.error || "Đăng ký thất bại");
  }
};
</script>

<template>
  <div>
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Tạo tài khoản</h1>
      <p class="text-slate-500 mt-1">Tham gia OikOS ngay hôm nay</p>
    </div>

    <div v-if="loadingSettings" class="text-center py-10">
      <a-spin />
    </div>

    <div
      v-else-if="!registrationAllowed"
      class="text-center p-6 space-y-4 bg-red-50 rounded-xl border border-red-100"
    >
      <div class="text-4xl">🚫</div>
      <h3 class="text-lg font-bold text-red-600">Đăng ký tạm khóa</h3>
      <p class="text-slate-600">
        Hệ thống tạm thời không nhận đăng ký mới.<br />Vui lòng thử lại sau hoặc
        liên hệ Admin.
      </p>
      <div class="pt-4">
        <router-link to="/login">
          <a-button type="primary">Quay lại Đăng nhập</a-button>
        </router-link>
      </div>
    </div>

    <div v-else>
      <a-form :model="form" @finish="onSubmit" layout="vertical">
        <a-form-item
          name="name"
          :rules="[{ required: true, message: 'Vui lòng nhập tên của bạn' }]"
        >
          <a-input
            v-model:value="form.name"
            placeholder="Họ và tên"
            size="large"
          >
            <template #prefix><UserOutlined /></template>
          </a-input>
        </a-form-item>

        <a-form-item
          name="email"
          :rules="[
            {
              required: true,
              type: 'email',
              message: 'Vui lòng nhập email hợp lệ',
            },
          ]"
        >
          <a-input v-model:value="form.email" placeholder="Email" size="large">
            <template #prefix><MailOutlined /></template>
          </a-input>
        </a-form-item>

        <a-form-item
          name="password"
          :rules="[
            {
              required: true,
              min: 8,
              message: 'Mật khẩu phải có ít nhất 8 ký tự',
            },
          ]"
        >
          <a-input-password
            v-model:value="form.password"
            placeholder="Mật khẩu"
            size="large"
          >
            <template #prefix><LockOutlined /></template>
          </a-input-password>
        </a-form-item>

        <a-form-item
          name="password_confirmation"
          :rules="[{ required: true, message: 'Vui lòng xác nhận mật khẩu' }]"
        >
          <a-input-password
            v-model:value="form.password_confirmation"
            placeholder="Xác nhận mật khẩu"
            size="large"
          >
            <template #prefix><LockOutlined /></template>
          </a-input-password>
        </a-form-item>

        <a-form-item>
          <a-button
            type="primary"
            html-type="submit"
            size="large"
            block
            :loading="auth.loading"
          >
            Tạo tài khoản
          </a-button>
        </a-form-item>
      </a-form>
    </div>

    <div class="text-center text-slate-500 mt-6">
      Đã có tài khoản?
      <router-link to="/login" class="text-blue-500">Đăng nhập</router-link>
    </div>
  </div>
</template>
