<script setup lang="ts">
import BaseButton from './BaseButton.vue'

defineProps<{
  message: string
}>()

defineEmits<{
  retry: []
}>()
</script>

<template>
  <div class="flex min-h-[220px] flex-col items-center justify-center gap-4 rounded-[1.75rem] border border-rose-200 bg-rose-50 p-8 text-center text-rose-700">
    <p class="max-w-sm text-sm font-medium">{{ message }}</p>
    <BaseButton variant="secondary" @click="$emit('retry')">Tentar novamente</BaseButton>
  </div>
</template>