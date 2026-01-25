<!--
  Settings.vue

  User settings view.
  Tabs:
  - Profile: Name, Email, Avatar
  - Preferences: Currency, Language, Theme
  - Security: Password change
-->
<script setup>
import { ref } from "vue";
import ProfileForm from "../../components/settings/ProfileForm.vue";
import PreferencesForm from "../../components/settings/PreferencesForm.vue";
import PasswordForm from "../../components/settings/PasswordForm.vue";
import {
  UserOutlined,
  SettingOutlined,
  SafetyOutlined,
} from "@ant-design/icons-vue";

const activeKey = ref("profile");
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

    <!-- Tabs -->
    <a-tabs v-model:activeKey="activeKey">
      <!-- Profile Tab -->
      <a-tab-pane key="profile">
        <template #tab>
          <span class="flex items-center gap-2">
            <UserOutlined />
            <span class="hidden sm:inline">Hồ sơ</span>
          </span>
        </template>
        <div class="bg-white border border-slate-200 rounded-xl p-4 lg:p-6">
          <h2 class="text-lg font-semibold text-slate-800 mb-4">
            Thông tin cá nhân
          </h2>
          <ProfileForm />
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
        <div class="bg-white border border-slate-200 rounded-xl p-4 lg:p-6">
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
        <div class="bg-white border border-slate-200 rounded-xl p-4 lg:p-6">
          <h2 class="text-lg font-semibold text-slate-800 mb-4">
            Đổi mật khẩu
          </h2>
          <PasswordForm />
        </div>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>
