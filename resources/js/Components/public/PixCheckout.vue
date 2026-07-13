<script setup lang="ts">
import type { CheckoutResponse } from '../../types/portal'
import BaseButton from './BaseButton.vue'

defineProps<{
  checkout: CheckoutResponse
}>()

async function copyCode(value: string | null): Promise<void> {
  if (!value) {
    return
  }

  await navigator.clipboard.writeText(value)
}
</script>

<template>
  <div class="space-y-4 rounded-[1.4rem] bg-white p-5 ring-1 ring-slate-200">
    <p class="text-sm text-slate-600">Escaneie o QR Code ou copie o código PIX para concluir o pagamento.</p>
    <div v-if="checkout.pix_qr_code" class="overflow-hidden rounded-[1.2rem] bg-slate-50 p-4 text-xs break-all text-slate-600">
      {{ checkout.pix_qr_code }}
    </div>
    <div class="rounded-[1.2rem] bg-slate-950 p-4 text-sm text-white break-all">
      {{ checkout.pix_copy_paste || 'Código PIX não disponível.' }}
    </div>
    <BaseButton variant="secondary" @click="copyCode(checkout.pix_copy_paste)">Copiar código PIX</BaseButton>
  </div>
</template>