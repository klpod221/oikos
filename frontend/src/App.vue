<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import DefaultLayout from "./layouts/DefaultLayout.vue";
import AuthLayout from "./layouts/AuthLayout.vue";

const route = useRoute();
const layout = computed(() => (route.meta.guest ? AuthLayout : DefaultLayout));
</script>

<template>
  <component :is="layout">
    <router-view />
  </component>
</template>
