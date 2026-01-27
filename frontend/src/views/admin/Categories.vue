<!--
  Categories.vue

  Admin view for managing system categories.
  Features:
  - List categories (income/expense)
  - Create/Edit/Delete categories
  - Filter by type and search by name
-->
<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useAdminStore } from "../../stores/admin";
import { message, Modal } from "ant-design-vue";
import {
  SearchOutlined,
  PlusOutlined,
  EditOutlined,
  DeleteOutlined,
  TagsOutlined,
} from "@ant-design/icons-vue";
import { formatDate } from "../../utils/formatters";

import { debounce } from "../../utils/debounce";

const adminStore = useAdminStore();

const searchQuery = ref("");
const typeFilter = ref("");
const showModal = ref(false);
const modalMode = ref("create"); // 'create' or 'edit'
const currentCategory = ref(null);

// Form data
const formData = ref({
  name: "",
  type: "expense",
  icon: "",
  color: "#3b82f6",
});

const columns = [
  {
    title: "ID",
    dataIndex: "id",
    key: "id",
    width: 80,
  },
  {
    title: "Tên danh mục",
    dataIndex: "name",
    key: "name",
    sorter: true,
  },
  {
    title: "Loại",
    dataIndex: "type",
    key: "type",
    width: 150,
    sorter: true,
  },
  {
    title: "Icon",
    dataIndex: "icon",
    key: "icon",
    width: 100,
  },
  {
    title: "Màu sắc",
    dataIndex: "color",
    key: "color",
    width: 120,
  },
  {
    title: "Ngày tạo",
    dataIndex: "created_at",
    key: "created_at",
    width: 150,
  },
  {
    title: "Thao tác",
    key: "action",
    width: 150,
  },
];

// Fetch categories on mount
onMounted(() => {
  adminStore.fetchCategories();
});

// Watch filters and trigger search
const debouncedSearch = debounce((search, type) => {
  adminStore.categoryFilters.search = search;
  adminStore.categoryFilters.type = type;
  adminStore.fetchCategories(1);
}, 500);

watch([searchQuery, typeFilter], () => {
  debouncedSearch(searchQuery.value, typeFilter.value);
});

const paginationConfig = computed(() => ({
  current: adminStore.categoryPagination.currentPage,
  pageSize: adminStore.categoryPagination.perPage,
  total: adminStore.categoryPagination.total,
  showSizeChanger: true,
  showTotal: (total) => `Tổng ${total} danh mục`,
  position: ["bottomCenter"],
}));

const handleTableChange = (pagination, filters, sorter) => {
  if (sorter.field && sorter.order) {
    adminStore.categoryFilters.sort_by = sorter.field;
    adminStore.categoryFilters.sort_order = sorter.order === "ascend" ? "asc" : "desc";
  } else {
    adminStore.categoryFilters.sort_by = "";
    adminStore.categoryFilters.sort_order = "desc";
  }
  adminStore.fetchCategories(pagination.current);
};

const openCreateModal = () => {
  modalMode.value = "create";
  currentCategory.value = null;
  formData.value = {
    name: "",
    type: "expense",
    icon: "",
    color: "#3b82f6",
  };
  showModal.value = true;
};

const openEditModal = (category) => {
  modalMode.value = "edit";
  currentCategory.value = category;
  formData.value = {
    name: category.name,
    type: category.type,
    icon: category.icon,
    color: category.color,
  };
  showModal.value = true;
};

const handleSubmit = async () => {
  if (!formData.value.name) {
    message.error("Vui lòng nhập tên danh mục");
    return;
  }

  let success = false;
  if (modalMode.value === "create") {
    success = await adminStore.createCategory(formData.value);
    if (success) {
      message.success("Tạo danh mục thành công");
    }
  } else {
    success = await adminStore.updateCategory(
      currentCategory.value.id,
      formData.value,
    );
    if (success) {
      message.success("Cập nhật danh mục thành công");
    }
  }

  if (success) {
    showModal.value = false;
  } else {
    message.error(adminStore.error || "Có lỗi xảy ra");
  }
};

const handleDelete = (category) => {
  Modal.confirm({
    title: "Xác nhận xóa danh mục",
    content: `Bạn có chắc chắn muốn xóa danh mục "${category.name}"? Hành động này không thể hoàn tác.`,
    okText: "Xóa",
    cancelText: "Hủy",
    okType: "danger",
    async onOk() {
      const success = await adminStore.deleteCategory(category.id);
      if (success) {
        message.success("Đã xóa danh mục thành công");
      } else {
        message.error(
          adminStore.error ||
            "Không thể xóa danh mục. Có thể danh mục đang được sử dụng.",
        );
      }
    },
  });
};

