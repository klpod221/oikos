<!--
  GoogleLoginButton.vue

  Reusable button component for initiating Google OAuth login.
-->
<script setup>
import { GoogleOutlined } from "@ant-design/icons-vue";
import { ref } from "vue";
import api from "../../utils/axios";
import { message } from "ant-design-vue";

const loading = ref(false);

const handleGoogleLogin = async () => {
  loading.value = true;
  try {
    const response = await api.get("/auth/google/url");
    if (response.data.url) {
      globalThis.location.href = response.data.url;
    }
  } catch (error) {
    message.error("Không thể kết nối với Google Login");
    console.error(error);
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <a-button
    block
    size="large"
    :loading="loading"
    @click="handleGoogleLogin"
    class="flex items-center justify-center gap-2 mt-4"
  >
    <template #icon>
      <GoogleOutlined class="text-red-500" />
    </template>
    Đăng nhập bằng Google
  </a-button>
</template>
