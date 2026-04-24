<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import Sidebar from '../components/Sidebar.vue'
import MessengerOverlay from '../components/MessengerOverlay.vue'
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'
import apiClient from '../api'

const isMobileMenuOpen = ref(false)
const route = useRoute()

watch(() => route.path, () => {
  isMobileMenuOpen.value = false
})

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

// Heartbeat: met à jour last_seen_at toutes les 30s pour la présence en ligne
let heartbeatInterval = null
onMounted(() => {
  apiClient.get('/ping').catch(() => {})
  heartbeatInterval = setInterval(() => {
    apiClient.get('/ping').catch(() => {})
  }, 30000)
})
onUnmounted(() => clearInterval(heartbeatInterval))
</script>

<template>
  <div class="flex h-screen relative overflow-hidden" style="background: var(--bg-primary); color: var(--text-primary);">

    <!-- Animated Background Grid (theme-aware) -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
      <div class="absolute inset-0 digital-grid opacity-60"></div>
      <!-- Floating orbs -->
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>

    <!-- Mobile Top Navigation Bar -->
    <div class="lg:hidden fixed top-0 left-0 right-0 h-16 backdrop-blur-md border-b flex items-center justify-between px-6 z-[60] shadow-sm"
         style="background: var(--card-bg); border-color: var(--card-border);">
      <div class="flex items-center space-x-3">
        <div class="h-8 w-8 rounded-xl flex items-center justify-center shadow-lg" style="background: var(--accent);">
          <span class="text-white font-black text-[9px] tracking-widest">ISI</span>
        </div>
        <span class="font-black uppercase tracking-widest text-[10px]" style="color: var(--text-primary);">
          ISI <span style="color: var(--accent);">Connect</span>
        </span>
      </div>

      <button @click="toggleMobileMenu"
              class="h-10 w-10 rounded-xl flex items-center justify-center transition-all active:scale-90 border"
              style="background: var(--bg-secondary); border-color: var(--card-border); color: var(--text-secondary);">
        <Bars3Icon v-if="!isMobileMenuOpen" class="h-5 w-5" />
        <XMarkIcon v-else class="h-5 w-5" />
      </button>
    </div>

    <!-- Sidebar Drawer Wrapper -->
    <div :class="[
      'fixed inset-0 z-[70] lg:relative lg:z-auto lg:translate-x-0 transition-transform duration-500 ease-in-out',
      isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
    ]">
      <!-- Overlay for mobile -->
      <div
        v-if="isMobileMenuOpen"
        @click="isMobileMenuOpen = false"
        class="absolute inset-0 backdrop-blur-sm lg:hidden"
        style="background: rgba(0,0,0,0.45);"
      ></div>

      <!-- Actual Sidebar Component -->
      <Sidebar class="relative z-[80] h-full shadow-2xl lg:shadow-none" />
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-y-auto relative z-10 custom-scrollbar pt-16 lg:pt-0">
      <main class="flex-1 p-4 md:p-8">
        <RouterView v-slot="{ Component }">
          <transition
            enter-active-class="transform transition duration-600 ease-out"
            enter-from-class="translate-y-6 opacity-0 scale-[0.97]"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transform transition duration-250 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
            mode="out-in"
          >
            <component :is="Component" />
          </transition>
        </RouterView>
      </main>

      <!-- Multibrain Tech Footer -->
      <footer class="shrink-0 px-6 py-3 flex items-center justify-end border-t" style="border-color: var(--card-border);">
        <a href="https://multibrain.tech" target="_blank" rel="noopener"
           class="flex items-center space-x-1.5 opacity-40 hover:opacity-70 transition-opacity duration-300 group">
          <div class="h-4 w-4 rounded flex items-center justify-center" style="background: var(--accent);">
            <span class="text-white font-black text-[6px]">MB</span>
          </div>
          <span class="text-[9px] font-bold uppercase tracking-[0.2em]" style="color: var(--text-secondary);">
            Powered by Multibrain Tech
          </span>
        </a>
      </footer>
    </div>

    <!-- Global Messenger Overlay -->
    <MessengerOverlay />
  </div>
</template>

<style scoped>
.digital-grid {
  background-image:
    linear-gradient(var(--accent, #0ea5e9) 1px, transparent 1px),
    linear-gradient(90deg, var(--accent, #0ea5e9) 1px, transparent 1px);
  background-size: 40px 40px;
  opacity: 0.03;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.07;
  animation: float-orb 12s ease-in-out infinite;
  background: var(--accent, #0ea5e9);
}
.orb-1 { width: 400px; height: 400px; top: -100px; left: -100px; animation-delay: 0s; }
.orb-2 { width: 300px; height: 300px; bottom: -80px; right: 200px; animation-delay: -4s; }
.orb-3 { width: 200px; height: 200px; top: 40%; right: -60px; animation-delay: -8s; }

@keyframes float-orb {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33%       { transform: translate(20px, -30px) scale(1.05); }
  66%       { transform: translate(-15px, 20px) scale(0.95); }
}

.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--accent, #0ea5e9);
  opacity: 0.3;
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
</style>
