<script setup lang="ts">
import type { CalendarDay } from '../../types/portal'
import CalendarGrid from './CalendarGrid.vue'
import CalendarMonthHeader from './CalendarMonthHeader.vue'
import ErrorState from './ErrorState.vue'
import LoadingState from './LoadingState.vue'

defineProps<{
  days: CalendarDay[]
  loading: boolean
  error: string | null
  monthLabel: string
}>()

defineEmits<{
  previous: []
  next: []
  retry: []
  select: [day: CalendarDay]
}>()
</script>

<template>
  <section class="rounded-[2rem] bg-white/80 p-6 shadow-[0_24px_90px_-40px_rgba(15,23,42,0.4)] ring-1 ring-white/70 sm:p-8">
    <CalendarMonthHeader :month-label="monthLabel" @previous="$emit('previous')" @next="$emit('next')" />

    <div class="mt-6">
      <LoadingState v-if="loading" />
      <ErrorState v-else-if="error" :message="error" @retry="$emit('retry')" />
      <CalendarGrid v-else :days="days" @select="$emit('select', $event)" />
    </div>
  </section>
</template>