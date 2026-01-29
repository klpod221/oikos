<!--
  Users.vue

  Admin view for managing users.
  Features:
  - List users
  - Block/Unblock users (except admin)
  - Search by name/email
  - Filter by role/status
-->
<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useAdminStore } from "../../stores/admin";
import { useAuthStore } from "../../stores/auth";
import { message, Modal } from "ant-design-vue";
import {
  SearchOutlined,
  UserOutlined,
  LockOutlined,
  UnlockOutlined,
  EyeOutlined,
  KeyOutlined,
} from "@ant-design/icons-vue";
import { formatDate } from "../../utils/formatters";

import { debounce } from "../../utils/debounce";

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
const debouncedSearch = debounce((search, role, status) => {
  adminStore.userFilters.search = search;
  adminStore.userFilters.role = role;
  adminStore.userFilters.status = status;
  adminStore.fetchUsers(1);
}, 500);

watch([searchQuery, roleFilter, statusFilter], () => {
  debouncedSearch(searchQuery.value, roleFilter.value, statusFilter.value);
});

const paginationConfig = computed(() => ({
  current: adminStore.userPagination.currentPage,
  pageSize: adminStore.userPagination.perPage,
  total: adminStore.userPagination.total,
  showSizeChanger: true,
  showTotal: (total) => `Tổng ${total} người dùng`,
  position: ["bottomCenter"],
}));

const handleTableChange = (pagination, filters, sorter) => {
  if (sorter.field && sorter.order) {
    adminStore.userFilters.sort_by = sorter.field;
    adminStore.userFilters.sort_order =
      sorter.order === "ascend" ? "asc" : "desc";
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

// Create User Modal
const isCreateUserModalVisible = ref(false);
const createUserForm = ref({
  name: "",
  email: "",
  password: "",
  role: "user",
});

const handleOpenCreateUserModal = () => {
  createUserForm.value = {
    name: "",
    email: "",
    password: "",
    role: "user",
  };
  isCreateUserModalVisible.value = true;
};

const handleCreateUser = async () => {
  if (
    !createUserForm.value.name ||
    !createUserForm.value.email ||
    !createUserForm.value.password
  ) {
    message.error("Vui lòng điền đầy đủ thông tin");
    return;
  }

  try {
    const success = await adminStore.createUser(createUserForm.value);
    if (success) {
      message.success("Tạo người dùng thành công");
      isCreateUserModalVisible.value = false;
    } else {
      message.error(adminStore.error || "Không thể tạo người dùng");
    }
  } catch (error) {
    message.error("Lỗi khi tạo người dùng");
  }
};

// Reset Password Modal
const isResetPasswordModalVisible = ref(false);
const resetPasswordUserId = ref(null);
const newPassword = ref("");

const handleOpenResetPasswordModal = (user) => {
  resetPasswordUserId.value = user.id;
  newPassword.value = "";
  isResetPasswordModalVisible.value = true;
};

const handleResetPassword = async () => {
  if (!newPassword.value || newPassword.value.length < 8) {
    message.error("Mật khẩu phải có ít nhất 8 ký tự");
    return;
  }

  try {
    const success = await adminStore.resetUserPassword(
      resetPasswordUserId.value,
      newPassword.value,
    );
    if (success) {
      message.success("Đặt lại mật khẩu thành công");
      isResetPasswordModalVisible.value = false;
    } else {
      message.error(adminStore.error || "Không thể đặt lại mật khẩu");
    }
  } catch (error) {
    message.error("Lỗi khi đặt lại mật khẩu");
  }
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
      <div>
        <a-button type="primary" @click="handleOpenCreateUserModal">
          <template #icon><UserOutlined /></template>
          Thêm người dùng
        </a-button>
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
        :pagination="paginationConfig"
        :row-key="(record) => record.id"
        @change="handleTableChange"
        :scroll="{ x: 'max-content' }"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <div class="flex items-center gap-2">
              <a-avatar :size="32" :src="record.avatar" class="bg-blue-500">
                <template #icon v-if="!record.avatar"
                  ><UserOutlined
                /></template>
              </a-avatar>
              <span class="font-medium text-xs sm:text-sm">{{
                record.name
              }}</span>
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
            <span class="text-xs sm:text-sm">{{
              formatDate(record.created_at)
            }}</span>
          </template>

          <template v-if="column.key === 'action'">
            <a-space>
              <a-tooltip title="Xem chi tiết">
                <a-button type="link" size="small">
                  <template #icon
                    ><EyeOutlined class="text-xs sm:text-sm"
                  /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip title="Đặt lại mật khẩu">
                <a-button
                  type="link"
                  size="small"
                  @click="handleOpenResetPasswordModal(record)"
                >
                  <template #icon
                    ><KeyOutlined class="text-xs sm:text-sm"
                  /></template>
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
                  <template #icon
                    ><LockOutlined class="text-xs sm:text-sm"
                  /></template>
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
                  <template #icon
                    ><UnlockOutlined class="text-xs sm:text-sm"
                  /></template>
                </a-button>
              </a-tooltip>
            </a-space>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Create User Modal -->
    <a-modal
      v-model:visible="isCreateUserModalVisible"
      title="Thêm người dùng mới"
      @ok="handleCreateUser"
      okText="Thêm"
      cancelText="Hủy"
    >
      <a-form layout="vertical">
        <a-form-item label="Tên người dùng" required>
          <a-input v-model:value="createUserForm.name" placeholder="Nhập tên" />
        </a-form-item>
        <a-form-item label="Email" required>
          <a-input
            v-model:value="createUserForm.email"
            placeholder="Nhập email"
          />
        </a-form-item>
        <a-form-item label="Mật khẩu" required>
          <a-input-password
            v-model:value="createUserForm.password"
            placeholder="Nhập mật khẩu"
          />
        </a-form-item>
        <a-form-item label="Vai trò">
          <a-select v-model:value="createUserForm.role">
            <a-select-option value="user">User</a-select-option>
            <a-select-option value="admin">Admin</a-select-option>
          </a-select>
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- Reset Password Modal -->
    <a-modal
      v-model:visible="isResetPasswordModalVisible"
      title="Đặt lại mật khẩu"
      @ok="handleResetPassword"
      okText="Lưu"
      cancelText="Hủy"
    >
      <a-form layout="vertical">
        <a-form-item label="Mật khẩu mới" required>
          <a-input-password
            v-model:value="newPassword"
            placeholder="Nhập mật khẩu mới"
          />
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>
