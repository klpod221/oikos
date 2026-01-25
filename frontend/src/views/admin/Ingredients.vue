<!--
  Ingredients.vue

  Admin view for managing global ingredients.
  Features:
  - List ingredients with nutrition info
  - Create/Edit/Delete ingredients
  - Search by name
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
  CoffeeOutlined,
} from "@ant-design/icons-vue";
import { formatDate } from "../../utils/formatters";

import { debounce } from "../../utils/debounce";

const adminStore = useAdminStore();

const searchQuery = ref("");
const showModal = ref(false);
const modalMode = ref("create"); // 'create' or 'edit'
const currentIngredient = ref(null);

// Form data
const formData = ref({
  name: "",
  calories: 0,
  protein: 0,
  carbs: 0,
  fat: 0,
  unit: "g",
});

const columns = [
  {
    title: "ID",
    dataIndex: "id",
    key: "id",
    width: 80,
  },
  {
    title: "Tên nguyên liệu",
    dataIndex: "name",
    key: "name",
    sorter: true,
  },
  {
    title: "Calories",
    dataIndex: "calories",
    key: "calories",
    width: 150,
    sorter: true,
  },
  {
    title: "Protein (g)",
    dataIndex: "protein",
    key: "protein",
    width: 120,
    sorter: true,
  },
  {
    title: "Carbs (g)",
    dataIndex: "carbs",
    key: "carbs",
    width: 120,
    sorter: true,
  },
  {
    title: "Fat (g)",
    dataIndex: "fat",
    key: "fat",
    width: 100,
    sorter: true,
  },
  {
    title: "Đơn vị",
    dataIndex: "unit",
    key: "unit",
    width: 100,
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

// Fetch ingredients on mount
onMounted(() => {
  adminStore.fetchIngredients();
});

// Watch filters and trigger search
const debouncedSearch = debounce((query) => {
  adminStore.ingredientFilters.search = query;
  adminStore.fetchIngredients(1);
}, 500);

watch([searchQuery], () => {
  debouncedSearch(searchQuery.value);
});

const paginationConfig = computed(() => ({
  current: adminStore.ingredientPagination.currentPage,
  pageSize: adminStore.ingredientPagination.perPage,
  total: adminStore.ingredientPagination.total,
  showSizeChanger: true,
  showTotal: (total) => `Tổng ${total} nguyên liệu`,
  position: ["bottomCenter"],
}));

const handleTableChange = (pagination, filters, sorter) => {
  if (sorter.field && sorter.order) {
    adminStore.ingredientFilters.sort_by = sorter.field;
    adminStore.ingredientFilters.sort_order = sorter.order === "ascend" ? "asc" : "desc";
  } else {
    adminStore.ingredientFilters.sort_by = "";
    adminStore.ingredientFilters.sort_order = "desc";
  }
  adminStore.fetchIngredients(pagination.current);
};

const openCreateModal = () => {
  modalMode.value = "create";
  currentIngredient.value = null;
  formData.value = {
    name: "",
    calories: 0,
    protein: 0,
    carbs: 0,
    fat: 0,
    unit: "g",
  };
  showModal.value = true;
};

const openEditModal = (ingredient) => {
  modalMode.value = "edit";
  currentIngredient.value = ingredient;
  formData.value = {
    name: ingredient.name,
    calories: ingredient.calories,
    protein: ingredient.protein,
    carbs: ingredient.carbs,
    fat: ingredient.fat,
    unit: ingredient.unit,
  };
  showModal.value = true;
};

const handleSubmit = async () => {
  if (!formData.value.name) {
    message.error("Vui lòng nhập tên nguyên liệu");
    return;
  }

  let success = false;
  if (modalMode.value === "create") {
    success = await adminStore.createIngredient(formData.value);
    if (success) {
      message.success("Tạo nguyên liệu thành công");
    }
  } else {
    success = await adminStore.updateIngredient(
      currentIngredient.value.id,
      formData.value,
    );
    if (success) {
      message.success("Cập nhật nguyên liệu thành công");
    }
  }

  if (success) {
    showModal.value = false;
  } else {
    message.error(adminStore.error || "Có lỗi xảy ra");
  }
};

const handleDelete = (ingredient) => {
  Modal.confirm({
    title: "Xác nhận xóa nguyên liệu",
    content: `Bạn có chắc chắn muốn xóa nguyên liệu "${ingredient.name}"? Hành động này không thể hoàn tác.`,
    okText: "Xóa",
    cancelText: "Hủy",
    okType: "danger",
    async onOk() {
      const success = await adminStore.deleteIngredient(ingredient.id);
      if (success) {
        message.success("Đã xóa nguyên liệu thành công");
      } else {
        message.error(
          adminStore.error ||
            "Không thể xóa nguyên liệu. Có thể nguyên liệu đang được sử dụng.",
        );
      }
    },
  });
};
</script>

<template>
  <div class="space-y-2">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-2"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Quản lý nguyên liệu</h1>
        <p class="text-slate-500">
          Quản lý nguyên liệu toàn cục cho dinh dưỡng
        </p>
      </div>
      <a-button type="primary" @click="openCreateModal">
        <template #icon><PlusOutlined /></template>
        Tạo nguyên liệu mới
      </a-button>
    </div>

    <!-- Filters -->
    <a-row :gutter="16">
      <a-col :xs="24" :sm="12" :md="8">
        <a-input
          v-model:value="searchQuery"
          placeholder="Tìm kiếm theo tên nguyên liệu..."
          allow-clear
        >
          <template #prefix>
            <SearchOutlined class="text-slate-400" />
          </template>
        </a-input>
      </a-col>
    </a-row>

    <!-- Ingredients Table -->
    <div class="bg-white border border-slate-200 rounded-xl">
      <a-table
        :columns="columns"
        :data-source="adminStore.ingredients"
        :loading="adminStore.loading"
        :pagination="paginationConfig"
        :row-key="(record) => record.id"
        :scroll="{ x: 'max-content' }"
        size="small"
        @change="handleTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <div class="flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white"
              >
                <CoffeeOutlined />
              </div>
              <span class="font-medium text-xs sm:text-sm">{{ record.name }}</span>
            </div>
          </template>

          <template v-if="column.key === 'calories'">
            <span class="font-mono text-xs sm:text-sm">{{ record.calories }} kcal</span>
          </template>

          <template v-if="column.key === 'protein'">
            <span class="font-mono text-xs sm:text-sm">{{ record.protein }}g</span>
          </template>

          <template v-if="column.key === 'carbs'">
            <span class="font-mono text-xs sm:text-sm">{{ record.carbs }}g</span>
          </template>

          <template v-if="column.key === 'fat'">
            <span class="font-mono text-xs sm:text-sm">{{ record.fat }}g</span>
          </template>

          <template v-if="column.key === 'unit'">
            <a-tag class="text-xs">{{ record.unit }}</a-tag>
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
      :title="
        modalMode === 'create' ? 'Tạo nguyên liệu mới' : 'Sửa nguyên liệu'
      "
      @ok="handleSubmit"
      :confirm-loading="adminStore.loading"
      width="600px"
    >
      <a-form layout="vertical" class="mt-4">
        <a-form-item label="Tên nguyên liệu" required>
          <a-input
            v-model:value="formData.name"
            placeholder="Nhập tên nguyên liệu"
          />
        </a-form-item>

        <a-row :gutter="16">
          <a-col :span="12">
            <a-form-item label="Calories (kcal)" required>
              <a-input-number
                v-model:value="formData.calories"
                :min="0"
                :step="1"
                class="w-full"
                placeholder="0"
              />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Đơn vị" required>
              <a-select v-model:value="formData.unit" class="w-full">
                <a-select-option value="g">Gram (g)</a-select-option>
                <a-select-option value="ml">Milliliter (ml)</a-select-option>
                <a-select-option value="serving">Khẩu phần</a-select-option>
                <a-select-option value="piece">Miếng</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :span="8">
            <a-form-item label="Protein (g)" required>
              <a-input-number
                v-model:value="formData.protein"
                :min="0"
                :step="0.1"
                class="w-full"
                placeholder="0"
              />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Carbs (g)" required>
              <a-input-number
                v-model:value="formData.carbs"
                :min="0"
                :step="0.1"
                class="w-full"
                placeholder="0"
              />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Fat (g)" required>
              <a-input-number
                v-model:value="formData.fat"
                :min="0"
                :step="0.1"
                class="w-full"
                placeholder="0"
              />
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
    </a-modal>
  </div>
</template>
