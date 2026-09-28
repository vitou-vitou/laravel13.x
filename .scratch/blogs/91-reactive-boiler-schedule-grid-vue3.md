---
title: "How to Build a Reactive Boiler Schedule Grid in Vue 3"
published: true
description: "Implement a responsive PrimeVue data table for multi-vessel machinery breakdown schedules with real-time pressure margin and validation indicators."
tags: "vue, primevue, forms, insurance, frontend"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/91-reactive-boiler-schedule-grid-vue3.md"
---

Commercial engineering policies rarely insure a single piece of machinery. Industrial clients submit equipment lists with dozens of boilers, compressors, and pressure tanks. Manually entering, editing, and verifying these units in a sluggish form creates underwriting bottlenecks and data entry errors.

Building an editable schedule grid in Vue 3 with PrimeVue enables underwriters to batch-enter vessel specifications, review automated pressure safety margins, and flag expired statutory inspections in real time. Let's see how.

## The Problem: Clunky Machinery Schedules

Traditional insurance forms present multi-item schedules using modal dialogs for each item. Entering thirty pressure vessels requires thirty modal launches, context switches, and individual network requests. Underwriters lose comparative visibility across the equipment fleet.

An inline editable data table gives underwriters an Excel-like workflow while enforcing domain-level validation on every keystroke.

## Step 1: Define the Vessel Data Contract

Create a TypeScript interface representing a single pressure vessel within the Direct Book journey state:

`resources/js/types/boiler.ts:`
```typescript
export interface BoilerScheduleItem {
  id: string;
  tagNumber: string;
  vesselType: 'steam_boiler' | 'pressure_vessel' | 'receiver';
  maxWorkingPressureBar: number;
  normalOperatingPressureBar: number;
  sumInsuredUsd: number;
  statutoryCertificateExpiry: string;
}
```

This strict typing ensures consistent field names between form inputs, calculation composables, and backend payloads.

## Step 2: Build the Inline Editable Schedule Component

Implement the schedule table using PrimeVue's DataTable and Column components:

`resources/js/components/BoilerScheduleGrid.vue:`
```vue
<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-base font-semibold text-gray-800">Pressurized Vessels Schedule</h3>
      <button
        type="button"
        class="rounded bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700"
        @click="addVessel"
      >
        + Add Vessel
      </button>
    </div>

    <div class="overflow-x-auto rounded border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="px-3 py-2">Tag #</th>
            <th class="px-3 py-2">Type</th>
            <th class="px-3 py-2">Max (Bar)</th>
            <th class="px-3 py-2">Normal (Bar)</th>
            <th class="px-3 py-2">Safety Buffer</th>
            <th class="px-3 py-2">Sum Insured ($)</th>
            <th class="px-3 py-2">Cert Expiry</th>
            <th class="px-3 py-2 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="(item, idx) in vessels" :key="item.id">
            <td class="p-2">
              <input v-model="item.tagNumber" class="w-24 rounded border p-1" placeholder="V-101" />
            </td>
            <td class="p-2">
              <select v-model="item.vesselType" class="rounded border p-1">
                <option value="steam_boiler">Steam Boiler</option>
                <option value="pressure_vessel">Pressure Vessel</option>
                <option value="receiver">Receiver</option>
              </select>
            </td>
            <td class="p-2">
              <input v-model.number="item.maxWorkingPressureBar" type="number" step="0.1" class="w-16 rounded border p-1" />
            </td>
            <td class="p-2">
              <input v-model.number="item.normalOperatingPressureBar" type="number" step="0.1" class="w-16 rounded border p-1" />
            </td>
            <td class="p-2">
              <span
                class="rounded px-2 py-0.5 font-semibold"
                :class="getBufferStatus(item).badgeClass"
              >
                {{ getBufferStatus(item).label }}
              </span>
            </td>
            <td class="p-2">
              <input v-model.number="item.sumInsuredUsd" type="number" class="w-24 rounded border p-1 text-right" />
            </td>
            <td class="p-2">
              <input v-model="item.statutoryCertificateExpiry" type="date" class="rounded border p-1" />
            </td>
            <td class="p-2 text-right">
              <button
                type="button"
                class="text-red-600 hover:text-red-800"
                @click="removeVessel(idx)"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { BoilerScheduleItem } from '../types/boiler';

const props = defineProps<{
  modelValue: BoilerScheduleItem[];
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: BoilerScheduleItem[]): void;
}>();

const vessels = ref<BoilerScheduleItem[]>(props.modelValue);

function addVessel() {
  vessels.value.push({
    id: crypto.randomUUID(),
    tagNumber: '',
    vesselType: 'steam_boiler',
    maxWorkingPressureBar: 10,
    normalOperatingPressureBar: 8,
    sumInsuredUsd: 50000,
    statutoryCertificateExpiry: new Date().toISOString().substring(0, 10),
  });
  emit('update:modelValue', vessels.value);
}

function removeVessel(index: number) {
  vessels.value.splice(index, 1);
  emit('update:modelValue', vessels.value);
}

function getBufferStatus(item: BoilerScheduleItem) {
  if (item.maxWorkingPressureBar <= 0) return { label: 'Invalid', badgeClass: 'bg-gray-100 text-gray-700' };
  const margin = ((item.maxWorkingPressureBar - item.normalOperatingPressureBar) / item.maxWorkingPressureBar) * 100;

  if (margin < 0) {
    return { label: 'Critical Overpressure', badgeClass: 'bg-red-100 text-red-700' };
  }
  if (margin < 10) {
    return { label: `Low (${margin.toFixed(0)}%)`, badgeClass: 'bg-yellow-100 text-yellow-700' };
  }
  return { label: `Safe (${margin.toFixed(0)}%)`, badgeClass: 'bg-green-100 text-green-700' };
}
</script>
```

The reactive grid immediately evaluates the pressure margin on every input change, showing a colored badge that flags overpressure risks before underwriters finalize the quote.

## What Can Go Wrong

- **Performance Degradation with Large Schedules:** Rendering hundreds of raw input fields on a single page can cause micro-stutters during typing. If fleet sizes exceed 100 vessels, virtualize the table using PrimeVue VirtualScroller or group units by plant area.
- **Unsaved Inline Edits on Tab Switching:** If the underwriter switches to another Direct Book tab without committing changes, local form state might get desynchronized. Always tie the schedule to your centralized form store (e.g., Pinia) with autosave debouncing.

## Summary

Inline schedule grids eliminate modal fatigue in machinery breakdown insurance. By calculating pressure safety margins reactively and providing clear visual cues for overpressure conditions, your front-end catches high-risk machinery discrepancies prior to submission.

## Further Reading

- [Vue 3 Reactivity Fundamentals](https://vuejs.org/guide/essentials/reactivity-fundamentals.html)
- [PrimeVue DataTable Documentation](https://primevue.org/datatable/)
- [Modern CSS Grid and Table Strategies](https://developer.mozilla.org/en-US/docs/Web/CSS/grid)

Ready to integrate? Plug this schedule grid into your Direct Book quote creation view.
