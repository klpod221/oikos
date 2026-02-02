<!--
  Workout.vue

  Main workout module view.
  Tabs:
  - Exercises: List of exercises
  - Routines: Manage workout routines
  - Player: Active workout session
  - History: Workout logs
-->
<script setup>
import { ref, onMounted } from "vue";
import {
  PlusOutlined,
  TrophyOutlined,
  HistoryOutlined,
  ThunderboltOutlined,
  SearchOutlined,
  CalendarOutlined,
} from "@ant-design/icons-vue";
import { useWorkoutStore } from "../../stores/workout";

// Components
import RoutineCard from "../../components/workout/RoutineCard.vue";
import RoutineModal from "../../components/workout/RoutineModal.vue";
import WorkoutPlayer from "../../components/workout/WorkoutPlayer.vue";
import ScheduleModal from "../../components/workout/ScheduleModal.vue";
import ExerciseModal from "../../components/workout/ExerciseModal.vue";
import ExerciseCard from "../../components/workout/ExerciseCard.vue";

const workout = useWorkoutStore();
const activeTab = ref("routines");

// Modal states
const routineModalOpen = ref(false);
const editingRoutine = ref(null);
const routineForm = ref({
  name: "",
  description: "",
  estimated_duration: 30,
  exercises: [],
});

// Search
const exerciseSearch = ref("");

// Schedule states
const scheduleModalOpen = ref(false);
const scheduleForm = ref({
  routine_id: null,
  name: "",
  schedule_config: { type: "weekly", days: [] },
  start_date: null,
  end_date: null,
  is_active: true,
});

// Exercise states
const exerciseModalOpen = ref(false);
const editingExercise = ref(null);
const exerciseForm = ref({
  name: "",
  description: "",
  muscle_group: "",
  type: "reps",
  calories_per_unit: 0,
  video_url: "",
});

onMounted(async () => {
  await Promise.all([
    workout.fetchExercises(),
    workout.fetchRoutines(),
    workout.fetchWorkoutLogs(),
    workout.fetchSchedules(),
  ]);
});

// Routine handlers
function openRoutineModal(routine = null) {
  const isEvent =
    routine && (routine instanceof Event || routine.type === "click");
  const targetRoutine = isEvent ? null : routine;

  editingRoutine.value = targetRoutine;
  routineForm.value = targetRoutine
    ? { ...targetRoutine }
    : { name: "", description: "", estimated_duration: 30, exercises: [] };
  routineModalOpen.value = true;
}

async function handleRoutineSubmit(data) {
  const success = editingRoutine.value
    ? await workout.updateRoutine(editingRoutine.value.id, data)
    : await workout.createRoutine(data);
  if (success) routineModalOpen.value = false;
}

async function handleRoutineDelete(id) {
  await workout.deleteRoutine(id);
}

function startRoutine(routine) {
  workout.setSelectedRoutine(routine);
}

async function handleWorkoutComplete(data) {
  const success = await workout.createWorkoutLog({
    routine_id: workout.selectedRoutine?.id,
    started_at: new Date(Date.now() - data.duration * 1000).toISOString(),
    completed_at: new Date().toISOString(),
    duration_seconds: data.duration,
    calories_burned: data.calories,
  });
  if (success) {
    workout.setSelectedRoutine(null);
    activeTab.value = "history";
  }
}

