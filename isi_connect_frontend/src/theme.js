import { ref, watch } from 'vue'

const savedTheme = localStorage.getItem('theme') || 'light'
export const theme = ref(savedTheme)

watch(theme, (newTheme) => {
  localStorage.setItem('theme', newTheme)
  if (newTheme === 'dark') {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}, { immediate: true })

export function toggleTheme() {
  theme.value = theme.value === 'light' ? 'dark' : 'light'
}
