<script setup lang="ts">
const props = withDefaults(defineProps<{
  variant?: 'primary' | 'secondary' | 'ghost'
  type?: 'button' | 'submit'
  block?: boolean
  disabled?: boolean
}>(), {
  variant: 'primary',
  type: 'button',
  block: false,
  disabled: false,
})

const variantClassMap = {
  primary: 'bg-[var(--tenant-primary)] text-white shadow-[0_18px_50px_-22px_var(--tenant-primary)] hover:brightness-110',
  secondary: 'bg-[var(--tenant-primary-soft)] text-[var(--tenant-primary-ink)] hover:brightness-95',
  ghost: 'bg-white/60 text-slate-700 ring-1 ring-slate-200 hover:bg-white',
}
</script>

<template>
  <button
    :type="props.type"
    :disabled="props.disabled"
    :class="[
      'inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60',
      variantClassMap[props.variant],
      props.block ? 'w-full' : '',
    ]"
  >
    <slot />
  </button>
</template>