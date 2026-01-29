<!--
  DefaultLayout.vue

  Main application layout.
  Features:
  - Responsive Sidebar/Drawer.
  - Header with User Menu.
  - Main Content Area.
  - Responsive behavior handling (mobile/tablet/desktop).
-->
<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../stores/auth";
import {
  DashboardOutlined,
  WalletOutlined,
  ExperimentOutlined,
  SettingOutlined,
  LogoutOutlined,
  UserOutlined,
  MenuOutlined,
  CloseOutlined,
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  TeamOutlined,
  TagsOutlined,
  CoffeeOutlined,
  TrophyOutlined,
  RobotOutlined,
  ControlOutlined,
} from "@ant-design/icons-vue";

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

// Responsive states
const isMobile = ref(false);
const isTablet = ref(false);
const mobileDrawerOpen = ref(false);
const sidebarCollapsed = ref(false);

const selectedKeys = computed(() => [route.path]);

// Handle resize
const handleResize = () => {
  const width = window.innerWidth;
  isMobile.value = width < 768; // md breakpoint
  isTablet.value = width >= 768 && width < 1024; // md to lg

  // Auto collapse on tablet
  if (isTablet.value) {
    sidebarCollapsed.value = true;
  } else if (width >= 1024) {
    // Desktop - keep user preference or default expanded
    // Don't auto-change on desktop resize
  }
};

onMounted(() => {
  handleResize();
  window.addEventListener("resize", handleResize);
});

// Close mobile drawer on route change
watch(
  () => route.path,
  () => {
    mobileDrawerOpen.value = false;
  },
);

const menuItems = [
  { key: "/", icon: DashboardOutlined, label: "Tổng quan" },
  { key: "/chat", icon: RobotOutlined, label: "Trợ lý AI" },
  { key: "/finance", icon: WalletOutlined, label: "Tài chính" },
  { key: "/nutrition", icon: ExperimentOutlined, label: "Dinh dưỡng" },
  { key: "/workout", icon: TrophyOutlined, label: "Tập luyện" },
  { key: "/settings", icon: SettingOutlined, label: "Cài đặt" },
];

const adminMenuItems = [
  { key: "/admin/users", icon: TeamOutlined, label: "Quản lý người dùng" },
  { key: "/admin/categories", icon: TagsOutlined, label: "Quản lý danh mục" },
  {
    key: "/admin/ingredients",
    icon: CoffeeOutlined,
    label: "Quản lý nguyên liệu",
  },
  {
    key: "/admin/settings",
    icon: ControlOutlined,
    label: "Cài đặt hệ thống",
  },
];

const handleLogout = async () => {
  await auth.logout();
  router.push("/login");
};

const navigateTo = (path) => {
  router.push(path);
  mobileDrawerOpen.value = false;
};

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};
</script>

