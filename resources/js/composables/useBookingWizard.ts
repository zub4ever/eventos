import axios from 'axios'
import { computed, ref } from 'vue'
import type {
  AuthUser,
  Booking,
  BookingPeriod,
  CalendarDay,
  LoginPayload,
  RegisterPayload,
  TenantConfig,
} from '../types/portal'

type WizardStep = 1 | 2 | 3 | 4

function addBusinessDays(date: Date, days: number): Date {
  const result = new Date(date)
  let remaining = days

  while (remaining > 0) {
    result.setDate(result.getDate() + 1)
    const day = result.getDay()

    if (day === 0 || day === 6) {
      continue
    }

    remaining -= 1
  }

  return result
}

export function useBookingWizard(tenant: TenantConfig, initialUser: AuthUser | null) {
  const isOpen = ref(false)
  const step = ref<WizardStep>(initialUser ? 2 : 1)
  const selectedDay = ref<CalendarDay | null>(null)
  const selectedPeriod = ref<BookingPeriod>('regular')
  const authMode = ref<'login' | 'register'>('register')
  const user = ref<AuthUser | null>(initialUser)
  const booking = ref<Booking | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const selectedPrice = computed(() => tenant.prices[selectedPeriod.value])
  const estimatedExpiration = computed(() => {
    const date = addBusinessDays(new Date(), 2)

    return new Intl.DateTimeFormat('pt-BR', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    }).format(date)
  })

  function open(day: CalendarDay): void {
    selectedDay.value = day
    step.value = user.value ? 2 : 1
    booking.value = null
    error.value = null
    isOpen.value = true
  }

  function close(): void {
    isOpen.value = false
    error.value = null
  }

  function selectPeriod(period: BookingPeriod): void {
    selectedPeriod.value = period
    step.value = 3
  }

  async function login(payload: LoginPayload): Promise<boolean> {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post<{ user: AuthUser }>('/portal/auth/login', payload)
      user.value = response.data.user
      step.value = 2
      return true
    } catch (caughtError) {
      error.value = 'Não foi possível entrar com esses dados.'
      console.error(caughtError)
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(payload: RegisterPayload): Promise<boolean> {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post<{ user: AuthUser }>('/portal/auth/register', payload)
      user.value = response.data.user
      step.value = 2
      return true
    } catch (caughtError) {
      error.value = 'Não foi possível concluir o cadastro agora.'
      console.error(caughtError)
      return false
    } finally {
      loading.value = false
    }
  }

  async function createBooking(): Promise<Booking | null> {
    if (!selectedDay.value) {
      error.value = 'Selecione uma data disponível para continuar.'
      return null
    }

    loading.value = true
    error.value = null

    try {
      const response = await axios.post<Booking>('/portal/bookings', {
        event_date: selectedDay.value.date,
        period_type: selectedPeriod.value,
      })

      booking.value = response.data
      step.value = 4
      return response.data
    } catch (caughtError) {
      error.value = 'Esta data acabou de ficar indisponível ou houve um erro ao criar a reserva.'
      console.error(caughtError)
      return null
    } finally {
      loading.value = false
    }
  }

  return {
    isOpen,
    step,
    selectedDay,
    selectedPeriod,
    selectedPrice,
    authMode,
    user,
    booking,
    loading,
    error,
    estimatedExpiration,
    open,
    close,
    selectPeriod,
    login,
    register,
    createBooking,
  }
}