<script setup>
import { ref, watch } from "vue";
import {
  SearchOutlined,
  LoadingOutlined,
  ClockCircleOutlined,
} from "@ant-design/icons-vue";
import { Modal, Tag } from "ant-design-vue";

const props = defineProps({
  open: Boolean,
  store: Object,
});

const emit = defineEmits(["update:open"]);

const searchQuery = ref("");
const searchResults = ref([]);
const isSearching = ref(false);

const handleSearchInModal = async () => {
  if (!searchQuery.value.trim()) return;
  isSearching.value = true;
  try {
    const results = await props.store.getService().getHistory({
      search: searchQuery.value,
      limit: 20,
    });
    searchResults.value = results.data || [];
  } catch (e) {
    console.error("Search failed", e);
  } finally {
    isSearching.value = false;
  }
};

const formatTime = (isoString) => {
  if (!isoString) return "";
  try {
    const date = new Date(isoString);
    if (isNaN(date.getTime())) return "";
    return date.toLocaleTimeString("vi-VN", {
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (e) {
    return "";
  }
};

const formatDate = (isoString) => {
  if (!isoString) return "";
  try {
    const date = new Date(isoString);
    return date.toLocaleDateString("vi-VN", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  } catch (e) {
    return "";
  }
};

watch(
  () => props.open,
  (newVal) => {
    if (newVal) {
      searchQuery.value = "";
      searchResults.value = [];
    }
  },
);
</script>

<template>
  <Modal
    :open="open"
    @update:open="$emit('update:open', $event)"
    title="Tìm kiếm tin nhắn"
    :footer="null"
    width="600px"
  >
    <div class="mb-4">
      <div class="flex gap-2">
        <input
          v-model="searchQuery"
          @keydown.enter="handleSearchInModal"
          placeholder="Nhập từ khóa tìm kiếm..."
          class="flex-1 p-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
          autoFocus
        />
        <button
          @click="handleSearchInModal"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <SearchOutlined /> Tìm
        </button>
      </div>
    </div>

    <div class="max-h-[60vh] overflow-y-auto space-y-3">
      <div v-if="isSearching" class="text-center py-4 text-gray-400">
        <LoadingOutlined spin /> Đang tìm...
      </div>
      <div
        v-else-if="searchResults.length === 0 && searchQuery"
        class="text-center py-4 text-gray-400"
      >
        Không tìm thấy kết quả nào.
      </div>

      <div
        v-for="item in searchResults"
        :key="item.id"
        class="border border-gray-100 rounded-lg p-3 hover:bg-gray-50 transition-colors"
      >
        <div class="flex justify-between items-start mb-1">
          <Tag :color="item.role === 'user' ? 'blue' : 'green'">
            {{ item.role === "user" ? "Bạn" : "AI" }}
          </Tag>
          <div class="text-xs text-gray-400 flex items-center gap-1">
            <ClockCircleOutlined />
            {{ formatTime(item.created_at) }} {{ formatDate(item.created_at) }}
          </div>
        </div>
        <div class="text-sm text-gray-700 line-clamp-3">
          {{ item.content }}
        </div>
      </div>
    </div>
  </Modal>
</template>
