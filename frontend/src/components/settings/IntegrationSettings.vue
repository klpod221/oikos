<!--
  IntegrationSettings.vue

  Manage external integrations.
  Currently supports: Gmail Bank Sync.
-->
<script setup>
import { ref } from "vue";
import { message } from "ant-design-vue";
import {
  GoogleOutlined,
  SyncOutlined,
  CheckCircleOutlined,
} from "@ant-design/icons-vue";
import { authService } from "../../services/auth.service";
import { useAuthStore } from "../../stores/auth";

const auth = useAuthStore();
const loading = ref(false);

const disconnectGoogle = () => {
  // Todo: Implement disconnect logic
  message.info("Tính năng ngắt kết nối đang phát triển");
};

const connectGoogle = async () => {
  try {
    const response = await authService.getGoogleAuthUrl();
    if (response.data.url) {
      globalThis.location.href = response.data.url;
    }
  } catch (error) {
    console.error(error);
    message.error("Không thể kết nối với Google.");
  }
};
</script>

<template>
  <div class="space-y-6">
    <!-- Google Connection Status -->
    <div
      class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200"
    >
      <div class="flex items-center gap-3">
        <GoogleOutlined class="text-2xl text-red-500" />
        <div>
          <h3 class="font-bold text-slate-700">Tài khoản Google</h3>
          <p class="text-sm text-slate-500" v-if="auth.user.google_id">
            Đã kết nối ({{ auth.user.email }})
          </p>
          <p class="text-sm text-slate-500" v-else>Chưa kết nối</p>
        </div>
      </div>
      <a-tag color="success" class="flex items-center gap-2 px-3 py-1">
        <template #icon>
          <SyncOutlined spin />
        </template>
        Tự động đồng bộ mỗi 10 phút
      </a-tag>
      <a-button
        v-if="!auth.user.google_id"
        type="primary"
        @click="connectGoogle"
        :loading="loading"
      >
        <GoogleOutlined />
        Kết nối Google
      </a-button>
      <a-button v-else danger @click="disconnectGoogle">
        Ngắt kết nối
      </a-button>
    </div>

    <!-- Instructions -->
    <div class="bg-blue-50 p-4 rounded-lg">
      <h4 class="font-bold text-blue-700 mb-2">Hướng dẫn:</h4>
      <ul class="list-disc list-inside text-sm text-blue-600 space-y-1">
        <li>Kết nối tài khoản Google để tự động đọc email thông báo số dư.</li>
        <li>Hệ thống sẽ tự động quét email mỗi 10 phút.</li>
        <li>
          Hỗ trợ: Vietcombank, Techcombank, VPBank (Tiêu đề: "Biến động số dư",
          "Biên lai chuyển tiền").
        </li>
      </ul>
    </div>
  </div>
</template>
