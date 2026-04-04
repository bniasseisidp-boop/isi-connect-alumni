<script setup>
import Sidebar from '../components/Sidebar.vue'
import MessengerOverlay from '../components/MessengerOverlay.vue'
import { RouterView } from 'vue-router'
</script>

<template>
  <div class="flex h-screen bg-white relative overflow-hidden">
    
    <!-- Background Digital Grid (Généralisé) -->
    <div class="absolute inset-0 pointer-events-none opacity-40 z-0">
      <div class="absolute inset-0 bg-[linear-gradient(rgba(14,165,233,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(14,165,233,0.03)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
    </div>

    <!-- Sidebar : Elle reste fixe à gauche -->
    <Sidebar />

    <!-- Zone de Contenu : Scrollable avec Wow feeling -->
    <div class="flex-1 flex flex-col overflow-y-auto relative z-10 custom-scrollbar">
      <main class="p-10 min-h-full">
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