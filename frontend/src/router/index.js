import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

const routes = [
  {
    path: "/login",
    name: "Login",
    component: () => import("../views/auth/Login.vue"),
    meta: { guest: true },
  },
  {
    path: "/register",
    name: "Register",
    component: () => import("../views/auth/Register.vue"),
    meta: { guest: true },
  },
  {
    path: "/",
    name: "Dashboard",
    component: () => import("../views/dashboard/Dashboard.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/finance",
    name: "Finance",
    component: () => import("../views/finance/Finance.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/nutrition",
    name: "Nutrition",
    component: () => import("../views/nutrition/Nutrition.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/workout",
    name: "Workout",
    component: () => import("../views/workout/Workout.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/settings",
    name: "Settings",
    component: () => import("../views/settings/Settings.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/admin/users",
    name: "AdminUsers",
    component: () => import("../views/admin/Users.vue"),
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/categories",
    name: "AdminCategories",
    component: () => import("../views/admin/Categories.vue"),
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/ingredients",
    name: "AdminIngredients",
    component: () => import("../views/admin/Ingredients.vue"),
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/chat",
    name: "Chat",
    component: () => import("../views/chat/Chat.vue"),
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    next("/login");
  } else if (to.meta.guest && auth.isAuthenticated) {
    next("/");
  } else if (to.meta.requiresAdmin && !auth.isAdmin) {
    // Redirect non-admin users trying to access admin routes
    next("/");
  } else {
    next();
  }
});

export default router;
