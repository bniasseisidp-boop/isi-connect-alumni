<template>
  <div class="flex flex-col w-80 h-screen p-8 bg-white border-r-2 border-slate-50 text-slate-800 relative z-20 shadow-[10px_0_40px_rgba(0,0,0,0.02)]">
    
    <!-- Tech Logo Section -->
    <div class="mb-14 relative group">
      <div class="flex items-center justify-center p-6 rounded-[2.5rem] bg-white border-2 border-sky-50 shadow-xl shadow-sky-500/5 transition-all duration-700 group-hover:rotate-2 group-hover:scale-105 active:scale-95 cursor-pointer">
        <img 
          src="../assets/images/isi.png" 
          alt="Logo ISI" 
          class="h-16 w-auto" 
        />
      </div>
      <!-- Digital under-glow -->
      <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-1/2 h-1 bg-sky-400/20 blur-md rounded-full"></div>
    </div>

    <!-- Navigation Hub (Matrix Style) -->
    <nav class="flex-1 space-y-4 overflow-y-auto pr-2 custom-scrollbar">
      <RouterLink 
        v-for="(item, index) in menuItems" 
        :key="item.to"
        :to="item.to" 
        class="group relative flex items-center px-6 py-5 rounded-[2rem] transition-all duration-500 hover:bg-slate-50 border-2 border-transparent hover:border-sky-100"
        active-class="active-hub"
        @mouseenter="hoverIdx = index"
        @mouseleave="hoverIdx = null"
      >
        <!-- Icon Hub -->
        <div class="relative z-10 p-3 rounded-2xl bg-slate-50 group-hover:bg-sky-500 transition-all duration-500 group-[.active-hub]:bg-sky-600 shadow-sm group-hover:shadow-lg group-hover:shadow-sky-500/20">
          <component 
            :is="item.icon" 
            class="h-6 w-6 transition-all duration-500" 
            :class="[
              hoverIdx === index ? 'text-white scale-110' : 'text-slate-400',
              'group-[.active-hub]:text-white'
            ]"
          />
        </div>
        
        <div class="ml-5 flex-1 relative z-10 transition-all duration-500 group-hover:translate-x-2">
          <span class="text-xs font-black tracking-[0.2em] uppercase transition-colors duration-500" 
                :class="[
                  hoverIdx === index ? 'text-sky-900' : 'text-slate-500',
                  'group-[.active-hub]:text-sky-600'
                ]">
            {{ item.label }}
          </span>
        </div>

        <!-- Digital Indicator -->
        <div class="absolute right-4 w-1.5 h-1.5 rounded-full bg-sky-200 opacity-0 group-hover:opacity-100 group-[.active-hub]:bg-sky-500 group-[.active-hub]:opacity-100 group-[.active-hub]:scale-150 transition-all duration-700 shadow-[0_0_10px_#0ea5e9]"></div>
      </RouterLink>
    </nav>

    <!-- User & Data Access -->
    <div class="mt-auto pt-10 border-t-2 border-slate-50">
      <div v-if="auth.user.value" class="flex items-center mb-8 px-6 py-6 bg-slate-50 rounded-[2.5rem] border-2 border-transparent hover:border-sky-100 transition-all duration-500 group/user cursor-help">
        <div class="relative h-14 w-14 rounded-3xl bg-slate-900 flex items-center justify-center font-black text-white text-xl shadow-2xl group-hover/user:rotate-6 transition-all duration-500 overflow-hidden">
           <img v-if="getUserPhoto()" :src="getUserPhoto()" class="h-full w-full object-cover" />
           <span v-else>{{ auth.user.value.name.charAt(0) }}</span>
           <!-- Online status -->
           <div class="absolute -top-1 -right-1 h-4 w-4 bg-green-500 border-4 border-slate-50 rounded-full"></div>
        </div>
        <div class="ml-5 truncate">
          <p class="text-xs font-black text-slate-800 truncate uppercase tracking-tighter">{{ auth.user.value.name }}</p>
          <p class="text-[9px] text-sky-600 font-bold truncate tracking-[0.2em] uppercase mt-1">SESSION ACTIVE</p>
        </div>
      </div>
      
      <button 
        @click="handleLogout" 
        class="group flex items-center justify-center w-full px-6 py-6 rounded-[2rem] transition-all duration-500 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white shadow-xl shadow-red-500/5 active:scale-95"
      >
        <ArrowRightOnRectangleIcon class="h-5 w-5 mr-4 transform transition-transform duration-500 group-hover:translate-x-2" />
        <span class="font-black text-[10px] uppercase tracking-[0.3em]">QUITTER LE HUB</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuth } from '../auth'
import { STORAGE_URL } from '../api'
import { 
  Squares2X2Icon, 
  UsersIcon, 
  BriefcaseIcon, 
  CalendarIcon, 
  ChatBubbleLeftRightIcon, 
  UserCircleIcon,
  ArrowRightOnRectangleIcon,
  SparklesIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

const auth = useAuth()

const getUserPhoto = () => {
    const photo = auth.user.value?.profile?.profile_picture_url;
    if (!photo) return null;
    return photo.startsWith('http') ? photo : (STORAGE_URL + photo);
};
const router = useRouter()
const hoverIdx = ref(null)

const menuItems = [
  { to: '/dashboard', label: 'ACCUEIL', icon: Squares2X2Icon },
  { to: '/actualites', label: 'ACTUALITÉS', icon: SparklesIcon },
  { to: '/groupes', label: 'GROUPES', icon: UserGroupIcon },
  { to: '/annuaire', label: 'ANNUAIRE', icon: UsersIcon },
  { to: '/offres', label: 'EMPLOIS', icon: BriefcaseIcon },
  { to: '/evenements', label: 'AGENDA', icon: CalendarIcon },
  { to: '/forum', label: 'FORUM', icon: ChatBubbleLeftRightIcon },
  { to: '/profil', label: 'PROFIL', icon: UserCircleIcon },
]

const handleLogout = async () => {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<style scoped>
.active-hub {
  background: white !important;
  border-color: #f0f9ff !important;
  box-shadow: 0 10px 40px rgba(14, 165, 233, 0.08);
}
.custom-scrollbar::-webkit-scrollbar {
  width: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f1f5f9;
  border-radius: 10px;
}
</style>