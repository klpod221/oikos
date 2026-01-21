import { createApp } from "vue";
import { createPinia } from "pinia";
import Antd from "ant-design-vue";
import App from "./App.vue";
import router from "./router";
import { useAuthStore } from "./stores/auth";
import "./style.css";
import "ant-design-vue/dist/reset.css";

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(Antd);

// Auto-fetch user if token exists
const auth = useAuthStore();
if (auth.token) {
  auth.fetchUser();
}

app.mount("#app");
