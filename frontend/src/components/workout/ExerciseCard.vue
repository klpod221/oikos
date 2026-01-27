<!--
  ExerciseCard.vue

  Displays an exercise summary card with actions.
  Props:
    - exercise: Exercise object
  Events:
    - edit: Emitted when edit selected
    - delete: Emitted when delete confirmed
-->
<script setup>
import {
  EditOutlined,
  DeleteOutlined,
  ThunderboltOutlined,
  MoreOutlined,
} from "@ant-design/icons-vue";

defineProps({
  exercise: { type: Object, required: true },
});

defineEmits(["edit", "delete"]);
</script>

<template>
  <div
    class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 lg:p-5 hover:shadow-md transition-shadow cursor-pointer group flex flex-col h-full"
    @click="$emit('edit', exercise)"
  >
    <div class="flex items-start justify-between mb-2 sm:mb-3">
      <div
        class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500"
      >
        <ThunderboltOutlined class="text-lg sm:text-xl" />
      </div>

      <a-dropdown>
        <a-button
          type="text"
          size="small"
          class="opacity-0 group-hover:opacity-100 transition-opacity text-slate-400"
          @click.stop
        >
          <template #icon><MoreOutlined class="text-lg" /></template>
        </a-button>
        <template #overlay>
          <a-menu>
            <a-menu-item @click.stop="$emit('edit', exercise)">
              <EditOutlined class="mr-2" />
              {{ exercise.is_global ? "Xem chi tiết" : "Chỉnh sửa" }}
            </a-menu-item>
            <a-menu-item class="text-red-500!">
              <a-popconfirm
                title="Xóa bài tập này?"
                @confirm="$emit('delete', exercise.id)"
              >
                <div class="w-full h-full text-red-500">
                  <DeleteOutlined class="mr-2" /> Xóa
                </div>
              </a-popconfirm>
            </a-menu-item>
          </a-menu>
        </template>
      </a-dropdown>
    </div>

    <h3 class="font-semibold text-slate-800 text-sm sm:text-base mb-1">
      {{ exercise.name }}
    </h3>
    <p
      class="text-xs sm:text-sm text-slate-500 mb-3 line-clamp-2 min-h-[2.5em]"
    >
      {{ exercise.description || "Không có mô tả" }}
    </p>

    <div class="flex flex-wrap gap-2 mb-3 mt-auto">
      <a-tag color="blue" :bordered="false">{{ exercise.muscle_group }}</a-tag>
      <a-tag v-if="exercise.type === 'time'" color="orange" :bordered="false"
        >Time</a-tag
      >
      <a-tag v-else color="cyan" :bordered="false">Reps</a-tag>
    </div>

    <div class="mt-3 pt-3 border-t border-slate-100">
      <a-button
        v-if="!exercise.is_global"
        type="default"
        size="small"
        block
        class="text-slate-600 border-slate-200 hover:border-indigo-500 hover:text-indigo-500"
        @click.stop="$emit('edit', exercise)"
      >
        <EditOutlined /> Chỉnh sửa
      </a-button>
      <a-button
        v-else
        type="dashed"
        size="small"
        block
        class="text-slate-400"
        disabled
      >
        Mặc định
      </a-button>
    </div>
  </div>
</template>
