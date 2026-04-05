<script setup>
import { ref, watch } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import Sidebar from '../components/Sidebar.vue'
import MessengerOverlay from '../components/MessengerOverlay.vue'
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'

const isMobileMenuOpen = ref(false)
const route = useRoute()

// Auto-close menu when route changes
watch(() => route.path, () => {
  isMobileMenuOpen.value = false
})

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}
</script>

<template>
  <div class="flex h-screen bg-white relative overflow-hidden font-inter">
    
    <!-- Background Digital Grid (Généralisé) -->
    <div class="absolute inset-0 pointer-events-none opacity-40 z-0">
      <div class="absolute inset-0 bg-[linear-gradient(rgba(14,165,233,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(14,165,233,0.03)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
    </div>

    <!-- Mobile Top Navigation Bar -->
    <div class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-6 z-[60] shadow-sm">
      <div class="flex items-center space-x-3">
        <div class="h-8 w-8 bg-slate-950 rounded-lg flex items-center justify-center">
            <span class="text-white font-black text-[10px]">ISI</span>
        </div>
        <span class="font-black uppercase tracking-widest text-[10px] text-slate-900">
          ISI <span class="text-sky-500">Connect</span>
        </span>
      </div>
      
      <button @click="toggleMobileMenu" class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-600 active:scale-90 transition-all">
        <Bars3Icon v-if="!isMobileMenuOpen" class="h-6 w-6" />
        <XMarkIcon v-else class="h-6 w-6" />
      </button>
    </div>

    <!-- Sidebar Drawer Wrapper -->
    <div 
      :class="[
        'fixed inset-0 z-[70] lg:relative lg:z-auto lg:translate-x-0 transition-transform duration-500 ease-in-out',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Overlay for mobile -->
      <div 
        v-if="isMobileMenuOpen" 
        @click="isMobileMenuOpen = false"
        class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm lg:hidden"
      ></div>

      <!-- Actual Sidebar Component -->
      <Sidebar class="relative z-[80] h-full shadow-2xl lg:shadow-none bg-white lg:bg-transparent" />
    </div>

    <!-- Zone de Contenu : Scrollable avec Wow feeling -->
    <div class="flex-1 flex flex-col overflow-y-auto relative z-10 custom-scrollbar bg-slate-50/10 pt-16 lg:pt-0">
      <main class="p-4 md:p-8 min-h-full">

        <RouterView v-slot="{ Component }">
          <transition 
            enter-active-class="transform transition duration-700 ease-out"
            enter-from-class="translate-y-8 opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transform transition duration-300 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-105"
            mode="out-in"
          >
            <component :is="Component" />
          </transition>
        </RouterView>
      </main>
    </div>

    <!-- Global Messenger Overlay -->
    <MessengerOverlay />
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f1f5f9;
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
</style>