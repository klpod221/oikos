<script setup>
import { DeleteOutlined } from "@ant-design/icons-vue";

defineProps({
  ingredients: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(["delete", "change"]);

const columns = [
  {
    title: "Tên",
    dataIndex: "name",
    key: "name",
    sorter: true,
  },
  {
    title: "Đơn vị",
    dataIndex: "unit",
    key: "unit",
    width: 80,
  },
  {
    title: "Calo",
    dataIndex: "calories",
    key: "calories",
    width: 100,
    sorter: true,
  },
  {
    title: "Đạm",
    dataIndex: "protein",
    key: "protein",
    width: 80,
    sorter: true,
  },
  {
    title: "Tinh bột",
    dataIndex: "carbs",
    key: "carbs",
    width: 80,
    sorter: true,
  },
  {
    title: "Béo",
    dataIndex: "fat",
    key: "fat",
    width: 80,
    sorter: true,
  },
  {
    title: "",
    key: "actions",
    width: 60,
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
    :pagination="false"
    row-key="id"
    @change="handleTableChange"
    :scroll="{ x: 'max-content' }"
    size="small"
  >
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'calories'">
        <span class="text-xs sm:text-sm">{{ record.calories }} kcal</span>
      </template>
      <template v-if="column.key === 'protein'">
        <span class="text-xs sm:text-sm">{{ record.protein }}g</span>
      </template>
      <template v-if="column.key === 'carbs'">
        <span class="text-xs sm:text-sm">{{ record.carbs }}g</span>
      </template>
      <template v-if="column.key === 'fat'">
        <span class="text-xs sm:text-sm">{{ record.fat }}g</span>
      </template>
      <template v-if="column.key === 'actions'">
        <a-popconfirm title="Xóa?" @confirm="$emit('delete', record.id)">
          <a-button type="text" danger size="small">
            <DeleteOutlined class="text-xs sm:text-sm" />
          </a-button>
        </a-popconfirm>
      </template>
    </template>
  </a-table>
</template>
