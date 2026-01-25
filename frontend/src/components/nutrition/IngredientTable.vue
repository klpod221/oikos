<!--
  IngredientTable.vue

  Displays a table of ingredients with sorting and actions.
  Styled to match Admin Interface.
  
  Props:
    - ingredients: Array of ingredient objects
    - loading: Loading state
  Events:
    - change: Emitted on table change (pagination, sorting)
    - delete: Emitted when delete confirmed
    - edit: Emitted when edit clicked
-->
<script setup>
import { 
  DeleteOutlined, 
  EditOutlined, 
  ExperimentOutlined 
} from "@ant-design/icons-vue";
import { useAuthStore } from "../../stores/auth";
import { formatDate } from "../../utils/formatters"; // Assuming this exists as seen in Users.vue

const props = defineProps({
  ingredients: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  pagination: { type: Object, default: false },
});

const emit = defineEmits(["delete", "change", "edit"]);
const authStore = useAuthStore();

const columns = [
  {
    title: "ID",
    dataIndex: "id",
    key: "id",
    width: 60,
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
    width: 80,
    align: 'center',
  },
  {
    title: "Ngày tạo",
    dataIndex: "created_at",
    key: "created_at",
    width: 120,
    sorter: true,
  },
  {
    title: "Thao tác",
    key: "actions",
    width: 100,
    align: 'right',
  },
];

const handleTableChange = (pagination, filters, sorter) => {
  emit("change", pagination, filters, sorter);
};
</script>

<template>
  <a-table
    :columns="columns"
    :data-source="ingredients"
    :loading="loading"
    :pagination="pagination"
    row-key="id"
    @change="handleTableChange"
    :scroll="{ x: 'max-content' }"
    size="middle" 
  >
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'id'">
        <span class="text-slate-500">#{{ record.id }}</span>
      </template>

      <template v-if="column.key === 'name'">
        <div class="flex items-center gap-3">
          <a-avatar :size="32" class="bg-green-100 flex items-center justify-center text-green-600">
            <template #icon><ExperimentOutlined /></template>
          </a-avatar>
          <span class="font-medium text-slate-700">{{ record.name }}</span>
        </div>
      </template>

      <template v-if="column.key === 'calories'">
        <span class="font-semibold text-slate-700">{{ record.calories }} kcal</span>
      </template>
      
      <template v-if="column.key === 'protein'">
        <span class="text-slate-600">{{ record.protein }}g</span>
      </template>
      
      <template v-if="column.key === 'carbs'">
        <span class="text-slate-600">{{ record.carbs }}g</span>
      </template>
      
      <template v-if="column.key === 'fat'">
        <span class="text-slate-600">{{ record.fat }}g</span>
      </template>

      <template v-if="column.key === 'unit'">
        <a-tag class="m-0 bg-slate-100 border-slate-200 text-slate-500 font-medium">
            {{ record.unit }}
        </a-tag>
      </template>

      <template v-if="column.key === 'created_at'">
        <span class="text-slate-500 text-xs">{{ formatDate(record.created_at) }}</span>
      </template>

      <template v-if="column.key === 'actions'">
          <div class="flex gap-2 justify-end">
            <template v-if="!record.is_global || authStore.user?.role === 'admin'">
            <a-tooltip title="Sửa">
              <a-button type="link" size="small" class="!text-blue-500 !p-0" @click="$emit('edit', record)">
                  <template #icon><EditOutlined /></template>
              </a-button>
            </a-tooltip>
            <a-popconfirm title="Xóa?" @confirm="$emit('delete', record.id)">
              <a-tooltip title="Xóa">
                  <a-button type="link" danger size="small" class="!p-0">
                      <template #icon><DeleteOutlined /></template>
                  </a-button>
              </a-tooltip>
            </a-popconfirm>
            </template>
            <template v-else>
              <a-tag color="blue" class="m-0 text-[10px]">Global</a-tag>
            </template>
        </div>
      </template>
    </template>
  </a-table>
</template>
