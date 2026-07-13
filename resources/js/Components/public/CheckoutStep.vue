<script setup lang="ts">
import { ref } from 'vue'
import type { Booking, CheckoutResponse, PaymentMethod } from '../../types/portal'
import BaseButton from './BaseButton.vue'
import BoletoCheckout from './BoletoCheckout.vue'
import CardCheckout from './CardCheckout.vue'
import PaymentExpirationAlert from './PaymentExpirationAlert.vue'
import PixCheckout from './PixCheckout.vue'

const props = defineProps<{
  booking: Booking
  checkout: CheckoutResponse | null
  loading: boolean
}>()

const emit = defineEmits<{
  submit: [method: PaymentMethod]
}>()

const method = ref<PaymentMethod>('pix')

function submit(): void {
  emit('submit', method.value)
}
</script>

<template>
  <div>
    <p class="text-sm uppercase tracking-[0.3em] text-[var(--tenant-primary-ink)]">Etapa 4</p>
    <h3 class="mt-3 text-2xl font-semibold text-slate-900">Pagamento da reserva</h3>
    <p class="mt-2 text-sm text-slate-600">Escolha a forma de pagamento disponível e siga as instruções dinâmicas retornadas pelo backend.</p>

    <div class="mt-6 flex flex-wrap gap-3">
      <button v-for="paymentMethod in ['pix', 'boleto', 'credit_card'] as PaymentMethod[]" :key="paymentMethod" type="button" class="rounded-full px-4 py-2 text-sm font-semibold transition" :class="method === paymentMethod ? 'bg-[var(--tenant-primary)] text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200'" @click="method = paymentMethod">
        {{ paymentMethod === 'pix' ? 'PIX' : paymentMethod === 'boleto' ? 'Boleto' : 'Cartão' }}
      </button>
    </div>

    <div class="mt-6 space-y-4">
      <PaymentExpirationAlert :expires-at="checkout?.expires_at || booking.payment_expires_at" />

      <BaseButton :disabled="loading" @click="submit">
        {{ loading ? 'Carregando checkout...' : 'Gerar instruções de pagamento' }}
      </BaseButton>

      <PixCheckout v-if="checkout && checkout.payment_method === 'pix'" :checkout="checkout" />
      <BoletoCheckout v-else-if="checkout && checkout.payment_method === 'boleto'" :checkout="checkout" />
      <CardCheckout v-else-if="checkout && checkout.payment_method === 'credit_card'" />
    </div>
  </div>
</template>