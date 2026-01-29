<script setup>
import { ref, onMounted } from "vue";
import { adminSettingService } from "../../services/admin.service";
import { message } from "ant-design-vue";
import { SaveOutlined } from "@ant-design/icons-vue";

const loading = ref(false);
const settings = ref([]);

// Local state for form binding
const formState = ref({});

const fetchSettings = async () => {
  loading.value = true;
  try {
    const response = await adminSettingService.getSettings();
    settings.value = response.data;

    // Map settings to formState
    settings.value.forEach((setting) => {
      // Convert 'true'/'false' strings to boolean if type is boolean
      let val = setting.value;
      if (setting.type === "boolean") {
        val = val === "true" || val === true;
      }
      formState.value[setting.key] = val;
    });
  } catch (error) {
    console.error(error);
    message.error("Không thể tải cài đặt hệ thống");
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  loading.value = true;
  try {
    // Transform formState back to API format
    const payload = Object.keys(formState.value).map((key) => {
      let val = formState.value[key];
      // Convert boolean back to 'true'/'false' string for consistency if needed,
      // but backend handles string casting. Let's keep it simple.
      return {
        key: key,
        value: val,
      };
    });

    console.log("Saving Settings Payload:", { settings: payload });

    await adminSettingService.updateSettings({ settings: payload });
    message.success("Lưu cài đặt thành công");
    await fetchSettings(); // Refresh
  } catch (error) {
    console.error(error);
    message.error("Lỗi khi lưu cài đặt");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>

<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Cài đặt hệ thống</h1>
        <p class="text-slate-500">
          Quản lý cấu hình toàn hệ thống (Cẩn thận khi thay đổi)
        </p>
      </div>
      <a-button type="primary" :loading="loading" @click="saveSettings">
        <template #icon><SaveOutlined /></template>
        Lưu thay đổi
      </a-button>
    </div>

    <div v-if="loading && settings.length === 0" class="text-center py-10">
      <a-spin />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Access Control -->
      <a-card title="🔐 Truy cập & Đăng ký" class="shadow-sm rounded-xl">
        <a-form layout="vertical">
          <a-form-item
            label="Cho phép đăng ký"
            help="Bật/Tắt tính năng đăng ký thành viên mới"
          >
            <a-switch
              v-model:checked="formState['allow_registration']"
              checked-children="Bật"
              un-checked-children="Tắt"
            />
          </a-form-item>

          <a-form-item
            label="Yêu cầu xác thực Email"
            help="User mới phải xác thực email trước khi đăng nhập"
          >
            <a-switch
              v-model:checked="formState['require_email_verification']"
              checked-children="Bật"
              un-checked-children="Tắt"
            />
          </a-form-item>

          <a-form-item
            label="Vai trò mặc định"
            help="Vai trò được gán cho user mới đăng ký"
          >
            <a-select v-model:value="formState['default_user_role']">
              <a-select-option value="user">User (Thành viên)</a-select-option>
              <a-select-option value="guest">Guest (Khách)</a-select-option>
              <a-select-option value="admin"
                >Admin (Quản trị viên - Cẩn thận)</a-select-option
              >
            </a-select>
          </a-form-item>
        </a-form>
      </a-card>

      <!-- System & Features -->
      <a-card title="⚙️ Hệ thống & Tính năng" class="shadow-sm rounded-xl">
        <a-form layout="vertical">
          <a-form-item
            label="Chế độ bảo trì"
            help="Khi bật, chỉ Admin mới có thể truy cập hệ thống"
          >
            <a-switch
              v-model:checked="formState['maintenance_mode']"
              checked-children="BẬT BẢO TRÌ"
              un-checked-children="Hoạt động"
              class="bg-slate-300"
              :class="{ '!bg-red-500': formState['maintenance_mode'] }"
            />
          </a-form-item>

          <a-form-item
            label="Tính năng AI Chat"
            help="Bật/Tắt module chat thông minh"
          >
            <a-switch
              v-model:checked="formState['enable_ai_chat']"
              checked-children="Bật"
              un-checked-children="Tắt"
            />
          </a-form-item>

          <a-form-item label="Ngôn ngữ mặc định">
            <a-select v-model:value="formState['default_language']">
              <a-select-option value="vi">Tiếng Việt</a-select-option>
              <a-select-option value="en">English</a-select-option>
            </a-select>
          </a-form-item>
        </a-form>
      </a-card>
    </div>
  </div>
</template>
