<script setup lang="ts">
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import BookingCalendar from '../Components/public/BookingCalendar.vue'
import BookingWizard from '../Components/public/BookingWizard.vue'
import PublicTenantLayout from '../Components/public/PublicTenantLayout.vue'
import TenantHeader from '../Components/public/TenantHeader.vue'
import { useCalendarAvailability } from '../composables/useCalendarAvailability'
import { useTenantTheme } from '../composables/useTenantTheme'
import type { CalendarDay, PortalPageProps } from '../types/portal'

const page = usePage<PortalPageProps>()
const tenant = page.props.tenant
const currentUser = page.props.auth.user

useTenantTheme(() => tenant.themeColor)

const calendar = useCalendarAvailability()
const wizardRef = ref<InstanceType<typeof BookingWizard> | null>(null)

function openWizard(day: CalendarDay): void {
  if (!day.available) {
    return
  }

  wizardRef.value?.open(day)
}
</script>

<template>
  <PublicTenantLayout>
    <div class="space-y-10">
      <TenantHeader :tenant="tenant" />

      <BookingCalendar
        :days="calendar.days.value"
        :loading="calendar.loading.value"
        :error="calendar.error.value"
        :month-label="calendar.monthLabel.value"
        @previous="calendar.previousMonth"
        @next="calendar.nextMonth"
        @retry="calendar.fetchMonth"
        @select="openWizard"
      />

      <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-[1.7rem] bg-white/75 p-6 ring-1 ring-slate-200">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Disponibilidade segura</p>
          <h3 class="mt-3 text-xl font-semibold text-slate-900">Calendário público sem vazamento de dados</h3>
          <p class="mt-3 text-sm leading-6 text-slate-600">O calendário público retorna somente ocupação e disponibilidade. Nenhum dado pessoal ou financeiro aparece antes da autenticação.</p>
        </article>
        <article class="rounded-[1.7rem] bg-white/75 p-6 ring-1 ring-slate-200">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Fluxo guiado</p>
          <h3 class="mt-3 text-xl font-semibold text-slate-900">Reserva em quatro etapas</h3>
          <p class="mt-3 text-sm leading-6 text-slate-600">Cadastro rápido, escolha de período, conferência e checkout com retorno dinâmico de PIX, boleto ou fluxo seguro de cartão.</p>
        </article>
        <article class="rounded-[1.7rem] bg-slate-950 p-6 text-white">
          <p class="text-xs uppercase tracking-[0.3em] text-white/60">Sua sessão</p>
          <h3 class="mt-3 text-xl font-semibold">{{ currentUser ? `Olá, ${currentUser.name.split(' ')[0]}` : 'Ainda não autenticado' }}</h3>
          <p class="mt-3 text-sm leading-6 text-white/70">{{ currentUser ? 'Você já pode pular o cadastro e seguir direto para a escolha do período.' : 'Ao clicar em uma data livre, abriremos um modal com login ou cadastro rápido.' }}</p>
        </article>
      </section>
    </div>

    <BookingWizard ref="wizardRef" :tenant="tenant" :initial-user="currentUser" />
  </PublicTenantLayout>
</template>