import { ref, watch } from 'vue'

// 3 themes: 'light' | 'dark-blue' | 'dark-purple'
const savedTheme = localStorage.getItem('theme') || 'light'
export const theme = ref(savedTheme)

const applyTheme = (t) => {
  const root = document.documentElement
  root.classList.remove('dark', 'theme-dark-blue', 'theme-dark-purple')

  if (t === 'dark-blue') {
    root.classList.add('dark', 'theme-dark-blue')
  } else if (t === 'dark-purple') {
    root.classList.add('dark', 'theme-dark-purple')
  }
  // 'light' = no extra class
}

watch(theme, (t) => {
  localStorage.setItem('theme', t)
  applyTheme(t)
}, { immediate: true })

export function cycleTheme() {
  const order = ['light', 'dark-blue', 'dark-purple']
  const idx = order.indexOf(theme.value)
  theme.value = order[(idx + 1) % order.length]
}

export function setTheme(t) {
  theme.value = t
}

export const themeLabels = {
  'light': '☀️ Blanc',
  'dark-blue': '🌊 Sombre Bleu',
  'dark-purple': '🌌 Sombre Violet',
}
