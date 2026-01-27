<template>
  <div class="h-full w-full bg-slate-50 flex flex-col">
    <div class="flex flex-col lg:flex-row h-full">
      <!-- LEFT PANEL: Main Active State (Timer & Controls) -->
      <div
        class="w-full lg:w-7/12 bg-white flex flex-col items-center justify-center relative border-b lg:border-b-0 lg:border-r border-slate-100 p-8 lg:p-12"
      >
        <!-- Logo -->
        <div
          class="absolute top-6 left-6 flex items-center gap-3 opacity-90 hover:opacity-100 transition-opacity"
        >
          <img src="/logo.png" alt="OikOS Logo" class="h-10 w-auto" />
          <span
            class="font-bold text-slate-800 text-xl tracking-tight hidden sm:inline-block"
            >OikOS Workout</span
          >
        </div>

        <!-- Sound Test & Settings -->
        <div class="absolute top-6 right-6 flex items-center gap-2">
          <a-dropdown trigger="click">
            <a-button
              type="text"
              shape="circle"
              class="text-slate-400 hover:text-indigo-500"
            >
              <template #icon><SettingOutlined class="text-xl" /></template>
            </a-button>
            <template #overlay>
              <a-menu>
                <a-menu-item
                  key="title"
                  disabled
                  class="cursor-default font-bold text-slate-800"
                >
                  Chọn giọng đọc
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item
                  v-for="v in availableVoices"
                  :key="v.name"
                  @click="setVoice(v)"
                  :class="{
                    'bg-indigo-50 text-indigo-600': voice?.name === v.name,
                  }"
                >
                  <span>{{ v.name }} ({{ v.lang }})</span>
                </a-menu-item>
                <a-menu-item v-if="availableVoices.length === 0" disabled>
                  Không tìm thấy giọng đọc nào
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>

          <a-tooltip title="Kiểm tra âm thanh">
            <a-button
              type="text"
              shape="circle"
              @click="speak('Kiểm tra âm thanh một hai ba')"
            >
              <template #icon
                ><SoundOutlined
                  class="text-slate-400 hover:text-indigo-500 text-xl"
              /></template>
            </a-button>
          </a-tooltip>
        </div>
        <!-- Content -->
        <div
          class="flex-1 flex flex-col items-center justify-center w-full animate-fade-in relative z-10"
        >
          <!-- IDLE -->
          <div v-if="state === 'idle'" class="text-center">
            <div
              class="w-48 h-48 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-10 text-indigo-500 shadow-inner"
            >
              <ThunderboltOutlined class="text-8xl" />
            </div>
            <h3 class="text-4xl lg:text-5xl font-bold text-slate-800 mb-6">
              Sẵn sàng chưa?
            </h3>
            <a-button
              type="primary"
              size="large"
              shape="round"
              class="h-20 px-24 text-2xl shadow-indigo-300 shadow-xl"
              @click="startWorkout"
            >
              Bắt đầu ngay <RocketOutlined class="ml-2" />
            </a-button>
          </div>

          <!-- PREP -->
          <div v-else-if="state === 'prep'" class="text-center w-full">
            <a-tag
              color="warning"
              class="text-xl rounded-full border-0 bg-amber-100 text-amber-600 font-bold"
              >CHUẨN BỊ</a-tag
            >
            <!-- PREP -->
            <div class="relative mt-16 mb-16 flex flex-col items-center">
              <div
                class="absolute inset-0 bg-amber-50 rounded-full scale-110 opacity-50 animate-ping"
              ></div>
              <a-progress
                type="circle"
                :percent="(remainingSeconds / 5) * 100"
                :width="200"
                stroke-color="#f59e0b"
                :stroke-width="8"
              >
                <template #format>
                  <ThunderboltOutlined class="text-6xl text-amber-500" />
                </template>
              </a-progress>
              <div
                class="mt-8 text-center bg-white/90 backdrop-blur-sm p-4 rounded-3xl border border-slate-100 shadow-sm w-full max-w-sm"
              >
                <span
                  class="block text-6xl lg:text-[8rem] leading-none font-black text-slate-800 tabular-nums"
                  >{{ remainingSeconds }}</span
                >
                <span
                  class="block text-xl text-slate-400 font-bold uppercase tracking-widest mt-2"
                  >Giây</span
                >
              </div>
            </div>
            <h4 class="text-3xl font-bold text-slate-800 animate-bounce">
              Sắp bắt đầu...
            </h4>
          </div>

          <!-- WORK -->
          <div v-else-if="state === 'work'" class="text-center w-full">
            <a-tag
              color="error"
              class="mb-10 px-8 py-3 text-xl rounded-full border-0 bg-red-100 text-red-600 animate-pulse font-bold"
              >ĐANG TẬP</a-tag
            >

            <h3
              class="text-4xl lg:text-6xl font-bold text-slate-800 mb-8 line-clamp-2 px-8 h-32 flex items-center justify-center"
            >
              {{ currentExercise?.name }}
            </h3>

            <div class="mb-12 relative flex justify-center w-full">
              <!-- Time based -->
              <div
                v-if="currentExercise?.type === 'time'"
                class="flex flex-col items-center w-full"
              >
                <a-progress
                  type="circle"
                  :percent="
                    (remainingSeconds / currentExercise?.pivot?.target_value) *
                    100
                  "
                  :width="200"
                  stroke-color="#ef4444"
                  :stroke-width="10"
                  :status="isPaused ? 'exception' : 'active'"
                >
                  <template #format>
                    <component
                      :is="isPaused ? 'PauseOutlined' : 'ThunderboltOutlined'"
                      class="text-6xl text-red-500"
                    />
                  </template>
                </a-progress>

                <div
                  class="mt-8 text-center bg-white p-6 rounded-[2rem] border-2 border-slate-100 shadow-lg w-full max-w-sm"
                >
                  <span
                    class="block text-6xl lg:text-9xl font-black text-slate-800 tabular-nums tracking-tighter"
                    >{{ formatTime(remainingSeconds) }}</span
                  >
                  <span
                    class="block text-lg lg:text-xl text-slate-400 uppercase tracking-widest font-bold mt-2"
                    >Thời gian</span
                  >
                </div>
              </div>

              <!-- Reps based -->
              <div v-else class="flex flex-col items-center w-full">
                <div
                  class="w-[200px] h-[200px] rounded-full border-[10px] border-slate-100 flex items-center justify-center bg-white shadow-inner"
                >
                  <ThunderboltOutlined class="text-6xl text-slate-300" />
                </div>

                <div
                  class="mt-8 text-center bg-white p-6 rounded-[2rem] border-2 border-slate-100 shadow-lg w-full max-w-sm"
                >
                  <span
                    class="block text-7xl lg:text-9xl font-black text-slate-800 leading-none"
                    >{{ currentExercise?.pivot?.target_value }}</span
                  >
                  <span
                    class="block text-slate-400 text-lg lg:text-xl font-bold mt-4 uppercase tracking-widest"
                    >Lần lặp</span
                  >
                </div>
              </div>
            </div>

            <a-button
              type="primary"
              danger
              shape="round"
              size="large"
              class="h-20 w-64 text-2xl shadow-red-200 shadow-2xl hover:scale-105 transition-transform"
              @click="completeExercise"
            >
              Hoàn thành
            </a-button>
          </div>

          <!-- REST -->
          <div v-else-if="state === 'rest'" class="text-center">
            <a-tag
              color="processing"
              class="px-8 py-3 text-xl rounded-full border-0 bg-blue-100 text-blue-600 font-bold"
              >NGHỈ NGƠI</a-tag
            >

            <div class="mb-12 mt-8 relative flex flex-col items-center">
              <a-progress
                type="circle"
                :percent="
                  (remainingSeconds /
                    (currentExercise?.pivot?.rest_time || 30)) *
                  100
                "
                :width="200"
                stroke-color="#3b82f6"
                :stroke-width="8"
              >
                <template #format>
                  <div class="text-6xl">☕</div>
                </template>
              </a-progress>

              <div
                class="mt-8 text-center bg-blue-50/50 p-6 rounded-[2rem] border border-blue-100 w-full max-w-sm"
              >
                <span
                  class="block text-[8rem] lg:text-[10rem] leading-none font-black text-slate-800 tabular-nums"
                  >{{ formatTime(remainingSeconds) }}</span
                >
                <span
                  class="block text-xl text-slate-400 uppercase tracking-[0.3em] font-bold mt-4"
                  >Nghỉ</span
                >
              </div>
            </div>

            <a-button
              type="default"
              shape="round"
              size="large"
              class="h-16 px-12 text-xl border-blue-200 text-blue-600 hover:border-blue-500 hover:text-blue-500"
              @click="skipRest"
            >
              Bỏ qua nghỉ <StepForwardOutlined />
            </a-button>
          </div>

          <!-- COMPLETED -->
          <div v-else-if="state === 'completed'" class="text-center">
            <div
              class="mb-10 p-12 rounded-full bg-green-50 text-green-500 animate-bounce shadow-xl inline-block"
            >
              <TrophyOutlined class="text-8xl" />
            </div>
            <h3 class="text-5xl font-bold text-slate-800 mb-4">Hoàn thành!</h3>
            <p class="text-slate-500 mb-12 text-2xl">Bạn làm rất tốt!</p>
            <a-button
              type="primary"
              size="large"
              shape="round"
              class="h-20 w-80 text-2xl bg-green-500 hover:bg-green-600 border-0 shadow-green-200 shadow-2xl"
              @click="finishWorkout"
            >
              Kết thúc buổi tập
            </a-button>
          </div>
        </div>

        <!-- Control Footer (Pause/Stop) -->
        <div
          v-if="state !== 'idle' && state !== 'completed'"
          class="mt-auto pt-8 flex gap-8 items-center justify-center w-full"
        >
          <!-- Play/Pause Button -->
          <button
            class="h-24 w-24 rounded-full flex items-center justify-center transition-all hover:bg-slate-100 active:bg-slate-200 focus:outline-none"
            :class="isPaused ? 'text-green-500' : 'text-slate-800'"
            @click="isPaused ? resumeWorkout() : pauseWorkout()"
          >
            <component
              :is="isPaused ? CaretRightOutlined : PauseOutlined"
              class="text-5xl"
            />
          </button>

          <!-- Stop Button -->
          <button
            class="h-20 w-20 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-red-500 transition-all focus:outline-none"
            @click="stopWorkout"
          >
            <StopOutlined class="text-4xl" />
          </button>
        </div>
      </div>

      <!-- RIGHT PANEL: Context & Info (Desktop) -->
      <div
        class="hidden lg:flex w-5/12 bg-slate-50 p-10 flex-col border-l border-slate-200 overflow-hidden"
      >
        <!-- Header Info -->
        <div class="mb-10">
          <h2 class="text-3xl font-bold text-slate-800 mb-3">
            {{ routine?.name }}
          </h2>
          <p class="text-slate-500 text-lg mb-6">
            {{ routine?.description || "Routine tập luyện cá nhân" }}
          </p>
          <div class="flex items-center gap-4">
            <div class="flex-1">
              <div
                class="flex justify-between text-sm font-bold text-slate-400 uppercase tracking-wider mb-2"
              >
                <span>Tiến độ</span>
                <span>{{ Math.round(progressPercentage) }}%</span>
              </div>
              <a-progress
                :percent="progressPercentage"
                :show-info="false"
                stroke-color="#4f46e5"
                trail-color="#cbd5e1"
                :stroke-width="12"
              />
            </div>
          </div>
        </div>

        <!-- Current Stats (Mini) -->
        <div class="grid grid-cols-2 gap-6 mb-10">
          <div
            class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100"
          >
            <p
              class="text-xs text-slate-400 uppercase font-bold mb-2 tracking-wider"
            >
              Thời gian
            </p>
            <p class="text-3xl font-black text-slate-800 tabular-nums">
              {{ formatDuration(totalDuration) }}
            </p>
          </div>
          <div
            class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100"
          >
            <p
              class="text-xs text-slate-400 uppercase font-bold mb-2 tracking-wider"
            >
              Calories
            </p>
            <p class="text-3xl font-black text-slate-800 tabular-nums">
              ~{{ Math.floor(totalCalories) }}
            </p>
          </div>
        </div>

        <!-- Up Next Preview -->
        <div
          class="flex-1 bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col"
        >
          <div class="absolute top-0 left-0 w-full h-1.5 bg-indigo-500"></div>
          <h3
            class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6"
          >
            Danh sách bài tập
          </h3>

          <div class="overflow-y-auto flex-1 pr-2 custom-scroll">
            <a-steps
              direction="vertical"
              :current="currentExerciseIndex"
              size="default"
              class="custom-steps w-full"
            >
              <a-step
                v-for="(ex, index) in exercises"
                :key="ex.id"
                :status="
                  index === currentExerciseIndex
                    ? 'process'
                    : index < currentExerciseIndex
                      ? 'finish'
                      : 'wait'
                "
              >
                <template #title>
                  <span
                    class="text-lg"
                    :class="
                      index === currentExerciseIndex
                        ? 'font-bold text-slate-800'
                        : 'font-medium'
                    "
                    >{{ ex.name }}</span
                  >
                </template>
                <template #description>
                  <span class="text-sm text-slate-400">
                    {{
                      ex.type === "time"
                        ? formatDuration(ex.pivot?.target_value || 0)
                        : (ex.pivot?.target_value || 0) + " reps"
                    }}
                  </span>
                </template>
              </a-step>
            </a-steps>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import {
  ThunderboltOutlined,
  RocketOutlined,
  TrophyOutlined,
  StepForwardOutlined,
  PauseOutlined,
  CaretRightOutlined,
  StopOutlined,
} from "@ant-design/icons-vue";
import { Modal } from "ant-design-vue";

