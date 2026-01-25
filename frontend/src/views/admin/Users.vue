<script setup>
import { ref, onMounted, watch } from "vue";
import { useAdminStore } from "../../stores/admin";
import { useAuthStore } from "../../stores/auth";
import { message, Modal } from "ant-design-vue";
import {
  SearchOutlined,
  UserOutlined,
  LockOutlined,
  UnlockOutlined,
  EyeOutlined,
} from "@ant-design/icons-vue";
import { formatDate } from "../../utils/formatters";

const adminStore = useAdminStore();
const authStore = useAuthStore();

const searchQuery = ref("");
const roleFilter = ref("");
const statusFilter = ref("");

const columns = [
  {
    title: "ID",
    dataIndex: "id",
    key: "id",
    width: 80,
  },
  {
    title: "Tên",
    dataIndex: "name",
    key: "name",
    sorter: true,
  },
  {
    title: "Email",
    dataIndex: "email",
    key: "email",
    sorter: true,
  },
  {
    title: "Vai trò",
    dataIndex: "role",
    key: "role",
    width: 120,
  },
  {
    title: "Trạng thái",
    dataIndex: "status",
    key: "status",
    width: 120,
  },
  {
    title: "Ngày tạo",
    dataIndex: "created_at",
    key: "created_at",
    width: 150,
    sorter: true,
  },
  {
    title: "Thao tác",
    key: "action",
    width: 150,
  },
];

// Fetch users on mount
onMounted(() => {
  adminStore.fetchUsers();
});

// Watch filters and trigger search
watch([searchQuery, roleFilter, statusFilter], () => {
  adminStore.userFilters.search = searchQuery.value;
  adminStore.userFilters.role = roleFilter.value;
  adminStore.userFilters.status = statusFilter.value;
  adminStore.fetchUsers(1);
});

const handlePageChange = (page) => {
  adminStore.fetchUsers(page);
};

const handleTableChange = (pagination, filters, sorter) => {
  if (sorter.field && sorter.order) {
    adminStore.userFilters.sort_by = sorter.field;
    adminStore.userFilters.sort_order = sorter.order === "ascend" ? "asc" : "desc";
  } else {
    adminStore.userFilters.sort_by = "";
    adminStore.userFilters.sort_order = "desc";
  }
  adminStore.fetchUsers(pagination.current);
};

const handleBlockUser = (user) => {
  Modal.confirm({
    title: "Xác nhận khóa người dùng",
    content: `Bạn có chắc chắn muốn khóa người dùng "${user.name}"?`,
    okText: "Khóa",
    cancelText: "Hủy",
    okType: "danger",
    async onOk() {
      const success = await adminStore.blockUser(user.id);
      if (success) {
        message.success("Đã khóa người dùng thành công");
      } else {
        message.error(adminStore.error || "Không thể khóa người dùng");
      }
    },
  });
};

const handleUnblockUser = (user) => {
  Modal.confirm({
    title: "Xác nhận mở khóa người dùng",
    content: `Bạn có chắc chắn muốn mở khóa người dùng "${user.name}"?`,
    okText: "Mở khóa",
    cancelText: "Hủy",
    async onOk() {
      const success = await adminStore.unblockUser(user.id);
      if (success) {
        message.success("Đã mở khóa người dùng thành công");
      } else {
        message.error(adminStore.error || "Không thể mở khóa người dùng");
      }
    },
  });
};

const getRoleColor = (role) => {
  return role === "admin" ? "blue" : "default";
};

const getStatusColor = (status) => {
  return status === "active" ? "green" : "red";
};

const getStatusText = (status) => {
  return status === "active" ? "Hoạt động" : "Bị khóa";
};
</script>

<template>
  <div class="space-y-2">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-2"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Quản lý người dùng</h1>
        <p class="text-slate-500">Quản lý tất cả người dùng trong hệ thống</p>
      </div>
    </div>

    <!-- Filters -->
    <a-row :gutter="16">
      <a-col :xs="24" :sm="12" :md="8">
        <a-input
          v-model:value="searchQuery"
          placeholder="Tìm kiếm theo tên hoặc email..."
          allow-clear
        >
          <template #prefix>
            <SearchOutlined class="text-slate-400" />
          </template>
        </a-input>
      </a-col>
      <a-col :xs="24" :sm="12" :md="4">
        <a-select
          v-model:value="roleFilter"
          placeholder="Vai trò"
          allow-clear
          class="w-full"
        >
          <a-select-option value="">Tất cả</a-select-option>
          <a-select-option value="admin">Admin</a-select-option>
          <a-select-option value="user">User</a-select-option>
        </a-select>
      </a-col>
      <a-col :xs="24" :sm="12" :md="4">
        <a-select
          v-model:value="statusFilter"
          placeholder="Trạng thái"
          allow-clear
          class="w-full"
        >
          <a-select-option value="">Tất cả</a-select-option>
          <a-select-option value="active">Hoạt động</a-select-option>
          <a-select-option value="blocked">Bị khóa</a-select-option>
        </a-select>
      </a-col>
    </a-row>

    <!-- Users Table -->
    <div class="bg-white border border-slate-200 rounded-xl">
      <a-table
        :columns="columns"
        :data-source="adminStore.users"
        :loading="adminStore.loading"
        :pagination="false"
        :row-key="(record) => record.id"
        @change="handleTableChange"
        :scroll="{ x: 'max-content' }"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <div class="flex items-center gap-2">
              <a-avatar :size="32" class="bg-blue-500">
                <template #icon><UserOutlined /></template>
              </a-avatar>
              <span class="font-medium text-xs sm:text-sm">{{ record.name }}</span>
            </div>
          </template>

          <template v-if="column.key === 'role'">
            <a-tag :color="getRoleColor(record.role)" class="text-xs">
              {{ record.role === "admin" ? "Admin" : "User" }}
            </a-tag>
          </template>

          <template v-if="column.key === 'status'">
            <a-tag :color="getStatusColor(record.status)" class="text-xs">
              {{ getStatusText(record.status) }}
            </a-tag>
          </template>

          <template v-if="column.key === 'created_at'">
            <span class="text-xs sm:text-sm">{{ formatDate(record.created_at) }}</span>
          </template>

          <template v-if="column.key === 'action'">
            <a-space>
              <a-tooltip title="Xem chi tiết">
                <a-button type="link" size="small">
                  <template #icon><EyeOutlined class="text-xs sm:text-sm" /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip
                v-if="record.role !== 'admin' && record.status === 'active'"
                title="Khóa người dùng"
              >
                <a-button
                  type="link"
                  danger
                  size="small"
                  @click="handleBlockUser(record)"
                >
                  <template #icon><LockOutlined class="text-xs sm:text-sm" /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip
                v-if="record.status === 'blocked'"
                title="Mở khóa người dùng"
              >
                <a-button
                  type="link"
                  size="small"
                  @click="handleUnblockUser(record)"
                >
                  <template #icon><UnlockOutlined class="text-xs sm:text-sm" /></template>
                </a-button>
              </a-tooltip>
            </a-space>
          </template>
        </template>
      </a-table>

      <!-- Pagination -->
      <div class="p-4 border-t border-slate-200">
        <a-pagination
          v-model:current="adminStore.userPagination.currentPage"
          :total="adminStore.userPagination.total"
          :page-size="adminStore.userPagination.perPage"
          :show-total="(total) => `Tổng ${total} người dùng`"
          show-size-changer
          @change="handlePageChange"
        />
      </div>
    </div>
  </div>
</template>
