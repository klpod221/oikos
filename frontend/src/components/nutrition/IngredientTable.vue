<script setup>
import { DeleteOutlined } from "@ant-design/icons-vue";

defineProps({
  ingredients: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

defineEmits(["delete"]);
</script>

<template>
  <a-table
    :data-source="ingredients"
    :loading="loading"
    :pagination="{ pageSize: 10 }"
    row-key="id"
  >
    <a-table-column title="Name" data-index="name" key="name" />
    <a-table-column title="Unit" data-index="unit" key="unit" width="80" />
    <a-table-column title="Calories" key="calories" width="100">
      <template #default="{ record }">{{ record.calories }} kcal</template>
    </a-table-column>
    <a-table-column title="Protein" key="protein" width="80">
      <template #default="{ record }">{{ record.protein }}g</template>
    </a-table-column>
    <a-table-column title="Carbs" key="carbs" width="80">
      <template #default="{ record }">{{ record.carbs }}g</template>
    </a-table-column>
    <a-table-column title="Fat" key="fat" width="80">
      <template #default="{ record }">{{ record.fat }}g</template>
    </a-table-column>
    <a-table-column title="" key="actions" width="60">
      <template #default="{ record }">
        <a-popconfirm title="Delete?" @confirm="$emit('delete', record.id)">
          <a-button type="text" danger size="small"
            ><DeleteOutlined
          /></a-button>
        </a-popconfirm>
      </template>
    </a-table-column>
  </a-table>
</template>
