import { ref, watch } from 'vue'

const savedTheme = localStorage.getItem('theme') || 'light'
export const theme = ref(savedTheme)

const applyTheme = (t) => {
  const root = document.documentElement
  root.classList.remove('dark', 'theme-dark-blue', 'theme-dark-purple')
  if (t === 'dark-purple') {
    root.classList.add('dark', 'theme-dark-purple')
  }
}

watch(theme, (t) => {
  localStorage.setItem('theme', t)
  applyTheme(t)
}, { immediate: true })

export function setTheme(t) {
  theme.value = t
}

export function cycleTheme() {
  theme.value = theme.value === 'light' ? 'dark-purple' : 'light'
}
