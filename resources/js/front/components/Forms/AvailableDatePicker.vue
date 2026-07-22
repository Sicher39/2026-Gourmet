<script setup lang="ts">
import { computed, ref, watch } from 'vue'

type CalendarDay = {
    date: Date
    value: string
    dayNumber: number
    isCurrentMonth: boolean
    isSelected: boolean
    isToday: boolean
    unavailableReason: string | null
}

const props = withDefaults(
    defineProps<{
        modelValue: string
        minDate?: string
        allowWeekendDates?: boolean
        unavailableHolidayDates?: string[]
        placeholder?: string
    }>(),
    {
        minDate: '',
        allowWeekendDates: false,
        unavailableHolidayDates: () => [],
        placeholder: 'Vyberte datum'
    }
)

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

const isOpen = ref(false)
const visibleMonth = ref(startOfMonth(props.modelValue ? parseDateValue(props.modelValue) : minimumVisibleDate()))
const unavailableHolidayDateSet = computed(() => new Set(props.unavailableHolidayDates))
const monthFormatter = new Intl.DateTimeFormat('cs-CZ', { month: 'long', year: 'numeric' })
const selectedDateLabel = computed(() => (props.modelValue ? formatDisplayDate(parseDateValue(props.modelValue)) : props.placeholder))
const selectedDateUnavailableReason = computed(() => (props.modelValue ? unavailableReason(props.modelValue) : null))

watch(
    () => props.modelValue,
    (value) => {
        if (value) {
            visibleMonth.value = startOfMonth(parseDateValue(value))
        }
    }
)

const calendarDays = computed<CalendarDay[]>(() => {
    const firstDay = startOfMonth(visibleMonth.value)
    const firstGridDay = addDays(firstDay, -((firstDay.getDay() + 6) % 7))
    const todayValue = toDateValue(new Date())

    return Array.from({ length: 42 }, (_, index): CalendarDay => {
        const date = addDays(firstGridDay, index)
        const value = toDateValue(date)

        return {
            date,
            value,
            dayNumber: date.getDate(),
            isCurrentMonth: date.getMonth() === visibleMonth.value.getMonth(),
            isSelected: props.modelValue === value,
            isToday: todayValue === value,
            unavailableReason: unavailableReason(value)
        }
    })
})

function previousMonth(): void {
    visibleMonth.value = addMonths(visibleMonth.value, -1)
}

function nextMonth(): void {
    visibleMonth.value = addMonths(visibleMonth.value, 1)
}

function selectDay(day: CalendarDay): void {
    if (day.unavailableReason !== null) {
        return
    }

    emit('update:modelValue', day.value)
    isOpen.value = false
}

function clearDate(): void {
    emit('update:modelValue', '')
    isOpen.value = false
}

function unavailableReason(value: string): string | null {
    if (props.minDate && value < props.minDate) {
        return `Termín je příliš brzy. Nejbližší možný termín je ${formatDisplayDate(parseDateValue(props.minDate))}.`
    }

    const date = parseDateValue(value)
    const day = date.getDay()

    if (!props.allowWeekendDates && (day === 0 || day === 6)) {
        return 'Víkendy nejsou v aktuálním nastavení nabízené.'
    }

    if (unavailableHolidayDateSet.value.has(value)) {
        return 'Tento svátek není v aktuálním nastavení nabízený.'
    }

    return null
}

function minimumVisibleDate(): Date {
    return props.minDate ? parseDateValue(props.minDate) : new Date()
}

function parseDateValue(value: string): Date {
    const [year, month, day] = value.split('-').map(Number)

    return new Date(year, month - 1, day)
}

function toDateValue(date: Date): string {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
}

function startOfMonth(date: Date): Date {
    return new Date(date.getFullYear(), date.getMonth(), 1)
}

function addDays(date: Date, days: number): Date {
    const result = new Date(date)
    result.setDate(result.getDate() + days)

    return result
}

function addMonths(date: Date, months: number): Date {
    return new Date(date.getFullYear(), date.getMonth() + months, 1)
}

