export type BookingPeriod = 'regular' | 'extended'
export type PaymentMethod = 'pix' | 'boleto' | 'credit_card'

export interface TenantConfig {
  id: string
  name: string
  subdomain: string
  logoUrl: string | null
  themeColor: string
  prices: {
    regular: number
    extended: number
  }
}

export interface CalendarDay {
  date: string
  available: boolean
}

export interface AuthUser {
  id: string
  name: string
  email: string
  phone: string | null
  role: string | null
}

export interface Booking {
  id: string
  user_id: string
  event_date: string
  period_type: BookingPeriod
  period_window: {
    start: string
    end: string
  }
  total_price: number
  status: string
  payment_expires_at: string | null
  created_at?: string | null
}

export interface CheckoutResponse {
  id: string
  booking_id: string
  gateway_name: string
  payment_method: PaymentMethod
  status: string
  amount: number
  pix_qr_code: string | null
  pix_copy_paste: string | null
  boleto_url: string | null
  expires_at: string | null
  gateway_transaction_id: string | null
}

export interface PortalPageProps {
  tenant: TenantConfig
  auth: {
    user: AuthUser | null
  }
}

export interface RegisterPayload {
  name: string
  email: string
  phone: string
  password: string
  password_confirmation: string
}

export interface LoginPayload {
  email: string
  password: string
}