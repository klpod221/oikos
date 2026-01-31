<!--
  GoogleCallback.vue

  Handles the callback from Google OAuth.
  Exchanges the code/token for a backend JWT.
-->
<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import { message } from "ant-design-vue";
import api from "../../utils/axios";

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

onMounted(async () => {
  // Determine if we have a code (from URL query) or fragment (if using implicit flow, but we are using code flow via backend)
  // Socialite stateless usually expects 'code' in query params.
  const query = route.query;

  if (!query.code && !query.state) {
    message.error("Lỗi xác thực Google: Không nhận được mã phản hồi.");
    router.push("/login");
    return;
  }

  try {
    // Forward the entire query string to the backend
    const response = await api.post("/auth/google/callback", {
      ...query,
      device_name: "web",
    });

    if (response.data.success) {
      auth.setAuthData(response.data.data);
      message.success("Đăng nhập bằng Google thành công!");
      router.push("/");
    } else {
      throw new Error(response.data.message);
    }
  } catch (error) {
    console.error(error);
    if (error.response?.data?.message) {
      message.error(error.response.data.message);
    } else {
      message.error("Đăng nhập bằng Google thất bại.");
    }
    router.push("/login");
  }
});
</script>

<template>
  <div class="h-[50vh] flex items-center justify-center bg-slate-50">
    <div class="text-center">
      <a-spin size="large" />
      <p class="mt-4 text-slate-500">Đang xác thực với Google...</p>
    </div>
  </div>
</template>
