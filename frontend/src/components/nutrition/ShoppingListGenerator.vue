<template>
  <div class="shopping-list-generator">
    <div class="generator-header">
      <h2>Shopping List Generator</h2>
      <p>Generate a shopping list from your meal plans</p>
    </div>

    <div class="date-range-selector">
      <div class="input-group">
        <label for="start-date">Start Date</label>
        <input
          id="start-date"
          v-model="startDate"
          type="date"
          class="date-input"
        />
      </div>

      <div class="input-group">
        <label for="end-date">End Date</label>
        <input id="end-date" v-model="endDate" type="date" class="date-input" />
      </div>

      <button @click="generatePreview" class="btn-generate" :disabled="loading">
        {{ loading ? "Generating..." : "Preview" }}
      </button>
    </div>

    <!-- Preview Section -->
    <div v-if="previewItems.length > 0" class="preview-section">
      <div class="preview-header">
        <h3>Preview ({{ previewItems.length }} items)</h3>
        <input
          v-model="listName"
          type="text"
          placeholder="Shopping list name"
          class="list-name-input"
        />
      </div>

      <div class="items-list">
        <div
          v-for="item in previewItems"
          :key="item.ingredient_id"
          class="item-row"
        >
          <div class="item-info">
            <span class="ingredient-name">{{ item.ingredient_name }}</span>
            <span class="quantity">{{
              formatQuantity(item.total_quantity)
            }}</span>
          </div>
          <input v-model="item.is_purchased" type="checkbox" class="checkbox" />
        </div>
      </div>

      <div class="actions">
        <button @click="saveShoppingList" class="btn-save" :disabled="saving">
          {{ saving ? "Saving..." : "Save Shopping List" }}
        </button>
        <button @click="exportToPDF" class="btn-export">Export to PDF</button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading" class="empty-state">
      <p>
        Select a date range and click Preview to generate your shopping list
      </p>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useToast } from "vue-toastification";

const toast = useToast();

const startDate = ref("");
const endDate = ref("");
const listName = ref("");
const previewItems = ref([]);
const loading = ref(false);
const saving = ref(false);
const error = ref("");

// Initialize dates to current week
const today = new Date();
const nextWeek = new Date(today);
nextWeek.setDate(today.getDate() + 7);

startDate.value = today.toISOString().split("T")[0];
endDate.value = nextWeek.toISOString().split("T")[0];

/**
 * Generate preview of shopping list
 */
async function generatePreview() {
  if (!startDate.value || !endDate.value) {
    error.value = "Please select both start and end dates";
    return;
  }

  if (new Date(startDate.value) > new Date(endDate.value)) {
    error.value = "Start date must be before end date";
    return;
  }

  error.value = "";
  loading.value = true;

  try {
    // Call backend API to generate preview
    const response = await fetch("/api/nutrition/shopping-list/preview", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
      body: JSON.stringify({
        start_date: startDate.value,
        end_date: endDate.value,
      }),
    });

    if (!response.ok) throw new Error("Failed to generate preview");

    const data = await response.json();
    previewItems.value = data.items.map((item) => ({
      ...item,
      is_purchased: false,
    }));

    // Auto-generate list name
    const start = new Date(startDate.value).toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
    });
    const end = new Date(endDate.value).toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
    });
    listName.value = `Shopping List ${start} - ${end}`;
  } catch (err) {
    error.value = err.message;
    toast.error("Failed to generate shopping list preview");
  } finally {
    loading.value = false;
  }
}

/**
 * Save shopping list
 */
async function saveShoppingList() {
  if (!listName.value) {
    error.value = "Please enter a name for the shopping list";
    return;
  }

  saving.value = true;

  try {
    const response = await fetch("/api/nutrition/shopping-list", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
      body: JSON.stringify({
        name: listName.value,
        start_date: startDate.value,
        end_date: endDate.value,
        items: previewItems.value,
      }),
    });

    if (!response.ok) throw new Error("Failed to save shopping list");

    const data = await response.json();
    toast.success("Shopping list saved successfully!");

    // Reset form
    previewItems.value = [];
    listName.value = "";
  } catch (err) {
    error.value = err.message;
    toast.error("Failed to save shopping list");
  } finally {
    saving.value = false;
  }
}

/**
 * Export shopping list to PDF
 */
function exportToPDF() {
  // Simple print approach (browser built-in)
  const printWindow = window.open("", "_blank");

  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>${listName.value}</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 2rem; }
        h1 { color: #1f2937; }
        .item { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb; }
        .checkbox { width: 20px; height: 20px; }
      </style>
    </head>
    <body>
      <h1>${listName.value}</h1>
      <p>Period: ${startDate.value} to ${endDate.value}</p>
      <div>
        ${previewItems.value
          .map(
            (item) => `
          <div class="item">
            <span>${item.ingredient_name}</span>
            <span>${formatQuantity(item.total_quantity)}</span>
            <input type="checkbox" class="checkbox" />
          </div>
        `,
          )
          .join("")}
      </div>
    </body>
    </html>
  `;

  printWindow.document.write(html);
  printWindow.document.close();
  printWindow.print();
}

/**
 * Format quantity with unit
 */
function formatQuantity(grams) {
  if (grams >= 1000) {
    return `${(grams / 1000).toFixed(2)} kg`;
  }
  return `${grams.toFixed(0)} g`;
}
</script>

<style scoped>
.shopping-list-generator {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  max-width: 800px;
  margin: 0 auto;
}

.generator-header {
  margin-bottom: 2rem;
}

.generator-header h2 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
}

.generator-header p {
  margin: 0;
  color: #6b7280;
}

.date-range-selector {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 1rem;
  margin-bottom: 2rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.input-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.date-input,
.list-name-input {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
}

.date-input:focus,
.list-name-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-generate {
  align-self: end;
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-generate:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-2px);
}

.btn-generate:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.preview-section {
  margin-top: 2rem;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.preview-header h3 {
  margin: 0;
  color: #1f2937;
}

.list-name-input {
  width: 300px;
}

.items-list {
  max-height: 400px;
  overflow-y: auto;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.item-row:last-child {
  border-bottom: none;
}

.item-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  flex: 1;
}

.ingredient-name {
  font-weight: 600;
  color: #1f2937;
}

.quantity {
  color: #6b7280;
  font-size: 0.875rem;
}

.checkbox {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.actions {
  display: flex;
  gap: 1rem;
}

.btn-save,
.btn-export {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save {
  background: #22c55e;
  color: white;
}

.btn-save:hover:not(:disabled) {
  background: #16a34a;
  transform: translateY(-2px);
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-export {
  background: #f3f4f6;
  color: #1f2937;
}

.btn-export:hover {
  background: #e5e7eb;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem;
  background: #fee2e2;
  color: #991b1b;
  border-radius: 8px;
  font-size: 0.875rem;
}

/* Mobile */
@media (max-width: 768px) {
  .date-range-selector {
    grid-template-columns: 1fr;
  }

  .preview-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .list-name-input {
    width: 100%;
  }

  .actions {
    flex-direction: column;
  }
}
</style>
