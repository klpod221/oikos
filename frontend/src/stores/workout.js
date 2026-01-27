/**
 * Workout Store
 *
 * Manages workout module state:
 * - Exercises
 * - Routines
 * - Workout Logs
 * - Schedules
 */
import { defineStore } from "pinia";
import { ref } from "vue";
import { workoutService } from "../services/workout.service";
import { message } from "ant-design-vue";

export const useWorkoutStore = defineStore("workout", () => {
  // State
  const exercises = ref([]);
  const routines = ref([]);
  const workoutLogs = ref([]);
  const upcomingWorkouts = ref([]);
  const selectedRoutine = ref(null);
  const loading = ref(false);

  const logsPagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });

  // Exercise Actions
  const fetchExercises = async () => {
    loading.value = true;
    try {
      const res = await workoutService.getExercises();
      exercises.value = res.data?.data || res.data || [];
    } catch (e) {
      console.error("Failed to fetch exercises", e);
    } finally {
      loading.value = false;
    }
  };

  // Routine Actions
  const fetchRoutines = async () => {
    loading.value = true;
    try {
      const res = await workoutService.getRoutines();
      routines.value = res.data?.data || res.data || [];
    } catch (e) {
      console.error("Failed to fetch routines", e);
    } finally {
      loading.value = false;
    }
  };

  const createRoutine = async (data) => {
    try {
      await workoutService.createRoutine(data);
      message.success("Đã tạo routine");
      await fetchRoutines();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi tạo routine");
      return false;
    }
  };

  const updateRoutine = async (id, data) => {
    try {
      await workoutService.updateRoutine(id, data);
      message.success("Đã cập nhật routine");
      await fetchRoutines();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi cập nhật routine");
      return false;
    }
  };

  const deleteRoutine = async (id) => {
    try {
      await workoutService.deleteRoutine(id);
      message.success("Đã xóa routine");
      await fetchRoutines();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi xóa routine");
      return false;
    }
  };

  // Workout Log Actions
  const fetchWorkoutLogs = async (page = 1) => {
    loading.value = true;
    try {
      const res = await workoutService.getLogs({
        page,
        per_page: logsPagination.value.perPage,
      });
      workoutLogs.value = res.data?.data || [];
      if (res.data?.meta) {
        logsPagination.value = {
          currentPage: res.data.meta.current_page,
          lastPage: res.data.meta.last_page,
          perPage: res.data.meta.per_page || 15,
          total: res.data.meta.total,
        };
      }
    } catch (e) {
      console.error("Failed to fetch workout logs", e);
    } finally {
      loading.value = false;
    }
  };

  const createWorkoutLog = async (data) => {
    try {
      await workoutService.createLog(data);
      message.success("Đã ghi nhận buổi tập");
      await fetchWorkoutLogs();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi ghi nhận buổi tập");
      return false;
    }
  };

  // Upcoming Workouts
  const fetchUpcomingWorkouts = async (startDate, endDate) => {
    try {
      const res = await workoutService.getUpcoming({
        start_date: startDate,
        end_date: endDate,
      });
      upcomingWorkouts.value = res.data || [];
    } catch (e) {
      console.error("Failed to fetch upcoming workouts", e);
    }
  };

  // Set selected routine for player
  const setSelectedRoutine = (routine) => {
    selectedRoutine.value = routine;
  };

  // Schedules State
  const schedules = ref([]);

  // Schedules Actions
  const fetchSchedules = async () => {
    try {
      const res = await workoutService.getSchedules();
      schedules.value = res.data?.data || res.data || [];
    } catch (e) {
      console.error("Failed to fetch schedules", e);
    }
  };

  const createSchedule = async (data) => {
    try {
      await workoutService.createSchedule(data);
      message.success("Đã tạo lịch tập");
      await fetchSchedules();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi tạo lịch");
      return false;
    }
  };

  const deleteSchedule = async (id) => {
    try {
      await workoutService.deleteSchedule(id);
      message.success("Đã xóa lịch tập");
      await fetchSchedules();
      return true;
    } catch (e) {
      message.error(e.response?.data?.message || "Lỗi khi xóa lịch");
      return false;
    }
  };

  return {
    // State
    exercises,
    routines,
    workoutLogs,
    upcomingWorkouts,
    selectedRoutine,
    loading,
    logsPagination,
    schedules,

    // Actions
    fetchExercises,
    fetchRoutines,
    createRoutine,
    updateRoutine,
    deleteRoutine,
    fetchWorkoutLogs,
    createWorkoutLog,
    fetchUpcomingWorkouts,
    setSelectedRoutine,
    fetchSchedules,
    createSchedule,
    deleteSchedule,
  };
});