function handleWorkoutCancel() {
  workout.setSelectedRoutine(null);
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString("vi-VN", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}

function formatDuration(seconds) {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}p ${secs}s`;
}

// Schedule handlers
function openScheduleModal() {
  scheduleForm.value = {
    routine_id: null,
    name: "",
    schedule_config: { type: "weekly", days: [] },
    start_date: null,
    end_date: null,
    is_active: true,
  };
  scheduleModalOpen.value = true;
}

async function handleScheduleSubmit(data) {
  const success = await workout.createSchedule(data);
  if (success) scheduleModalOpen.value = false;
}

async function handleScheduleDelete(id) {
  await workout.deleteSchedule(id);
}

// Exercise handlers
function openExerciseModal(exercise = null) {
  editingExercise.value = exercise;
  exerciseForm.value = exercise
    ? { ...exercise }
    : {
        name: "",
        description: "",
        muscle_group: "",
        type: "reps",
        calories_per_unit: 0,
        video_url: "",
      };
  exerciseModalOpen.value = true;
}

async function handleExerciseSubmit(data) {
  const success = editingExercise.value
    ? await workout.updateExercise(editingExercise.value.id, data) // Assuming store update
    : await workout.createExercise(data); // Assuming store update
  if (success) {
    exerciseModalOpen.value = false;
    await workout.fetchExercises();
  }
}

async function handleExerciseDelete(id) {
  await workout.deleteExercise(id); // Assuming store update
  await workout.fetchExercises();
}
</script>

<template>
  <div class="space-y-2">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between sm:items-center gap-2"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Tập luyện</h1>
        <p class="text-slate-500">Quản lý bài tập và routine của bạn</p>
      </div>
    </div>

    <!-- Tabs -->
    <a-tabs v-model:activeKey="activeTab">
      <!-- Exercises Tab -->
      <a-tab-pane key="exercises">
        <template #tab>
          <span><ThunderboltOutlined /> Bài tập</span>
        </template>
        <div class="mb-4 flex flex-col sm:flex-row gap-2 justify-between">
          <a-input
            v-model:value="exerciseSearch"
            placeholder="Tìm kiếm bài tập..."
            allow-clear
            class="w-full sm:w-64"
          >
            <template #prefix>
              <SearchOutlined class="text-slate-400" />
            </template>
          </a-input>
          <a-button type="primary" @click="openExerciseModal()">
            <template #icon><PlusOutlined /></template>
            Thêm bài tập
          </a-button>
        </div>
        <div
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3"
        >
          <ExerciseCard
            v-for="exercise in workout.exercises"
            :key="exercise.id"
            :exercise="exercise"
            @edit="openExerciseModal"
            @delete="handleExerciseDelete"
          />
          <div
            v-if="workout.exercises.length === 0 && !workout.loading"
            class="col-span-full text-center py-12 text-slate-500"
          >
            <ThunderboltOutlined class="text-4xl mb-4 opacity-50" />
            <p class="text-sm">Chưa có bài tập nào</p>
          </div>
        </div>
      </a-tab-pane>

      <!-- Routines Tab -->
      <a-tab-pane key="routines">
        <template #tab>
          <span><TrophyOutlined /> Routine</span>
        </template>
        <div class="mb-4 flex flex-col sm:flex-row gap-2 justify-between">
          <div></div>
          <a-button type="primary" @click="openRoutineModal()">
            <template #icon><PlusOutlined /></template>
            Routine mới
          </a-button>
        </div>
        <div
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3"
        >
          <RoutineCard
            v-for="routine in workout.routines"
            :key="routine.id"
            :routine="routine"
            @edit="openRoutineModal"
            @delete="handleRoutineDelete"
            @start="startRoutine"
          />
          <div
            v-if="workout.routines.length === 0 && !workout.loading"
            class="col-span-full text-center py-12 text-slate-500"
          >
            <TrophyOutlined class="text-4xl mb-4 opacity-50" />
            <p class="text-sm">
              Chưa có routine nào. Hãy tạo routine đầu tiên!
            </p>
          </div>
        </div>
      </a-tab-pane>

      <!-- Player Tab Removed -->

      <!-- History Tab -->
      <a-tab-pane key="history">
        <template #tab>
          <span><HistoryOutlined /> Lịch sử</span>
        </template>
        <div
          v-if="workout.workoutLogs.length === 0"
          class="text-center py-12 text-slate-500"
        >
          <HistoryOutlined class="text-4xl mb-4 opacity-50" />
          <p class="text-sm">Chưa có lịch sử tập luyện</p>
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="log in workout.workoutLogs"
            :key="log.id"
            class="bg-white border border-slate-200 rounded-xl p-2"
          >
            <div class="flex justify-between items-start mb-2">
              <h4 class="font-medium text-slate-800">
                {{ log.routine?.name || "Bài tập tự do" }}
              </h4>
              <span class="text-xs text-slate-500">{{
                formatDate(log.started_at)
              }}</span>
            </div>
            <div class="flex gap-4 text-sm text-slate-600">
              <span>⏱️ {{ formatDuration(log.duration_seconds) }}</span>
              <span>🔥 {{ log.calories_burned }} kcal</span>
            </div>
          </div>
        </div>
      </a-tab-pane>

      <!-- Schedules Tab -->
      <a-tab-pane key="schedules">
        <template #tab>
          <span><CalendarOutlined /> Lịch tập</span>
        </template>
        <div class="mb-4 flex justify-end">
          <a-button type="primary" @click="openScheduleModal()">
            <template #icon><PlusOutlined /></template>
            Tạo lịch
          </a-button>
        </div>
        <div class="space-y-3">
          <div
            v-for="schedule in workout.schedules"
            :key="schedule.id"
            class="bg-white border border-slate-200 rounded-xl p-2"
          >
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-medium text-slate-800">
                  {{ schedule.name || schedule.routine?.name }}
                </h4>
                <p class="text-sm text-slate-500">
                  {{
                    schedule.schedule_config?.type === "weekly"
                      ? "Hàng tuần"
                      : "Theo khoảng"
                  }}
                </p>
              </div>
              <a-popconfirm
                title="Xóa lịch này?"
                @confirm="handleScheduleDelete(schedule.id)"
              >
                <a-button type="text" danger size="small">Xóa</a-button>
              </a-popconfirm>
            </div>
          </div>
          <div
            v-if="workout.schedules.length === 0"
            class="text-center py-12 text-slate-500"
          >
            <CalendarOutlined class="text-4xl mb-4 opacity-50" />
            <p class="text-sm">Chưa có lịch tập</p>
          </div>
        </div>
      </a-tab-pane>
    </a-tabs>

    <!-- Modals -->
    <RoutineModal
      v-model:open="routineModalOpen"
      v-model:form="routineForm"
      :routine="editingRoutine"
      :loading="workout.loading"
      :exercises="workout.exercises"
      @submit="handleRoutineSubmit"
    />
    <ScheduleModal
      v-model:open="scheduleModalOpen"
      v-model:form="scheduleForm"
      :loading="workout.loading"
      :routines="workout.routines"
      @submit="handleScheduleSubmit"
    />
    <ExerciseModal
      v-model:open="exerciseModalOpen"
      v-model:form="exerciseForm"
      :exercise="editingExercise"
      :loading="workout.loading"
      @submit="handleExerciseSubmit"
    />

    <!-- Player Modal -->
    <a-modal
      :open="!!workout.selectedRoutine"
      :footer="null"
      :closable="false"
      :maskClosable="false"
      :keyboard="false"
      width="100%"
      wrap-class-name="full-screen-modal"
      destroy-on-close
    >
      <div v-if="workout.selectedRoutine" class="h-full w-full bg-slate-50">
        <WorkoutPlayer
          :routine="workout.selectedRoutine"
          @complete="handleWorkoutComplete"
          @cancel="handleWorkoutCancel"
        />
      </div>
    </a-modal>
  </div>
</template>
