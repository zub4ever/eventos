<script setup lang="ts">
import type { CheckoutResponse } from '../../types/portal'

defineProps<{
  checkout: CheckoutResponse
}>()
</script>

<template>
  <div class="rounded-[1.4rem] bg-white p-5 ring-1 ring-slate-200">
    <p class="text-sm text-slate-600">Seu boleto já está disponível.</p>
    <a v-if="checkout.boleto_url" :href="checkout.boleto_url" target="_blank" rel="noreferrer" class="mt-4 inline-flex rounded-full bg-[var(--tenant-primary)] px-5 py-3 text-sm font-semibold text-white">
      Visualizar boleto
    </a>
  </div>
</template>