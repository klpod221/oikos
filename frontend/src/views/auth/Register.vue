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
    message.error("Passwords do not match");
    return;
  }
  if (await auth.register(form)) {
    message.success("Account created successfully!");
    router.push("/");
  } else {
    message.error(auth.error || "Registration failed");
  }
};
</script>

<template>
  <div>
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Create Account</h1>
      <p class="text-slate-500 mt-1">Join OikOS today</p>
    </div>

    <a-form :model="form" @finish="onSubmit" layout="vertical">
      <a-form-item
        name="name"
        :rules="[{ required: true, message: 'Please enter your name' }]"
      >
        <a-input v-model:value="form.name" placeholder="Full Name" size="large">
          <template #prefix><UserOutlined /></template>
        </a-input>
      </a-form-item>

      <a-form-item
        name="email"
        :rules="[
          {
            required: true,
            type: 'email',
            message: 'Please enter a valid email',
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
            message: 'Password must be at least 8 characters',
          },
        ]"
      >
        <a-input-password
          v-model:value="form.password"
          placeholder="Password"
          size="large"
        >
          <template #prefix><LockOutlined /></template>
        </a-input-password>
      </a-form-item>

      <a-form-item
        name="password_confirmation"
        :rules="[{ required: true, message: 'Please confirm your password' }]"
      >
        <a-input-password
          v-model:value="form.password_confirmation"
          placeholder="Confirm Password"
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
          Create Account
        </a-button>
      </a-form-item>
    </a-form>

    <div class="text-center text-slate-500">
      Already have an account?
      <router-link to="/login" class="text-blue-500">Sign in</router-link>
    </div>
  </div>
</template>
