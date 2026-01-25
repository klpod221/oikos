<!--
  Login.vue

  Login page component.
  Handles user authentication via email/password.
-->
<script setup>
import { reactive } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import { message } from "ant-design-vue";
import { UserOutlined, LockOutlined } from "@ant-design/icons-vue";

const router = useRouter();
const auth = useAuthStore();

const form = reactive({ email: "", password: "" });

const onSubmit = async () => {
  if (await auth.login(form)) {
    message.success("Chào mừng trở lại!");
    router.push("/");
  } else {
    message.error(auth.error || "Đăng nhập thất bại");
  }
};
</script>

<template>
  <div>
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Chào mừng trở lại</h1>
      <p class="text-slate-500 mt-1">Đăng nhập vào tài khoản của bạn</p>
    </div>

    <a-form :model="form" @finish="onSubmit" layout="vertical">
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
          <template #prefix><UserOutlined /></template>
        </a-input>
      </a-form-item>

      <a-form-item
        name="password"
        :rules="[{ required: true, message: 'Vui lòng nhập mật khẩu' }]"
      >
        <a-input-password
          v-model:value="form.password"
          placeholder="Mật khẩu"
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
          Đăng nhập
        </a-button>
      </a-form-item>
    </a-form>

    <div class="text-center text-slate-500">
      Chưa có tài khoản?
      <router-link to="/register" class="text-blue-500">Đăng ký</router-link>
    </div>
  </div>
</template>
