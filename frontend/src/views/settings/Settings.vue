<!--
  Settings.vue

  User settings view with tabs:
  - Profile: Name, Email, Avatar
  - User Stats: Weight, Height, Age, Activity
  - User Goals: Calories, Macros, Workout targets
  - Preferences: Currency, Language, Theme
  - Security: Password change
-->
<script setup>
import { ref } from "vue";
import {
  UserOutlined,
  SettingOutlined,
  SafetyOutlined,
  BarChartOutlined,
  HeartOutlined,
  ApiOutlined,
  BellOutlined,
} from "@ant-design/icons-vue";

// Form Components
import ProfileForm from "../../components/settings/ProfileForm.vue";
import PreferencesForm from "../../components/settings/PreferencesForm.vue";
import PasswordForm from "../../components/settings/PasswordForm.vue";
import UserStatsForm from "../../components/settings/UserStatsForm.vue";
import UserGoalsForm from "../../components/settings/UserGoalsForm.vue";
import IntegrationSettings from "../../components/settings/IntegrationSettings.vue";
import NotificationSettings from "./NotificationSettings.vue"; // Relative import since in same folder

const activeKey = ref("profile");
const isAndroid = ref(false);

import { onMounted } from "vue";
onMounted(() => {
  if (window.AndroidNotification) {
    isAndroid.value = true;
  }
});
</script>

<template>
  <div class="space-y-2">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-2"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Cài đặt</h1>
        <p class="text-slate-500">Quản lý thông tin và tùy chọn của bạn</p>
      </div>
    </div>

    <a-tabs v-model:activeKey="activeKey">
      <!-- Profile Tab -->
      <a-tab-pane key="profile">
        <template #tab>
          <span class="flex items-center gap-2">
            <UserOutlined />
            <span class="hidden sm:inline">Hồ sơ</span>
          </span>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column: Identity & Stats -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Identity Card -->
            <div
              class="bg-white rounded-xl p-6 border border-slate-200 flex flex-col h-fit"
            >
              <div class="flex items-center gap-3 mb-6">
                <div class="flex items-center justify-center text-slate-600">
                  <UserOutlined class="text-xl" />
                </div>
                <h2 class="text-base font-bold text-slate-800 m-0">
                  Định danh
                </h2>
              </div>
              <ProfileForm />
            </div>

            <!-- Stats Card -->
            <div
              class="bg-white rounded-xl p-6 border border-slate-200 flex flex-col h-fit"
            >
              <div class="flex items-center gap-3 mb-6">
                <div class="flex items-center justify-center text-slate-600">
                  <BarChartOutlined class="text-xl" />
                </div>
                <div>
                  <h2 class="text-base font-bold text-slate-800 m-0">
                    Chỉ số cơ thể
                  </h2>
                  <p class="text-slate-500 text-xs m-0">
                    Cân nặng, chiều cao & chỉ số BMR
                  </p>
                </div>
              </div>
              <UserStatsForm />
            </div>
          </div>

          <!-- Right Column: Goals -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-xl p-8 border border-slate-200 h-full">
              <div
                class="flex items-center gap-3 mb-8 border-b border-slate-100 pb-4"
              >
                <div class="flex items-center justify-center text-slate-600">
                  <HeartOutlined class="text-2xl" />
                </div>
                <div>
                  <h2 class="text-lg font-bold text-slate-800 m-0">
                    Mục tiêu sức khỏe
                  </h2>
                  <p class="text-slate-500 text-sm m-0">
                    Thiết lập lộ trình Calories & Macro dinh dưỡng
                  </p>
                </div>
              </div>
              <UserGoalsForm />
            </div>
          </div>
        </div>
      </a-tab-pane>

      <!-- Preferences Tab -->
      <a-tab-pane key="preferences">
        <template #tab>
          <span class="flex items-center gap-2">
            <SettingOutlined />
            <span class="hidden sm:inline">Tùy chọn</span>
          </span>
        </template>
        <div class="bg-white border border-slate-200 rounded-xl p-2 lg:p-6">
          <h2 class="text-lg font-semibold text-slate-800 mb-4">
            Tùy chọn ứng dụng
          </h2>
          <PreferencesForm />
        </div>
      </a-tab-pane>

      <!-- Security Tab -->
      <a-tab-pane key="security">
        <template #tab>
          <span class="flex items-center gap-2">
            <SafetyOutlined />
            <span class="hidden sm:inline">Bảo mật</span>
          </span>
        </template>
        <div class="bg-white border border-slate-200 rounded-xl p-2 lg:p-6">
          <h2 class="text-lg font-semibold text-slate-800 mb-4">
            Đổi mật khẩu
          </h2>
          <PasswordForm />
        </div>
      </a-tab-pane>
      <!-- Integration Tab -->
      <a-tab-pane key="integration">
        <template #tab>
          <span class="flex items-center gap-2">
            <ApiOutlined />
            <span class="hidden sm:inline">Kết nối</span>
          </span>
        </template>
        <div class="bg-white border border-slate-200 rounded-xl p-2 lg:p-6">
          <h2 class="text-lg font-semibold text-slate-800 mb-4">
            Kết nối tài khoản
          </h2>
          <IntegrationSettings />
        </div>
      </a-tab-pane>

      <!-- Notification Tab (Android Only) -->
      <a-tab-pane key="notifications" v-if="isAndroid">
        <template #tab>
          <span class="flex items-center gap-2">
            <BellOutlined />
            <span class="hidden sm:inline">Thông báo</span>
          </span>
        </template>
        <div class="bg-white border border-slate-200 rounded-xl p-2 lg:p-6">
          <NotificationSettings />
        </div>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>
