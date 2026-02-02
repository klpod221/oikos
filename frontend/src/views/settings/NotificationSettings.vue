<script setup>
import { ref, onMounted } from "vue";
import { message } from "ant-design-vue";
import {
  CheckCircleOutlined,
  CloseCircleOutlined,
  RobotOutlined,
  PlusOutlined,
  DeleteOutlined,
  BellOutlined,
  AndroidOutlined,
} from "@ant-design/icons-vue";

const isAndroid = ref(false);
const hasPermission = ref(false);
const whitelist = ref([]);
const newPackage = ref("");

// Common VN Banking Apps for quick add
const commonApps = [
  { name: "Vietcombank", package: "com.VCB.MobileBanking" },
  { name: "Techcombank", package: "vn.com.techcombank.bb.app" },
  { name: "MB Bank", package: "com.mbmobile" },
  { name: "BIDV", package: "com.vnpay.bidv" },
  { name: "VietinBank", package: "com.vietinbank.ipay" },
  { name: "TPBank", package: "com.tpb.mobile" },
  { name: "VPBank", package: "com.vpbank.neo" },
  { name: "ACB", package: "com.acb.mobile" },
  { name: "Momo", package: "com.mservice.momotransfer" },
  { name: "ZaloPay", package: "vn.com.vng.zalopay" },
];

const checkEnvironment = () => {
  // Check if Bridge exists
  if (window.AndroidNotification) {
    isAndroid.value = true;
    hasPermission.value = window.AndroidNotification.checkPermission();
    loadWhitelist();
  } else {
    // Fallback or dev mode
    console.warn("AndroidNotification bridge not found.");
  }
};

const requestPermission = () => {
  if (window.AndroidNotification) {
    window.AndroidNotification.requestPermission();
    // Poll for change or wait for resume
    const interval = setInterval(() => {
      if (window.AndroidNotification.checkPermission()) {
        hasPermission.value = true;
        clearInterval(interval);
        message.success("Đã cấp quyền thành công!");
      }
    }, 1000);
    // Stop after 30s
    setTimeout(() => clearInterval(interval), 30000);
  }
};

const loadWhitelist = () => {
  if (window.AndroidNotification) {
    try {
      const json = window.AndroidNotification.getWhitelist();
      whitelist.value = JSON.parse(json);
    } catch (e) {
      console.error("Failed to parse whitelist", e);
      whitelist.value = [];
    }
  }
};

const saveWhitelist = () => {
  if (window.AndroidNotification) {
    const json = JSON.stringify(whitelist.value);
    window.AndroidNotification.saveWhitelist(json);
    message.success("Đã lưu cấu hình");
  }
};

const addPackage = (pkg) => {
  if (pkg && !whitelist.value.includes(pkg)) {
    whitelist.value.push(pkg);
    saveWhitelist();
    newPackage.value = "";
  }
};

const removePackage = (pkg) => {
  whitelist.value = whitelist.value.filter((p) => p !== pkg);
  saveWhitelist();
};

const getAppName = (pkg) => {
  const found = commonApps.find((a) => a.package === pkg);
  return found ? found.name : pkg;
};

onMounted(() => {
  checkEnvironment();
  // Re-check on visibility change (app resume)
  document.addEventListener("visibilitychange", () => {
    if (!document.hidden && isAndroid.value) {
      hasPermission.value = window.AndroidNotification.checkPermission();
    }
  });
});
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center gap-3 mb-2">
      <div
        class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"
      >
        <BellOutlined class="text-xl" />
      </div>
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0">Cài đặt Thông báo</h1>
        <p class="text-slate-500 text-sm m-0">Quản lý nguồn dữ liệu tự động</p>
      </div>
    </div>

    <!-- Warning for non-Android -->
    <a-alert
      v-if="!isAndroid"
      message="Chưa phát hiện môi trường Android"
      description="Tính năng này chỉ hoạt động trên ứng dụng Android."
      type="warning"
      show-icon
      class="rounded-xl border-none bg-orange-50 text-orange-800"
    >
      <template #icon><AndroidOutlined /></template>
    </a-alert>

    <!-- Permission Card -->
    <a-card
      class="rounded-2xl border border-slate-200"
      :bodyStyle="{ padding: '16px' }"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div
            :class="`w-10 h-10 rounded-full flex items-center justify-center ${hasPermission ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'}`"
          >
            <CheckCircleOutlined v-if="hasPermission" class="text-xl" />
            <CloseCircleOutlined v-else class="text-xl" />
          </div>
          <div>
            <h3 class="font-semibold text-slate-800 m-0">
              Quyền đọc thông báo
            </h3>
            <p class="text-xs text-slate-500 m-0">Để tự động cập nhật số dư</p>
          </div>
        </div>
        <div
          v-if="hasPermission"
          class="px-3 py-1.5 bg-green-50 text-green-600 rounded-lg text-sm font-medium"
        >
          Đã cấp quyền
        </div>
        <a-button
          v-else
          type="primary"
          danger
          class="rounded-lg shadow-none"
          @click="requestPermission"
        >
          Cấp quyền
        </a-button>
      </div>
    </a-card>

    <!-- Whitelist Section -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div
        class="p-2 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center"
      >
        <h3 class="font-semibold text-slate-800 m-0">Ứng dụng theo dõi</h3>
        <a-tag color="blue">{{ whitelist.length }} App</a-tag>
      </div>

      <div class="p-2 space-y-4">
        <!-- Add Controls -->
        <div class="flex flex-col gap-2">
          <a-select
            class="w-full"
            placeholder="Chọn ngân hàng phổ biến..."
            @change="addPackage"
            size="large"
          >
            <a-select-option
              v-for="app in commonApps"
              :key="app.package"
              :value="app.package"
            >
              <div class="flex items-center justify-between">
                <span>{{ app.name }}</span>
                <span class="text-xs text-slate-400"
                  >{{ app.package.replace("com.", "") }}...</span
                >
              </div>
            </a-select-option>
          </a-select>

          <div class="flex gap-2">
            <a-input
              v-model:value="newPackage"
              placeholder="Hoặc nhập package ID..."
              class="rounded-lg"
              allowClear
              @pressEnter="addPackage(newPackage)"
            />
            <a-button @click="addPackage(newPackage)" class="rounded-lg">
              <template #icon><PlusOutlined /></template>
            </a-button>
          </div>
        </div>

        <!-- List -->
        <a-list
          item-layout="horizontal"
          :data-source="whitelist"
          :locale="{ emptyText: 'Chưa chọn ứng dụng nào' }"
        >
          <template #renderItem="{ item }">
            <a-list-item class="!px-0 !py-3">
              <a-list-item-meta>
                <template #title>
                  <span class="font-medium text-slate-700">{{
                    getAppName(item)
                  }}</span>
                </template>
                <template #description>
                  <span
                    class="text-xs text-slate-400 font-mono bg-slate-100 px-1.5 py-0.5 rounded"
                    >{{ item }}</span
                  >
                </template>
                <template #avatar>
                  <div
                    class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500"
                  >
                    <RobotOutlined />
                  </div>
                </template>
              </a-list-item-meta>
              <template #actions>
                <a-button
                  type="text"
                  danger
                  shape="circle"
                  @click="removePackage(item)"
                >
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </template>
            </a-list-item>
          </template>
        </a-list>
      </div>
    </div>
  </div>
</template>
