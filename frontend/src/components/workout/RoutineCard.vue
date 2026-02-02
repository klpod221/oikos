<!--
  RoutineCard.vue

  Displays a workout routine summary card with actions.
  Props:
    - routine: Routine object
  Events:
    - edit: Emitted when edit selected
    - delete: Emitted when delete confirmed
    - start: Emitted when start workout is clicked
-->
<script setup>
import {
  EditOutlined,
  DeleteOutlined,
  TrophyOutlined,
  PlayCircleOutlined,
} from "@ant-design/icons-vue";

defineProps({
  routine: { type: Object, required: true },
});

defineEmits(["edit", "delete", "start"]);
</script>

<template>
  <div
    class="bg-white border border-slate-200 rounded-xl p-3 sm:p-2 lg:p-5 hover:shadow-md transition-shadow cursor-pointer group"
    @click="$emit('start', routine)"
  >
    <div class="flex items-start justify-between mb-2 sm:mb-3">
      <div
        class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-orange-100 flex items-center justify-center"
      >
        <TrophyOutlined class="text-orange-600 text-lg sm:text-xl" />
      </div>
      <a-dropdown>
        <a-button
          type="text"
          size="small"
          @click.stop
          class="opacity-0 group-hover:opacity-100 transition-opacity"
          >•••</a-button
        >
        <template #overlay>
          <a-menu>
            <a-menu-item @click.stop="$emit('edit', routine)">
              <EditOutlined class="mr-2" /> Sửa
            </a-menu-item>
            <a-menu-item class="text-red-500!">
              <a-popconfirm
                title="Xóa routine này?"
                @confirm="$emit('delete', routine.id)"
              >
                <DeleteOutlined class="mr-2" /> Xóa
              </a-popconfirm>
            </a-menu-item>
          </a-menu>
        </template>
      </a-dropdown>
    </div>
    <h3 class="font-semibold text-slate-800 text-sm sm:text-base">
      {{ routine.name }}
    </h3>
    <p class="text-xs sm:text-sm text-slate-500 mb-2 sm:mb-3 line-clamp-2">
      {{ routine.description || "Không có mô tả" }}
    </p>
    <div class="flex gap-2 text-xs sm:text-sm text-slate-500">
      <span>{{ routine.exercises?.length || 0 }} bài tập</span>
      <span>• {{ routine.estimated_duration || 0 }} phút</span>
    </div>
    <div class="mt-3 pt-3 border-t border-slate-100">
      <a-button
        type="primary"
        size="small"
        block
        @click.stop="$emit('start', routine)"
      >
        <PlayCircleOutlined /> Bắt đầu tập
      </a-button>
    </div>
  </div>
</template>
