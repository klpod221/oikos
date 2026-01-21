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
    message.success("Welcome back!");
    router.push("/");
  } else {
    message.error(auth.error || "Login failed");
  }
};
</script>

<template>
  <div>
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Welcome Back</h1>
      <p class="text-slate-500 mt-1">Sign in to your account</p>
    </div>

    <a-form :model="form" @finish="onSubmit" layout="vertical">
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
          <template #prefix><UserOutlined /></template>
        </a-input>
      </a-form-item>

      <a-form-item
        name="password"
        :rules="[{ required: true, message: 'Please enter your password' }]"
      >
        <a-input-password
          v-model:value="form.password"
          placeholder="Password"
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
          Sign In
        </a-button>
      </a-form-item>
    </a-form>

    <div class="text-center text-slate-500">
      Don't have an account?
      <router-link to="/register" class="text-blue-500">Sign up</router-link>
    </div>
  </div>
</template>
