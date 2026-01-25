<script setup>
import { HeartFilled } from "@ant-design/icons-vue";
</script>

<template>
  <div class="min-h-screen flex">
    <!-- Left Side - Branding (Desktop only) -->
    <div
      class="hidden lg:flex lg:w-1/2 bg-linear-to-br from-blue-600 via-indigo-600 to-purple-700 p-12 flex-col justify-between"
    >
      <div>
        <!-- White background for logo -->
        <div class="inline-block bg-white rounded-2xl p-3 shadow-lg">
          <img src="/logo.png" alt="OikOS" class="h-10" />
        </div>
      </div>

      <div class="text-white space-y-6">
        <h1 class="text-4xl font-bold leading-tight">
          Quản lý cuộc sống,<br />
          tất cả trong một.
        </h1>
        <p class="text-blue-100 text-lg max-w-md">
          Theo dõi tài chính, lên kế hoạch ăn uống và đạt được mục tiêu cùng
          OikOS - người bạn đồng hành quản lý cuộc sống cá nhân của bạn.
        </p>
      </div>

      <div class="text-blue-200 text-sm">
        Made with <HeartFilled class="text-red-500!" /> by
        <a href="https://github.com/klpod221" target="_blank">klpod221</a>
      </div>
    </div>

    <!-- Right Side - Form -->
    <div
      class="w-full lg:w-1/2 flex items-center justify-center p-6 bg-slate-100"
    >
      <a-card class="w-full max-w-md shadow-lg">
        <!-- Mobile Logo -->
        <div class="lg:hidden text-center mb-6">
          <img src="/logo.png" alt="OikOS" class="h-16 mx-auto mb-2" />
          <h2 class="text-xl font-bold text-slate-800">OikOS</h2>
        </div>
        <slot />
      </a-card>
    </div>
  </div>
</template>
