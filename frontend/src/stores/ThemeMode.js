import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  const isDark = ref(JSON.parse(localStorage.getItem('darkTheme') || 'false'))
  
  // Fungsi untuk mengupdate theme
  const updateTheme = (darkMode) => {
    isDark.value = darkMode
    localStorage.setItem('darkTheme', JSON.stringify(darkMode))
    
    // Update class pada document element
    if (darkMode) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
    
    console.log('Theme updated:', darkMode ? 'dark' : 'light')
    console.log('HTML classList:', document.documentElement.classList.toString())
  }
  
  // Watch untuk perubahan isDark
  watch(isDark, (newVal) => {
    updateTheme(newVal)
  }, { immediate: true })
  
  // Set initial theme saat store di-initialize
  updateTheme(isDark.value)
  
  return { 
    isDark,
    updateTheme 
  }
})