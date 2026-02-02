<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4"
  >
    <div class="w-full max-w-lg text-center">
      <div class="mb-8 animate-pulse">
        <img src="/logo.png" alt="OikOS Logo" class="h-20 w-auto mx-auto" />
      </div>

      <a-result
        status="404"
        title="404"
        sub-title="Xin lỗi, trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển."
      >
        <template #extra>
          <div class="space-x-4">
            <router-link to="/">
              <a-button type="primary" size="large">
                <template #icon><HomeOutlined /></template>
                Về trang chủ
              </a-button>
            </router-link>

            <a-button size="large" @click="$router.back()">
              <template #icon><ArrowLeftOutlined /></template>
              Quay lại
            </a-button>
          </div>
        </template>
      </a-result>
    </div>
  </div>
</template>

<script setup>
import { HomeOutlined, ArrowLeftOutlined } from "@ant-design/icons-vue";
</script>
