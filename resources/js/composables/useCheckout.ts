import axios from 'axios'
import { ref } from 'vue'
import type { CheckoutResponse, PaymentMethod } from '../types/portal'

export function useCheckout() {
  const checkout = ref<CheckoutResponse | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function createCheckout(bookingId: string, paymentMethod: PaymentMethod): Promise<CheckoutResponse | null> {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post<CheckoutResponse>(`/portal/bookings/${bookingId}/payments`, {
        payment_method: paymentMethod,
      })

      checkout.value = response.data

      return response.data
    } catch (caughtError: unknown) {
      error.value = 'Não foi possível iniciar o pagamento. Tente novamente.'
      console.error(caughtError)
      return null
    } finally {
      loading.value = false
    }
  }

  return {
    checkout,
    loading,
    error,
    createCheckout,
  }
}