<script setup lang="ts">
import { reactive } from 'vue'
import type { LoginPayload, RegisterPayload } from '../../types/portal'
import BaseButton from './BaseButton.vue'

const props = defineProps<{
  mode: 'login' | 'register'
  loading: boolean
}>()

const emit = defineEmits<{
  switchMode: [mode: 'login' | 'register']
  submitLogin: [payload: LoginPayload]
  submitRegister: [payload: RegisterPayload]
}>()

const loginForm = reactive<LoginPayload>({
  email: '',
  password: '',
})

const registerForm = reactive<RegisterPayload>({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

function submit(): void {
  if (props.mode === 'login') {
    emit('submitLogin', { ...loginForm })
    return
  }

  emit('submitRegister', { ...registerForm })
}
</script>

<template>
  <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
    <div class="rounded-[1.5rem] bg-slate-950 p-6 text-white">
      <p class="text-sm uppercase tracking-[0.3em] text-white/60">Etapa 1</p>
      <h3 class="mt-3 text-2xl font-semibold">Identifique-se para reservar</h3>
      <p class="mt-3 text-sm leading-6 text-white/70">
        Você pode entrar com uma conta existente ou fazer um cadastro rápido para seguir com a reserva sem sair desta página.
      </p>
    </div>

    <div class="rounded-[1.5rem] bg-white p-6 ring-1 ring-slate-200">
      <div class="mb-5 flex gap-2 rounded-full bg-slate-100 p-1">
        <button class="flex-1 rounded-full px-4 py-2 text-sm font-semibold" :class="mode === 'register' ? 'bg-white shadow-sm' : 'text-slate-500'" @click="emit('switchMode', 'register')">Cadastro rápido</button>
        <button class="flex-1 rounded-full px-4 py-2 text-sm font-semibold" :class="mode === 'login' ? 'bg-white shadow-sm' : 'text-slate-500'" @click="emit('switchMode', 'login')">Entrar</button>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <template v-if="mode === 'register'">
          <input v-model="registerForm.name" class="w-full rounded-2xl border border-slate-200 px-4 py-3" type="text" placeholder="Nome completo" required>
          <input v-model="registerForm.email" class="w-full rounded-2xl border border-slate-200 px-4 py-3" type="email" placeholder="E-mail" required>
          <input v-model="registerForm.phone" class="w-full rounded-2xl border border-slate-200 px-4 py-3" type="text" placeholder="WhatsApp" required>
          <input v-model="registerForm.password" class="w-full rounded-2xl border border-slate-200 px-4 py-3" type="password" placeholder="Senha" required>
          <input v-model="registerForm.password_confirmation" class="w-full rounded-2xl border border-slate-200 px-4 py-3" type="password" placeholder="Confirme a senha" required>
        </template>

        <template v-else>
          <input v-model="loginForm.email" class="w-full rounded-2xl border border-slate-200 px-4 py-3" type="email" placeholder="E-mail" required>
          <input v-model="loginForm.password" class="w-full rounded-2xl border border-slate-200 px-4 py-3" type="password" placeholder="Senha" required>
        </template>

        <BaseButton type="submit" :block="true" :disabled="loading">
          {{ loading ? 'Processando...' : mode === 'register' ? 'Criar acesso e continuar' : 'Entrar e continuar' }}
        </BaseButton>
      </form>
    </div>
  </div>
</template>