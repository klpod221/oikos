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
  } else {
    next();
  }
});

export default router;