<template>
  <a-layout class="min-h-screen!">
    <!-- Mobile Drawer Overlay -->
    <div
      v-if="isMobile && mobileDrawerOpen"
      class="fixed inset-0 bg-black/50 z-40"
      @click="mobileDrawerOpen = false"
    ></div>

    <!-- Mobile Drawer Sidebar -->
    <aside
      v-if="isMobile"
      class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 transform transition-transform duration-300"
      :class="mobileDrawerOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div
        class="h-16 flex items-center justify-between px-4 border-b border-slate-800"
      >
        <div class="flex items-center gap-2">
          <img src="/logo.png" alt="OikOS" class="w-8 h-8" />
          <span class="text-white font-semibold text-lg">OikOS</span>
        </div>
        <button class="text-white! p-1" @click="mobileDrawerOpen = false">
          <CloseOutlined />
        </button>
      </div>
      <nav class="p-3" aria-label="Mobile navigation">
        <a-menu
          v-model:selectedKeys="selectedKeys"
          theme="dark"
          mode="inline"
          class="bg-transparent! border-0!"
        >
          <a-menu-item
            v-for="item in menuItems"
            :key="item.key"
            class="rounded-lg! mb-1!"
            @click="navigateTo(item.key)"
          >
            <component :is="item.icon" class="text-lg" />
            <span>{{ item.label }}</span>
          </a-menu-item>
          <a-menu-divider v-if="auth.isAdmin" class="my-2! border-slate-700!" />
          <div v-if="auth.isAdmin" class="px-4 mb-2">
            <span class="text-xs text-slate-500">Quản trị</span>
          </div>
          <a-menu-item
            v-for="item in adminMenuItems"
            v-if="auth.isAdmin"
            :key="item.key"
            class="rounded-lg! mb-1!"
            @click="navigateTo(item.key)"
          >
            <component :is="item.icon" class="text-lg" />
            <span>{{ item.label }}</span>
          </a-menu-item>
        </a-menu>
      </nav>
    </aside>

    <!-- Desktop/Tablet Sidebar -->
    <a-layout-sider
      v-else
      v-model:collapsed="sidebarCollapsed"
      :trigger="null"
      collapsible
      :width="240"
      :collapsed-width="64"
      class="bg-slate-900!"
    >
      <div
        class="h-16 flex items-center justify-center border-b border-slate-800 gap-2"
      >
        <img src="/logo.png" alt="OikOS" class="w-8 h-8" />
        <span v-if="!sidebarCollapsed" class="text-white font-semibold text-lg"
          >OikOS</span
        >
      </div>
      <nav class="p-2" aria-label="Main navigation">
        <a-menu
          v-model:selectedKeys="selectedKeys"
          theme="dark"
          mode="inline"
          class="bg-transparent! border-0!"
        >
          <a-menu-item
            v-for="item in menuItems"
            :key="item.key"
            class="rounded-lg! mb-1!"
            @click="navigateTo(item.key)"
          >
            <component :is="item.icon" class="text-lg" />
            <span>{{ item.label }}</span>
          </a-menu-item>
          <!-- Admin Section Divider -->
          <div v-if="auth.isAdmin" class="my-3 px-2">
            <div class="border-t border-slate-700"></div>
          </div>
          <a-menu-item
            v-for="item in adminMenuItems"
            v-if="auth.isAdmin"
            :key="item.key"
            class="rounded-lg! mb-1!"
            @click="navigateTo(item.key)"
          >
            <component :is="item.icon" class="text-lg" />
            <span>{{ item.label }}</span>
          </a-menu-item>
        </a-menu>
      </nav>
    </a-layout-sider>

    <!-- Main Content -->
    <a-layout>
      <!-- Header -->
      <a-layout-header
        class="bg-white! px-4! flex items-center justify-between shadow-sm sticky top-0 z-30 h-16!"
      >
        <!-- Mobile: Hamburger -->
        <button
          v-if="isMobile"
          class="text-xl text-slate-600 p-2 -ml-2"
          @click="mobileDrawerOpen = true"
        >
          <MenuOutlined />
        </button>

        <!-- Desktop/Tablet: Collapse toggle -->
        <button
          v-else
          class="text-xl text-slate-600 p-2 -ml-2 hover:text-blue-600 transition-colors cursor-pointer"
          @click="toggleSidebar"
        >
          <MenuUnfoldOutlined v-if="sidebarCollapsed" />
          <MenuFoldOutlined v-else />
        </button>

        <a-dropdown>
          <div class="flex items-center gap-3 cursor-pointer">
            <div class="text-right hidden sm:block">
              <div class="text-sm font-medium text-slate-800">
                {{ auth.user?.name || "Người dùng" }}
              </div>
              <div class="text-xs text-slate-500">{{ auth.user?.email }}</div>
            </div>
            <a-avatar :size="36" :src="auth.user?.avatar" class="bg-blue-500">
              <template #icon v-if="!auth.user?.avatar"
                ><UserOutlined
              /></template>
            </a-avatar>
          </div>
          <template #overlay>
            <a-menu>
              <a-menu-item key="profile" @click="router.push('/settings')">
                <UserOutlined class="mr-2" /> Hồ sơ & Cài đặt
              </a-menu-item>
              <a-menu-divider />
              <a-menu-item
                key="logout"
                @click="handleLogout"
                class="text-red-500!"
              >
                <LogoutOutlined class="mr-2" /> Đăng xuất
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>
      </a-layout-header>

      <!-- Content -->
      <a-layout-content
        :class="route.meta.noLayoutPadding ? 'h-[calc(100vh-64px)]' : 'm-4'"
      >
        <div
          v-if="!route.meta.noLayoutPadding"
          class="bg-white rounded-xl p-4 lg:p-6 max-h-[calc(100vh-96px)] overflow-y-auto overflow-x-hidden"
        >
          <slot />
        </div>
        <slot v-else />
      </a-layout-content>
    </a-layout>
  </a-layout>
</template>

<style scoped>
:deep(.ant-layout-sider) {
  background: #0f172a !important;
}
:deep(.ant-menu-dark) {
  background: transparent !important;
}
:deep(.ant-menu-dark .ant-menu-item-selected) {
  background: linear-gradient(90deg, #3b82f6, #6366f1) !important;
}
</style>
