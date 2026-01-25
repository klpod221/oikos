<!--
  MealPlanCalendar.vue
  
  Weekly calendar view for meal plans.
-->
<script setup>
import { ref, computed, onMounted } from "vue";
import { useNutritionStore } from "../../stores/nutrition";
import dayjs from "dayjs";
import isoWeek from "dayjs/plugin/isoWeek";
import { LeftOutlined, RightOutlined, PlusOutlined } from "@ant-design/icons-vue";
import MealPlanModal from "./MealPlanModal.vue";

dayjs.extend(isoWeek);

const nutrition = useNutritionStore();

// Calendar State
const currentWeekStart = ref(dayjs().startOf("isoWeek")); // Monday
const selectedPlan = ref(null);
const modalOpen = ref(false);
const modalMode = ref("create");
const form = ref({
  date: dayjs().format("YYYY-MM-DD"),
  meal_type: "breakfast",
  recipe_id: null,
  description: "",
  status: "planned",
});

// Calculate week days with pre-formatting to avoid render-loop calls
const weekDays = computed(() => {
  const days = [];
  const start = currentWeekStart.value;
  const today = dayjs().format("YYYY-MM-DD");
  
  for (let i = 0; i < 7; i++) {
    const d = start.add(i, "day");
    const dateStr = d.format("YYYY-MM-DD");
    days.push({
        dateObj: d,
        dateString: dateStr,
        dayName: d.format("ddd"),
        dayNumber: d.format("DD"),
        isToday: dateStr === today
    });
  }
  return days;
});

const weekRangeLabel = computed(() => {
  const start = currentWeekStart.value.format("DD/MM");
  const end = currentWeekStart.value.endOf("isoWeek").format("DD/MM/YYYY");
  return `${start} - ${end}`;
});

// Group plans by Date -> Meal Type
const plansByDate = computed(() => {
  const map = {};
  nutrition.mealPlans.forEach((plan) => {
    const date = plan.date; // YYYY-MM-DD
    if (!map[date]) map[date] = {};
    if (!map[date][plan.meal_type]) map[date][plan.meal_type] = [];
    map[date][plan.meal_type].push(plan);
  });
  return map;
});

// Actions
const loadPlans = async () => {
  const start = currentWeekStart.value.format("YYYY-MM-DD");
  const end = currentWeekStart.value.endOf("isoWeek").format("YYYY-MM-DD");
  await nutrition.fetchMealPlans(start, end);
};

const prevWeek = () => {
  currentWeekStart.value = currentWeekStart.value.subtract(1, "week");
  loadPlans();
};

const nextWeek = () => {
  currentWeekStart.value = currentWeekStart.value.add(1, "week");
  loadPlans();
};

const currentWeek = () => {
  currentWeekStart.value = dayjs().startOf("isoWeek");
  loadPlans();
};

// Modal handlers
const openCreate = (date, type = "breakfast") => {
  selectedPlan.value = null;
  form.value = {
    date: date.format("YYYY-MM-DD"),
    meal_type: type,
    recipe_id: null,
    description: "",
    status: "planned",
  };
  modalMode.value = "create";
  modalOpen.value = true;
};

const openEdit = (plan) => {
  selectedPlan.value = plan;
  form.value = {
    date: plan.date,
    meal_type: plan.meal_type,
    recipe_id: plan.recipe_id,
    description: plan.description,
    status: plan.status,
  };
  modalMode.value = "edit";
  modalOpen.value = true;
};

const handleSubmit = async (data) => {
  let success = false;
  if (modalMode.value === "create") {
    success = await nutrition.createMealPlan(data);
  } else {
    success = await nutrition.updateMealPlan(selectedPlan.value.id, data);
  }
  
  if (success) {
    modalOpen.value = false;
    loadPlans();
  }
};

const handleDelete = async () => {
    if (selectedPlan.value) {
        const success = await nutrition.deleteMealPlan(selectedPlan.value.id);
        if (success) {
            modalOpen.value = false;
            loadPlans();
        }
    }
}

onMounted(() => {
  loadPlans();
});

const mealTypes = [
  { key: "breakfast", label: "Sáng", color: "text-orange-500 bg-orange-50 border-orange-100" },
  { key: "lunch", label: "Trưa", color: "text-blue-500 bg-blue-50 border-blue-100" },
  { key: "dinner", label: "Tối", color: "text-purple-500 bg-purple-50 border-purple-100" },
  { key: "snack", label: "Phụ", color: "text-green-500 bg-green-50 border-green-100" },
];
</script>

<template>
  <div class="flex flex-col h-full">
    <!-- Toolbar -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <a-button-group>
          <a-button @click="prevWeek">
            <LeftOutlined />
          </a-button>
          <a-button @click="currentWeek">Hôm nay</a-button>
          <a-button @click="nextWeek">
            <RightOutlined />
          </a-button>
        </a-button-group>
        <span class="font-semibold text-lg ml-2">{{ weekRangeLabel }}</span>
      </div>
      <a-button type="primary" @click="openCreate(dayjs())">
        <PlusOutlined /> Thêm bữa ăn
      </a-button>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-px border border-slate-200 bg-slate-200 rounded-lg overflow-hidden">
        <div 
            v-for="day in weekDays" 
            :key="day.dateString"
            class="flex flex-col bg-slate-200 gap-px"
        >
            <!-- Day Header -->
            <div 
                class="bg-white p-2 text-center flex justify-between md:block items-center"
                :class="{ 'bg-blue-50': day.isToday }"
            >
                <div class="text-xs font-semibold text-slate-500 uppercase">{{ day.dayName }}</div>
                <div 
                    class="text-lg font-bold"
                    :class="day.isToday ? 'text-blue-600' : 'text-slate-700'"
                >
                    {{ day.dayNumber }}
                </div>
            </div>

            <!-- Meal Content -->
            <div 
                class="bg-white p-2 min-h-[150px] md:min-h-[400px] flex flex-col gap-2 relative group flex-1"
            >
                <!-- Add Button Overlay -->
                 <div class="absolute top-2 right-2 md:opacity-0 md:group-hover:opacity-100 transition-opacity z-10">
                    <a-button type="dashed" size="small" shape="circle" @click="openCreate(day.dateObj)">
                        <PlusOutlined class="text-xs" />
                    </a-button>
                </div>

                <!-- Meal Sections -->
                <div v-for="type in mealTypes" :key="type.key" class="flex flex-col gap-1">
                    <!-- Meal Header if has items -->
                    <div 
                        v-if="plansByDate[day.dateString]?.[type.key]?.length > 0"
                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1"
                    >
                        {{ type.label }}
                    </div>

                    <!-- Plan Items -->
                     <div 
                        v-for="plan in (plansByDate[day.dateString]?.[type.key] || [])"
                        :key="plan.id"
                        @click="openEdit(plan)"
                        class="p-2 rounded border cursor-pointer hover:shadow-sm transition-all text-xs"
                        :class="[
                            plan.status === 'completed' ? 'opacity-60 bg-slate-50 line-through' : 'bg-white',
                            type.color
                        ]"
                     >
                        <div class="font-medium truncate">
                            {{ plan.recipe?.name || plan.description }}
                        </div>
                     </div>
                </div>
            </div>
        </div>
    </div>

    <MealPlanModal
        v-model:open="modalOpen"
        v-model:form="form"
        :mode="modalMode"
        @submit="handleSubmit"
        @delete="handleDelete"
    />
  </div>
</template>
