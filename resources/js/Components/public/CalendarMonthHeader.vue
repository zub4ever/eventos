<script setup lang="ts">
import BaseButton from './BaseButton.vue'

defineProps<{
  monthLabel: string
}>()

defineEmits<{
  previous: []
  next: []
}>()
</script>

<template>
  <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--tenant-primary-ink)]">Calendário do espaço</p>
      <h2 class="mt-2 text-2xl font-semibold capitalize text-slate-900">{{ monthLabel }}</h2>
    </div>

    <div class="flex gap-3">
      <BaseButton variant="ghost" @click="$emit('previous')">Mês anterior</BaseButton>
      <BaseButton variant="secondary" @click="$emit('next')">Próximo mês</BaseButton>
    </div>
  </div>
</template>