function formatDisplayDate(date: Date): string {
    return new Intl.DateTimeFormat('cs-CZ', {
        day: 'numeric',
        month: 'numeric',
        year: 'numeric'
    }).format(date)
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="form-input flex min-h-13 w-full items-center justify-between gap-3 text-left transition hover:border-accent focus:border-accent focus:outline-none"
            :class="selectedDateUnavailableReason ? 'border-yellow-300/70 text-yellow-100' : ''"
            @click="isOpen = !isOpen"
        >
            <span :class="modelValue ? 'text-light' : 'text-light/45'">{{ selectedDateLabel }}</span>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-6 shrink-0 text-accent transition-transform"
                :class="isOpen ? 'rotate-180' : ''"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <p v-if="selectedDateUnavailableReason" class="mt-2 text-sm text-yellow-200">
            {{ selectedDateUnavailableReason }}
        </p>

        <div
            v-if="isOpen"
            class="absolute left-0 z-40 mt-3 max-h-[min(80vh,560px)] w-full min-w-[320px] overflow-y-auto border border-accent/40 bg-dark/95 p-4 text-light shadow-2xl shadow-black/40 backdrop-blur md:w-[390px]"
        >
            <div class="mb-4 flex items-center justify-between gap-3">
                <button
                    type="button"
                    class="flex size-10 items-center justify-center border border-accent/30 text-xl text-accent transition hover:bg-accent hover:text-dark"
                    aria-label="Předchozí měsíc"
                    @click="previousMonth"
                >
                    ‹
                </button>
                <div class="text-center">
                    <p class="font-main text-2xl text-accent first-letter:uppercase">
                        {{ monthFormatter.format(visibleMonth) }}
                    </p>
                    <p class="text-xs uppercase tracking-[0.22em] text-light/45">Dostupné termíny</p>
                </div>
                <button
                    type="button"
                    class="flex size-10 items-center justify-center border border-accent/30 text-xl text-accent transition hover:bg-accent hover:text-dark"
                    aria-label="Další měsíc"
                    @click="nextMonth"
                >
                    ›
                </button>
            </div>

            <div class="grid grid-cols-7 gap-1 text-center text-xs uppercase tracking-wide text-light/50">
                <span>Po</span>
                <span>Út</span>
                <span>St</span>
                <span>Čt</span>
                <span>Pá</span>
                <span>So</span>
                <span>Ne</span>
            </div>

            <div class="mt-2 grid grid-cols-7 gap-1">
                <button
                    v-for="day in calendarDays"
                    :key="day.value"
                    type="button"
                    class="group relative flex aspect-square items-center justify-center border text-sm transition"
                    :class="[
                        day.isSelected
                            ? 'border-accent bg-accent text-dark'
                            : day.unavailableReason
                              ? 'cursor-not-allowed border-transparent text-light/20 line-through'
                              : 'border-accent/10 text-light hover:border-accent hover:bg-accent/15',
                        !day.isCurrentMonth && !day.isSelected ? 'opacity-35' : '',
                        day.isToday && !day.isSelected ? 'border-accent-green/60 text-accent-green' : ''
                    ]"
                    :disabled="day.unavailableReason !== null"
                    :title="day.unavailableReason ?? formatDisplayDate(day.date)"
                    @click="selectDay(day)"
                >
                    {{ day.dayNumber }}
                    <span
                        v-if="day.unavailableReason"
                        class="pointer-events-none absolute inset-x-1 bottom-1 h-px bg-light/20"
                    />
                </button>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-accent/15 pt-4 text-sm">
                <div class="flex flex-wrap gap-4 text-light/60">
                    <span class="inline-flex items-center gap-2">
                        <span class="size-2 bg-accent"></span>
                        vybraný termín
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="size-2 border border-accent-green/70"></span>
                        dnešní den
                    </span>
                </div>
                <button type="button" class="text-accent underline-offset-4 hover:underline" @click="clearDate">
                    Vymazat
                </button>
            </div>
        </div>
    </div>
</template>
