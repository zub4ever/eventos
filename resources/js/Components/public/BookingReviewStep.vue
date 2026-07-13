<script setup lang="ts">
import type { AuthUser, BookingPeriod, CalendarDay, TenantConfig } from '../../types/portal'
import BaseButton from './BaseButton.vue'

defineProps<{
  tenant: TenantConfig
  user: AuthUser | null
  selectedDay: CalendarDay
  selectedPeriod: BookingPeriod
  estimatedExpiration: string
  loading: boolean
}>()

defineEmits<{
  confirm: []
}>()

function money(value: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}

function periodLabel(period: BookingPeriod): string {
  return period === 'extended' ? 'Estendido — 09h às 22h' : 'Regular — 09h às 19h'
}
</script>

<template>
  <div class="grid gap-6 lg:grid-cols-[1fr_0.85fr]">
    <div>
      <p class="text-sm uppercase tracking-[0.3em] text-[var(--tenant-primary-ink)]">Etapa 3</p>
      <h3 class="mt-3 text-2xl font-semibold text-slate-900">Confirme os dados antes do pagamento</h3>
      <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-[1.4rem] bg-white p-5 ring-1 ring-slate-200">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Data</p>
          <strong class="mt-2 block text-lg text-slate-900">{{ new Intl.DateTimeFormat('pt-BR', { dateStyle: 'full' }).format(new Date(`${selectedDay.date}T00:00:00`)) }}</strong>
        </div>
        <div class="rounded-[1.4rem] bg-white p-5 ring-1 ring-slate-200">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Período</p>
          <strong class="mt-2 block text-lg text-slate-900">{{ periodLabel(selectedPeriod) }}</strong>
        </div>
        <div class="rounded-[1.4rem] bg-white p-5 ring-1 ring-slate-200">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Preço</p>
          <strong class="mt-2 block text-lg text-slate-900">{{ money(tenant.prices[selectedPeriod]) }}</strong>
        </div>
        <div class="rounded-[1.4rem] bg-white p-5 ring-1 ring-slate-200">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Prazo previsto para pagamento</p>
          <strong class="mt-2 block text-lg text-slate-900">{{ estimatedExpiration }}</strong>
        </div>
      </div>
    </div>

    <div class="rounded-[1.7rem] bg-slate-950 p-6 text-white">
      <p class="text-xs uppercase tracking-[0.3em] text-white/60">Dados do cliente</p>
      <div class="mt-5 space-y-3 text-sm text-white/85">
        <p><strong class="text-white">Nome:</strong> {{ user?.name }}</p>
        <p><strong class="text-white">E-mail:</strong> {{ user?.email }}</p>
        <p><strong class="text-white">WhatsApp:</strong> {{ user?.phone }}</p>
      </div>

      <BaseButton class="mt-8" :block="true" :disabled="loading" @click="$emit('confirm')">
        {{ loading ? 'Criando reserva...' : 'Criar reserva e seguir para pagamento' }}
      </BaseButton>
    </div>
  </div>
</template>