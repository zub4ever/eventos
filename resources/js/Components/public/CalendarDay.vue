<script setup lang="ts">
import type { CalendarDay } from '../../types/portal'

const props = defineProps<{
  day: CalendarDay
}>()

defineEmits<{
  select: [day: CalendarDay]
}>()

function dayNumber(date: string): string {
  return date.slice(-2)
}

function parsePortalDate(date: string): Date | null {
  const parsed = new Date(`${date}T00:00:00`)

  return Number.isNaN(parsed.getTime()) ? null : parsed
}

function isPast(date: string): boolean {
  const target = parsePortalDate(date)

  if (target === null) {
    return true
  }

  const today = new Date()
  today.setHours(0, 0, 0, 0)

  return target < today
}

const past = isPast(props.day.date)
const weekdayLabel = (() => {
  const parsed = parsePortalDate(props.day.date)

  if (parsed === null) {
    return '--'
  }

  return new Intl.DateTimeFormat('pt-BR', { weekday: 'short' }).format(parsed)
})()
</script>

<template>
  <button
    type="button"
    :disabled="past || !day.available"
    :class="[
      'group flex aspect-square flex-col justify-between rounded-[1.3rem] border p-3 text-left transition',
      past
        ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400'
        : day.available
          ? 'border-emerald-200 bg-emerald-50 text-emerald-900 hover:-translate-y-0.5 hover:shadow-lg'
          : 'cursor-not-allowed border-rose-200 bg-rose-50 text-rose-700',
    ]"
    @click="$emit('select', day)"
  >
    <span class="text-xs font-semibold uppercase tracking-[0.2em]">{{ weekdayLabel }}</span>
    <strong class="text-2xl font-semibold">{{ dayNumber(day.date) }}</strong>
    <span class="text-xs font-medium">
      {{ past ? 'Encerrado' : day.available ? 'Livre' : 'Ocupado' }}
    </span>
  </button>
</template>