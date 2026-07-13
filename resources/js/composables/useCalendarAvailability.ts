import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import type { CalendarDay } from '../types/portal'

interface CalendarAvailabilityResponse {
    data: CalendarDay[]
}

function pad(value: number): string {
    return value.toString().padStart(2, '0')
}

function monthKey(date: Date): string {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}`
}

export function useCalendarAvailability() {
    const currentMonth = ref(new Date())
    const days = ref<CalendarDay[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)

    const monthLabel = computed(() => new Intl.DateTimeFormat('pt-BR', {
        month: 'long',
        year: 'numeric',
    }).format(currentMonth.value))

    async function fetchMonth(): Promise<void> {
        loading.value = true
        error.value = null

        try {
            const response = await axios.get<CalendarAvailabilityResponse>('/api/public/calendar', {
                params: {
                    month: monthKey(currentMonth.value),
                },
            })

            days.value = response.data.data
        } catch (caughtError) {
            error.value = 'Não foi possível carregar a disponibilidade deste mês.'
            console.error(caughtError)
        } finally {
            loading.value = false
        }
    }

    function nextMonth(): void {
        currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1)
        void fetchMonth()
    }

    function previousMonth(): void {
        currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1)
        void fetchMonth()
    }

    onMounted(() => {
        void fetchMonth()
    })

    return {
        currentMonth,
        days,
        loading,
        error,
        monthLabel,
        fetchMonth,
        nextMonth,
        previousMonth,
    }
}