const getTypeColor = (type) => {
  return type === "income" ? "green" : "red";
};

const getTypeText = (type) => {
  return type === "income" ? "Thu nhập" : "Chi tiêu";
};
</script>

<template>
  <div class="space-y-2">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-2"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Quản lý danh mục</h1>
        <p class="text-slate-500">Quản lý danh mục hệ thống cho tài chính</p>
      </div>
      <a-button type="primary" @click="openCreateModal">
        <template #icon><PlusOutlined /></template>
        Tạo danh mục mới
      </a-button>
    </div>

    <!-- Filters -->
    <a-row :gutter="16">
      <a-col :xs="24" :sm="12" :md="8">
        <a-input
          v-model:value="searchQuery"
          placeholder="Tìm kiếm theo tên danh mục..."
          allow-clear
        >
          <template #prefix>
            <SearchOutlined class="text-slate-400" />
          </template>
        </a-input>
      </a-col>
      <a-col :xs="24" :sm="12" :md="4">
        <a-select
          v-model:value="typeFilter"
          placeholder="Loại"
          allow-clear
          class="w-full"
        >
          <a-select-option value="">Tất cả</a-select-option>
          <a-select-option value="income">Thu nhập</a-select-option>
          <a-select-option value="expense">Chi tiêu</a-select-option>
        </a-select>
      </a-col>
    </a-row>

    <!-- Categories Table -->
    <div class="bg-white border border-slate-200 rounded-xl">
      <a-table
        :columns="columns"
        :data-source="adminStore.categories"
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
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-white"
                :style="{ backgroundColor: record.color }"
              >
                <TagsOutlined v-if="!record.icon" />
                <span v-else>{{ record.icon }}</span>
              </div>
              <span class="font-medium text-xs sm:text-sm">{{ record.name }}</span>
            </div>
          </template>

          <template v-if="column.key === 'type'">
            <a-tag :color="getTypeColor(record.type)" class="text-xs">
              {{ getTypeText(record.type) }}
            </a-tag>
          </template>

          <template v-if="column.key === 'icon'">
            <span class="text-2xl">{{ record.icon || "-" }}</span>
          </template>

          <template v-if="column.key === 'color'">
            <div class="flex items-center gap-2">
              <div
                class="w-6 h-6 rounded border border-slate-300"
                :style="{ backgroundColor: record.color }"
              ></div>
              <span class="text-sm text-slate-600">{{ record.color }}</span>
            </div>
          </template>

          <template v-if="column.key === 'created_at'">
            <span class="text-xs sm:text-sm">{{ formatDate(record.created_at) }}</span>
          </template>

          <template v-if="column.key === 'action'">
            <a-space>
              <a-tooltip title="Sửa">
                <a-button
                  type="link"
                  size="small"
                  @click="openEditModal(record)"
                >
                  <template #icon><EditOutlined class="text-xs sm:text-sm" /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip title="Xóa">
                <a-button
                  type="link"
                  danger
                  size="small"
                  @click="handleDelete(record)"
                >
                  <template #icon><DeleteOutlined class="text-xs sm:text-sm" /></template>
                </a-button>
              </a-tooltip>
            </a-space>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Create/Edit Modal -->
    <a-modal
      v-model:open="showModal"
      :title="modalMode === 'create' ? 'Tạo danh mục mới' : 'Sửa danh mục'"
      @ok="handleSubmit"
      :confirm-loading="adminStore.loading"
    >
      <a-form layout="vertical" class="mt-4">
        <a-form-item label="Tên danh mục" required>
          <a-input
            v-model:value="formData.name"
            placeholder="Nhập tên danh mục"
          />
        </a-form-item>

        <a-form-item label="Loại" required>
          <a-select v-model:value="formData.type" class="w-full">
            <a-select-option value="income">Thu nhập</a-select-option>
            <a-select-option value="expense">Chi tiêu</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Icon (emoji)">
          <a-input
            v-model:value="formData.icon"
            placeholder="Ví dụ: 🍔, 💰, 🏠"
            maxlength="2"
          />
        </a-form-item>

        <a-form-item label="Màu sắc">
          <div class="flex items-center gap-2">
            <input
              v-model="formData.color"
              type="color"
              class="w-12 h-10 border border-slate-300 rounded cursor-pointer"
            />
            <a-input v-model:value="formData.color" placeholder="#3b82f6" />
          </div>
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>
