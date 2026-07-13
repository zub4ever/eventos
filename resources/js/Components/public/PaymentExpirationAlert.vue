<script setup lang="ts">
defineProps<{
  expiresAt: string | null
}>()
</script>

<template>
  <div v-if="expiresAt" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
    Pagamento disponível até <strong>{{ new Intl.DateTimeFormat('pt-BR', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(expiresAt)) }}</strong>.
  </div>
</template>