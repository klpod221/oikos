<script setup>
import { reactive } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import { message } from "ant-design-vue";
import {
  UserOutlined,
  LockOutlined,
  MailOutlined,
} from "@ant-design/icons-vue";

const router = useRouter();
const auth = useAuthStore();

const form = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
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

    <a-form :model="form" @finish="onSubmit" layout="vertical">
      <a-form-item
        name="name"
        :rules="[{ required: true, message: 'Vui lòng nhập tên của bạn' }]"
      >
        <a-input v-model:value="form.name" placeholder="Họ và tên" size="large">
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

    <div class="text-center text-slate-500">
      Đã có tài khoản?
      <router-link to="/login" class="text-blue-500">Đăng nhập</router-link>
    </div>
  </div>
</template>
