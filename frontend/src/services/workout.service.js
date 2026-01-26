import api from "../utils/axios";

/**
 * Workout Service
 *
 * API endpoints for workout module.
 */
export const workoutService = {
  // Exercises
  getExercises: (params = {}) => api.get("/workout/exercises", { params }),
  getExercise: (id) => api.get(`/workout/exercises/${id}`),
  createExercise: (data) => api.post("/workout/exercises", data),
  updateExercise: (id, data) => api.put(`/workout/exercises/${id}`, data),
  deleteExercise: (id) => api.delete(`/workout/exercises/${id}`),

  // Routines
  getRoutines: (params = {}) => api.get("/workout/routines", { params }),
  getRoutine: (id) => api.get(`/workout/routines/${id}`),
  createRoutine: (data) => api.post("/workout/routines", data),
  updateRoutine: (id, data) => api.put(`/workout/routines/${id}`, data),
  deleteRoutine: (id) => api.delete(`/workout/routines/${id}`),

  // Schedules
  getSchedules: (params = {}) => api.get("/workout/schedules", { params }),
  createSchedule: (data) => api.post("/workout/schedules", data),
  deleteSchedule: (id) => api.delete(`/workout/schedules/${id}`),

  // Workout Logs
  getLogs: (params = {}) => api.get("/workout/logs", { params }),
  createLog: (data) => api.post("/workout/logs", data),

  // Upcoming Workouts
  getUpcoming: (params = {}) => api.get("/workout/upcoming", { params }),
};
