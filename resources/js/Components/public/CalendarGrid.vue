<script setup lang="ts">
import type { CalendarDay } from '../../types/portal'
import CalendarDayCard from './CalendarDay.vue'

defineProps<{
  days: CalendarDay[]
}>()

defineEmits<{
  select: [day: CalendarDay]
}>()

const weekdays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']
</script>

<template>
  <div>
    <div class="mb-3 hidden grid-cols-7 gap-3 px-1 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500 sm:grid">
      <span v-for="weekday in weekdays" :key="weekday">{{ weekday }}</span>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
      <CalendarDayCard v-for="day in days" :key="day.date" :day="day" @select="$emit('select', $event)" />
    </div>
  </div>
</template>