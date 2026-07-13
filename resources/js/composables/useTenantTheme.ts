import { onBeforeUnmount, watchEffect } from 'vue'

function clamp(value: number, min: number, max: number): number {
  return Math.min(Math.max(value, min), max)
}

function hexToRgb(hex: string): [number, number, number] {
  const sanitized = hex.replace('#', '')
  const expanded = sanitized.length === 3
    ? sanitized.split('').map((char) => char + char).join('')
    : sanitized

  const int = Number.parseInt(expanded, 16)

  return [
    (int >> 16) & 255,
    (int >> 8) & 255,
    int & 255,
  ]
}

function rgbToHex([red, green, blue]: [number, number, number]): string {
  return `#${[red, green, blue]
    .map((channel) => clamp(channel, 0, 255).toString(16).padStart(2, '0'))
    .join('')}`
}

function shift(hex: string, amount: number): string {
  const [red, green, blue] = hexToRgb(hex)

  return rgbToHex([
    red + amount,
    green + amount,
    blue + amount,
  ] as [number, number, number])
}

export function useTenantTheme(color: () => string) {
  watchEffect(() => {
    const themeColor = color() || '#0f766e'
    const root = document.documentElement

    root.style.setProperty('--tenant-primary', themeColor)
    root.style.setProperty('--tenant-primary-soft', shift(themeColor, 160))
    root.style.setProperty('--tenant-primary-ink', shift(themeColor, -90))
  })

  onBeforeUnmount(() => {
    const root = document.documentElement
    root.style.removeProperty('--tenant-primary')
    root.style.removeProperty('--tenant-primary-soft')
    root.style.removeProperty('--tenant-primary-ink')
  })
}