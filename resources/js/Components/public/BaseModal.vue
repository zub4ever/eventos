<script setup lang="ts">
defineProps<{
  open: boolean
}>()

defineEmits<{
  close: []
}>()
</script>

<template>
  <teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-4 backdrop-blur-sm sm:items-center">
      <div class="absolute inset-0" @click="$emit('close')" />
      <div class="relative z-10 max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[2rem] bg-[#fffdf8] p-6 shadow-2xl sm:p-8">
        <slot />
      </div>
    </div>
  </teleport>
</template>