const props = defineProps({
  routine: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["complete", "cancel"]);

// State machine states
const STATES = {
  IDLE: "idle",
  PREP: "prep",
  WORK: "work",
  REST: "rest",
  COMPLETED: "completed",
};

const state = ref(STATES.IDLE);
const currentExerciseIndex = ref(-1);
const remainingSeconds = ref(0);
const isPaused = ref(false);
const totalDuration = ref(0);
const totalCalories = ref(0);
const startTime = ref(null);

const exercises = computed(() => props.routine?.exercises || []);
const currentExercise = computed(
  () => exercises.value[currentExerciseIndex.value],
);
// Removed nextExercise unused computed

const progressPercentage = computed(() => {
  if (exercises.value.length === 0) return 0;
  if (state.value === STATES.IDLE) return 0;
  if (state.value === STATES.COMPLETED) return 100;
  return (currentExerciseIndex.value / exercises.value.length) * 100;
});

// Web Worker
let timerWorker = null;
let wakeLock = null;

onMounted(() => {
  timerWorker = new Worker("/workout-timer.worker.js");
  timerWorker.onmessage = handleWorkerMessage;
});

onUnmounted(() => {
  if (timerWorker) {
    timerWorker.postMessage({ command: "stop" });
    timerWorker.terminate();
  }
  releaseWakeLock();
});

function handleWorkerMessage(e) {
  const { type, remaining } = e.data;
  if (type === "tick") {
    remainingSeconds.value = remaining;
  } else if (type === "complete") {
    handleTimerComplete();
  }
}

async function requestWakeLock() {
  try {
    if ("wakeLock" in navigator) {
      wakeLock = await navigator.wakeLock.request("screen");
    }
  } catch (err) {
    console.warn("Wake Lock error:", err);
  }
}

function releaseWakeLock() {
  if (wakeLock) {
    wakeLock.release();
    wakeLock = null;
  }
}

function startWorkout() {
  state.value = STATES.PREP;
  currentExerciseIndex.value = 0;
  startTime.value = Date.now();
  totalCalories.value = 0;
  requestWakeLock();

  remainingSeconds.value = 5;
  timerWorker.postMessage({ command: "start", seconds: 5 });
}

function handleTimerComplete() {
  if (state.value === STATES.PREP) {
    startExercise();
  } else if (state.value === STATES.WORK) {
    completeExercise();
  } else if (state.value === STATES.REST) {
    moveToNextExercise();
  }
}

function startExercise() {
  state.value = STATES.WORK;
  const exercise = currentExercise.value;

  if (exercise.type === "time") {
    const duration = exercise.pivot.target_value;
    remainingSeconds.value = duration;
    timerWorker.postMessage({ command: "start", seconds: duration });
  } else {
    remainingSeconds.value = 0;
  }
}

function completeExercise() {
  const exercise = currentExercise.value;
  const caloriesPerUnit = exercise.calories_per_unit || 0;
  const targetValue = exercise.pivot.target_value;

  if (exercise.type === "time") {
    totalCalories.value += (targetValue / 60) * caloriesPerUnit;
  } else {
    totalCalories.value += targetValue * caloriesPerUnit;
  }

  if (currentExerciseIndex.value < exercises.value.length - 1) {
    state.value = STATES.REST;
    const restTime = exercise.pivot.rest_time || 30;

    remainingSeconds.value = restTime;
    timerWorker.postMessage({ command: "start", seconds: restTime });
  } else {
    completeWorkout();
  }
}

function moveToNextExercise() {
  currentExerciseIndex.value++;
  state.value = STATES.PREP;

  remainingSeconds.value = 3;
  timerWorker.postMessage({ command: "start", seconds: 3 });
}

function skipRest() {
  timerWorker.postMessage({ command: "stop" });
  moveToNextExercise();
}

function completeWorkout() {
  state.value = STATES.COMPLETED;
  totalDuration.value = Math.floor((Date.now() - startTime.value) / 1000);
  releaseWakeLock();
}

function pauseWorkout() {
  isPaused.value = true;
  timerWorker.postMessage({ command: "pause" });
}

function resumeWorkout() {
  isPaused.value = false;
  timerWorker.postMessage({ command: "resume" });
}

function stopWorkout() {
  Modal.confirm({
    title: "Dừng tập luyện?",
    content:
      "Bạn có chắc muốn dừng buổi tập này? Tiến trình hiện tại sẽ không được lưu.",
    okText: "Dừng lại",
    okType: "danger",
    cancelText: "Tiếp tục",
    onOk() {
      state.value = STATES.IDLE;
      currentExerciseIndex.value = -1;
      timerWorker.postMessage({ command: "stop" });
      releaseWakeLock();
      emit("cancel");
    },
    onCancel() {},
  });
}

function finishWorkout() {
  emit("complete", {
    duration: totalDuration.value,
    calories: Math.round(totalCalories.value),
  });
}

function formatTime(seconds) {
  const mins = Math.floor(seconds / 60);
  const secs = Math.round(seconds % 60);
  return `${mins}:${secs.toString().padStart(2, "0")}`;
}

function formatDuration(seconds) {
  const mins = Math.floor(seconds / 60);
  const secs = Math.round(seconds % 60);
  return `${mins}p ${secs}s`;
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Custom Scrollbar for right panel */
.custom-scroll::-webkit-scrollbar {
  width: 6px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background-color: #e2e8f0;
  border-radius: 20px;
}

:deep(.ant-steps-item-title) {
  font-weight: 600 !important;
}
</style>
