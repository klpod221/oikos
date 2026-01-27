<!--
  RecipeDetailModal.vue
  
  Read-only modal for displaying recipe details visually.
-->
<script setup>
import { computed } from "vue";
import { 
  ClockCircleOutlined, 
  FireOutlined, 
  TeamOutlined,
  CheckOutlined
} from "@ant-design/icons-vue";

const props = defineProps({
  open: { type: Boolean, default: false },
  recipe: { type: Object, default: null },
});

const emit = defineEmits(["update:open"]);

const handleCancel = () => {
  emit("update:open", false);
};

const totalTime = computed(() => {
    if (!props.recipe) return 0;
    return (props.recipe.prep_time || 0) + (props.recipe.cooking_time || 0);
});
</script>

<template>
  <a-modal
    :open="open"
    @update:open="emit('update:open', $event)"
    :footer="null"
    width="800px"
    class="recipe-detail-modal"
  >
    <div v-if="recipe" class="p-4">
        <!-- Header -->
        <h2 class="text-3xl font-bold text-slate-800 mb-2">{{ recipe.name }}</h2>
        <p class="text-slate-500 italic mb-6 text-lg">{{ recipe.description }}</p>

        <!-- Meta Info Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
             <div class="bg-orange-50 p-3 rounded-lg flex items-center gap-3">
                 <div class="bg-white p-2 rounded-full text-orange-500 shadow-sm">
                     <ClockCircleOutlined />
                 </div>
                 <div>
                     <div class="text-xs text-slate-500 uppercase font-semibold">Thời gian</div>
                     <div class="font-bold text-slate-700">{{ totalTime }} phút</div>
                 </div>
             </div>
             <div class="bg-red-50 p-3 rounded-lg flex items-center gap-3">
                 <div class="bg-white p-2 rounded-full text-red-500 shadow-sm">
                     <FireOutlined />
                 </div>
                 <div>
                     <div class="text-xs text-slate-500 uppercase font-semibold">Calo</div>
                     <div class="font-bold text-slate-700">{{ recipe.calories }} kcal</div>
                 </div>
             </div>
             <div class="bg-blue-50 p-3 rounded-lg flex items-center gap-3">
                 <div class="bg-white p-2 rounded-full text-blue-500 shadow-sm">
                     <TeamOutlined />
                 </div>
                 <div>
                     <div class="text-xs text-slate-500 uppercase font-semibold">Khẩu phần</div>
                     <div class="font-bold text-slate-700">{{ recipe.servings }} người</div>
                 </div>
             </div>
             <div class="bg-green-50 p-3 rounded-lg flex flex-col justify-center">
                 <div class="flex justify-between text-xs mb-1">
                     <span class="text-slate-500">Đạm</span>
                     <span class="font-bold text-slate-700">{{ recipe.protein }}g</span>
                 </div>
                 <div class="flex justify-between text-xs mb-1">
                     <span class="text-slate-500">Carb</span>
                     <span class="font-bold text-slate-700">{{ recipe.carbs }}g</span>
                 </div>
                 <div class="flex justify-between text-xs">
                     <span class="text-slate-500">Béo</span>
                     <span class="font-bold text-slate-700">{{ recipe.fat }}g</span>
                 </div>
             </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Ingredients Column -->
            <div class="md:col-span-1">
                <h3 class="text-xl font-bold text-slate-800 mb-4 border-b pb-2">Nguyên liệu</h3>
                <ul class="space-y-3">
                    <li v-for="ing in recipe.ingredients" :key="ing.id" class="flex items-start gap-2">
                         <CheckOutlined class="text-green-500 mt-1 shrink-0" />
                         <span class="text-slate-700">
                             <span class="font-semibold">{{ ing.pivot?.quantity }} {{ ing.pivot?.unit }}</span>
                             {{ ing.name }}
                         </span>
                    </li>
                    <li v-if="!recipe.ingredients || recipe.ingredients.length === 0" class="text-slate-400 italic">
                        Chưa có nguyên liệu
                    </li>
                </ul>
            </div>

            <!-- Instructions Column -->
             <div class="md:col-span-2">
                <h3 class="text-xl font-bold text-slate-800 mb-4 border-b pb-2">Hướng dẫn nấu</h3>
                <div class="space-y-4">
                    <div 
                        v-for="(step, index) in (recipe.instructions || '').split('\n').filter(s => s.trim())" 
                        :key="index"
                        class="flex gap-4"
                    >
                        <div class="shrink-0 w-8 h-8 rounded-full bg-slate-100 text-slate-500 font-bold flex items-center justify-center border border-slate-200">
                            {{ index + 1 }}
                        </div>
                        <p class="text-slate-700 leading-relaxed pt-1">{{ step.replace(/^\d+\.\s*/, '') }}</p>
                    </div>
                     <p v-if="!recipe.instructions" class="text-slate-400 italic">
                        Chưa có hướng dẫn
                    </p>
                </div>
            </div>
        </div>
    </div>
  </a-modal>
</template>

<style scoped>
/* Optional styling overrides */
</style>
