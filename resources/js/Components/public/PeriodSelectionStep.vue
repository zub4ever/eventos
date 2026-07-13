<script setup lang="ts">
import type { BookingPeriod, TenantConfig } from '../../types/portal'

defineProps<{
  tenant: TenantConfig
  selected: BookingPeriod
}>()

defineEmits<{
  select: [period: BookingPeriod]
}>()

function money(value: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}
</script>

<template>
  <div>
    <p class="text-sm uppercase tracking-[0.3em] text-[var(--tenant-primary-ink)]">Etapa 2</p>
    <h3 class="mt-3 text-2xl font-semibold text-slate-900">Escolha o período ideal</h3>
    <div class="mt-6 grid gap-4 md:grid-cols-2">
      <button type="button" class="rounded-[1.6rem] border p-5 text-left transition" :class="selected === 'regular' ? 'border-[var(--tenant-primary)] bg-[var(--tenant-primary-soft)]' : 'border-slate-200 bg-white hover:border-slate-300'" @click="$emit('select', 'regular')">
        <span class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-500">Regular</span>
        <strong class="mt-3 block text-2xl text-slate-900">09h às 19h</strong>
        <p class="mt-3 text-sm text-slate-600">Ideal para eventos enxutos com operação concentrada ao longo do dia.</p>
        <p class="mt-6 text-lg font-semibold text-[var(--tenant-primary-ink)]">{{ money(tenant.prices.regular) }}</p>
      </button>

      <button type="button" class="rounded-[1.6rem] border p-5 text-left transition" :class="selected === 'extended' ? 'border-[var(--tenant-primary)] bg-[var(--tenant-primary-soft)]' : 'border-slate-200 bg-white hover:border-slate-300'" @click="$emit('select', 'extended')">
        <span class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-500">Estendido</span>
        <strong class="mt-3 block text-2xl text-slate-900">09h às 22h</strong>
        <p class="mt-3 text-sm text-slate-600">Perfeito para eventos mais longos, com encerramento estendido no mesmo dia.</p>
        <p class="mt-6 text-lg font-semibold text-[var(--tenant-primary-ink)]">{{ money(tenant.prices.extended) }}</p>
      </button>
    </div>
  </div>
</template>