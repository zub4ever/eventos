<script setup lang="ts">
import type { TenantConfig } from '../../types/portal'

defineProps<{
  tenant: TenantConfig
}>()

function money(value: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}
</script>

<template>
  <section class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
    <div>
      <p class="inline-flex rounded-full bg-[var(--tenant-primary-soft)] px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-[var(--tenant-primary-ink)]">
        Agendamentos online
      </p>
      <h1 class="mt-6 max-w-3xl text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl">
        {{ tenant.name }}
      </h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
        Consulte o calendário do mês, escolha o período ideal e finalize a sua reserva com pagamento seguro e prazo bem definido.
      </p>
      <div class="mt-8 flex flex-wrap gap-4 text-sm text-slate-700">
        <div class="rounded-2xl bg-white/85 px-4 py-3 shadow-sm ring-1 ring-slate-200">
          <strong class="block text-slate-900">Regular</strong>
          09h às 19h · {{ money(tenant.prices.regular) }}
        </div>
        <div class="rounded-2xl bg-white/85 px-4 py-3 shadow-sm ring-1 ring-slate-200">
          <strong class="block text-slate-900">Estendido</strong>
          09h às 22h · {{ money(tenant.prices.extended) }}
        </div>
      </div>
    </div>

    <div class="relative overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-[0_30px_80px_-30px_rgba(15,118,110,0.5)]">
      <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-[var(--tenant-primary)]/40 blur-3xl" />
      <div class="absolute bottom-0 left-0 h-28 w-28 rounded-full bg-white/10 blur-3xl" />

      <div class="relative flex items-center gap-4">
        <div class="flex h-18 w-18 items-center justify-center overflow-hidden rounded-3xl bg-white/10 ring-1 ring-white/15">
          <img v-if="tenant.logoUrl" :src="tenant.logoUrl" :alt="tenant.name" class="h-full w-full object-cover" />
          <span v-else class="text-2xl font-semibold">{{ tenant.name.charAt(0) }}</span>
        </div>
        <div>
          <p class="text-sm text-white/60">Portal do tenant</p>
          <p class="text-lg font-semibold">{{ tenant.subdomain }}.saas.com.br</p>
        </div>
      </div>

      <div class="relative mt-10 rounded-[1.5rem] bg-white/8 p-5 ring-1 ring-white/10">
        <p class="text-sm text-white/70">Como funciona</p>
        <ul class="mt-4 space-y-3 text-sm text-white/90">
          <li>1. Escolha uma data disponível no calendário.</li>
          <li>2. Entre na sua conta ou faça um cadastro rápido.</li>
          <li>3. Selecione o período e confirme o valor recalculado pelo backend.</li>
          <li>4. Gere seu PIX, boleto ou fluxo compatível com cartão.</li>
        </ul>
      </div>
    </div>
  </section>
</template>