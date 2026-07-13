<script setup lang="ts">
import { computed } from 'vue'
import { useBookingWizard } from '../../composables/useBookingWizard'
import { useCheckout } from '../../composables/useCheckout'
import type { AuthUser, CalendarDay, TenantConfig } from '../../types/portal'
import AuthStep from './AuthStep.vue'
import BaseModal from './BaseModal.vue'
import CheckoutStep from './CheckoutStep.vue'
import PeriodSelectionStep from './PeriodSelectionStep.vue'
import BookingReviewStep from './BookingReviewStep.vue'

const props = defineProps<{
  tenant: TenantConfig
  initialUser: AuthUser | null
}>()

const wizard = useBookingWizard(props.tenant, props.initialUser)
const checkout = useCheckout()

const headline = computed(() => {
  if (!wizard.selectedDay.value) {
    return 'Nova reserva'
  }

  return `Reserva para ${new Intl.DateTimeFormat('pt-BR', { dateStyle: 'full' }).format(new Date(`${wizard.selectedDay.value.date}T00:00:00`))}`
})

async function submitPayment(method: 'pix' | 'boleto' | 'credit_card'): Promise<void> {
  if (!wizard.booking.value) {
    return
  }

  await checkout.createCheckout(wizard.booking.value.id, method)
}

defineExpose({
  open: (day: CalendarDay) => wizard.open(day),
})
</script>

<template>
  <BaseModal :open="wizard.isOpen.value" @close="wizard.close">
    <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Fluxo de reserva</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ headline }}</h2>
      </div>
      <button type="button" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600" @click="wizard.close">Fechar</button>
    </div>

    <p v-if="wizard.error.value || checkout.error.value" class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
      {{ wizard.error.value || checkout.error.value }}
    </p>

    <div class="mt-6">
      <AuthStep
        v-if="wizard.step.value === 1"
        :mode="wizard.authMode.value"
        :loading="wizard.loading.value"
        @switch-mode="wizard.authMode.value = $event"
        @submit-login="wizard.login"
        @submit-register="wizard.register"
      />

      <PeriodSelectionStep
        v-else-if="wizard.step.value === 2"
        :tenant="tenant"
        :selected="wizard.selectedPeriod.value"
        @select="wizard.selectPeriod"
      />

      <BookingReviewStep
        v-else-if="wizard.step.value === 3 && wizard.selectedDay.value"
        :tenant="tenant"
        :user="wizard.user.value"
        :selected-day="wizard.selectedDay.value"
        :selected-period="wizard.selectedPeriod.value"
        :estimated-expiration="wizard.estimatedExpiration.value"
        :loading="wizard.loading.value"
        @confirm="wizard.createBooking"
      />

      <CheckoutStep
        v-else-if="wizard.step.value === 4 && wizard.booking.value"
        :booking="wizard.booking.value"
        :checkout="checkout.checkout.value"
        :loading="checkout.loading.value"
        @submit="submitPayment"
      />
    </div>
  </BaseModal>
</template>