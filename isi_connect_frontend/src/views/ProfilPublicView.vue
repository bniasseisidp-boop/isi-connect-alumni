<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import apiClient from '../api'
import { 
  ArrowLeftIcon, 
  MapPinIcon, 
  AcademicCapIcon, 
  EnvelopeIcon, 
  LinkIcon,
  UserIcon,
  SparklesIcon,
  IdentificationIcon,
  ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline'
import { STORAGE_URL } from '../api'
import { openChatWith } from '../messenger'

const route = useRoute()
const user = ref(null)
const isLoading = ref(true)
const errorMessage = ref(null)

const fetchUserProfile = async () => {
  isLoading.value = true
  try {
    const userId = route.params.id
    const response = await apiClient.get(`/alumni/${userId}`)
    user.value = response.data
  } catch (error) {
    console.error("DÉFAILLANCE HUB PROFIL:", error)
    errorMessage.value = "PROFIL NON DISPONIBLE"
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchUserProfile)
</script>

<template>
  <div class="space-y-10 animate-page-reveal">
    
    <!-- Hover Back Navigation -->
    <RouterLink 
      to="/annuaire" 
      class="group inline-flex items-center px-6 py-3 rounded-2xl bg-white border-2 border-slate-50 text-slate-500 hover:text-sky-600 hover:border-sky-100 hover:shadow-xl transition-all duration-500"
    >
      <ArrowLeftIcon class="h-4 w-4 mr-3 transition-transform group-hover:-translate-x-2" />
      <span class="text-[10px] font-black uppercase tracking-[0.2em]">Retour à l'Annuaire</span>
    </RouterLink>

    <!-- Loading HUB -->
    <div v-if="isLoading" class="flex flex-col justify-center items-center h-96">
      <div class="relative h-24 w-24">
        <div class="absolute inset-0 rounded-full border-4 border-sky-100 opacity-20"></div>
        <div class="absolute inset-0 rounded-full border-4 border-t-sky-500 animate-spin"></div>
      </div>
      <p class="mt-8 text-[10px] font-black uppercase tracking-[0.6em] text-sky-600 animate-pulse">DÉCRYPTAGE PROFIL...</p>
    </div>
    
    <!-- Error State -->
    <div v-else-if="errorMessage" class="wow-card rounded-[3.5rem] p-24 text-center border-4 border-dashed border-red-50">
      <IdentificationIcon class="h-16 w-16 mx-auto mb-6 text-red-200" />
      <h3 class="text-xl font-black text-red-500 tracking-widest">{{ errorMessage }}</h3>
      <p class="mt-4 text-slate-400 text-xs font-bold uppercase tracking-widest">Le protocole de sécurité a bloqué l'accès.</p>
    </div>

    <!-- Main HUB Profile Content -->
    <div v-else-if="user" class="space-y-10">
      
      <!-- Top Card: Identity & Status -->
      <div class="wow-card rounded-[4rem] bg-slate-950 p-12 md:p-20 text-white border-b-8 border-sky-500 relative overflow-hidden group">
        <!-- Floating Digital Elements -->
        <div class="absolute inset-0 opacity-5 pointer-events-none text-[8px] font-black grid grid-cols-12 gap-4">
          <div v-for="n in 12" :key="n" class="animate-float-slow text-sky-200">{{ n % 2 === 0 ? '1' : '0' }}</div>
        </div>

        <div class="relative z-10 flex flex-col items-center text-center">
          <!-- Animated Avatar HUB -->
          <div class="relative h-44 w-44 mb-10 group/avatar">
            <div class="absolute inset-0 bg-gradient-to-tr from-sky-400 via-blue-500 to-sky-600 rounded-[3.5rem] rotate-6 group-hover/avatar:rotate-12 transition-all duration-700 animate-pulse"></div>
            <div class="absolute inset-1.5 bg-slate-900 rounded-[3rem] flex items-center justify-center overflow-hidden z-10 border-4 border-slate-950">
              <img v-if="user.profile?.profile_picture_url" :src="user.profile.profile_picture_url.startsWith('http') ? user.profile.profile_picture_url : (STORAGE_URL + user.profile.profile_picture_url)" class="h-full w-full object-cover" />
              <UserIcon v-else class="h-20 w-20 text-slate-700 group-hover/avatar:scale-110 transition-transform duration-700" />
            </div>
            <!-- Identity Pulse Badge -->
            <div class="absolute -bottom-3 -right-3 px-6 py-2 bg-green-500 text-white text-[9px] font-black uppercase tracking-[0.3em] rounded-full shadow-2xl border-4 border-slate-950 z-20">
               ONLINE
            </div>
          </div>

          <h1 class="text-5xl md:text-7xl font-black mb-4 tracking-tighter text-white">
            {{ user.name.toUpperCase() }}
          </h1>
          <div class="flex items-center space-x-3 mb-6 bg-sky-500/10 px-6 py-2.5 rounded-full border border-sky-400/20 backdrop-blur-xl">
             <IdentificationIcon class="h-4 w-4 text-sky-400" />
             <span class="text-[11px] font-black uppercase tracking-[0.4em] text-sky-400">{{ user.profile.job_title || 'TALENT À DÉFINIR' }}</span>
          </div>
          <p class="text-slate-400 text-lg font-medium opacity-80 uppercase tracking-widest leading-relaxed max-w-xl mb-10">
            {{ user.profile.company_name || 'ÉCOSYSTÈME INDÉPENDANT' }}
          </p>

          <button 
            @click="openChatWith(user)"
            class="flex items-center space-x-4 bg-sky-500 text-white px-10 py-6 rounded-[2rem] font-black text-[11px] uppercase tracking-[0.3em] shadow-[0_20px_50px_rgba(14,165,233,0.3)] hover:-translate-y-2 transition-all active:scale-95 group/msg"
          >
            <ChatBubbleLeftRightIcon class="h-6 w-6 group-hover:rotate-12 transition-transform" />
            <span>ENVOYER UN SIGNAL</span>
          </button>
        </div>
      </div>
      
      <!-- Content Grid: Digital Data Blocks -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        
        <!-- Left Bloc: Core Data -->
        <section class="wow-card rounded-[3.5rem] p-10 md:p-14 border-2 border-slate-50 bg-white shadow-xl shadow-slate-200/50">
          <div class="flex items-center space-x-4 mb-12">
            <IdentificationIcon class="h-8 w-8 text-sky-500" />
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Spécifications</h2>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
            <div class="p-8 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:border-sky-200 transition-all group">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">GEN : PROMOTION</p>
              <div class="flex items-center space-x-4">
                <AcademicCapIcon class="h-6 w-6 text-sky-500" />
                <span class="text-2xl font-black text-slate-900">{{ user.promotion_year }}</span>
              </div>
            </div>
            
            <div class="p-8 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:border-sky-200 transition-all group">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">LOC : LOCALISATION</p>
              <div class="flex items-center space-x-4">
                <MapPinIcon class="h-6 w-6 text-sky-500" />
                <span class="text-2xl font-black text-slate-900">{{ user.profile.city || 'HUB MONDIAL' }}</span>
              </div>
            </div>
          </div>

          <!-- Contact Matrix -->
          <div class="mt-10 space-y-6">
            <a :href="'mailto:' + user.email" class="flex items-center p-8 rounded-[2.5rem] bg-sky-50 group hover:bg-sky-500 transition-all duration-500 relative overflow-hidden">
               <div class="h-12 w-12 bg-white rounded-2xl flex items-center justify-center mr-6 group-hover:bg-white/20 transition-all">
                  <EnvelopeIcon class="h-6 w-6 text-sky-500 group-hover:text-white" />
               </div>
               <div class="flex-1">
                 <p class="text-[9px] font-black text-sky-600 group-hover:text-white/70 uppercase tracking-widest mb-1">CANAL EMAIL</p>
                 <p class="text-lg font-black text-slate-900 group-hover:text-white transition-colors truncate">{{ user.email }}</p>
               </div>
            </a>
            
            <a v-if="user.profile.linkedin_url" :href="'https://' + user.profile.linkedin_url" target="_blank" class="flex items-center p-8 rounded-[2.5rem] bg-slate-900 group hover:bg-blue-600 transition-all duration-500">
               <div class="h-12 w-12 bg-white/5 rounded-2xl flex items-center justify-center mr-6">
                  <LinkIcon class="h-6 w-6 text-sky-400 group-hover:text-white" />
               </div>
               <div class="flex-1">
                 <p class="text-[9px] font-black text-slate-500 group-hover:text-white/70 uppercase tracking-widest mb-1">RESEAU SOCIAL PRO</p>
                 <p class="text-lg font-black text-white truncate">{{ user.profile.linkedin_url }}</p>
               </div>
            </a>
          </div>
        </section>

        <!-- Right Bloc: Bio & Insights -->
        <section class="wow-card rounded-[3.5rem] p-10 md:p-14 border-2 border-slate-50 bg-white">
          <div class="flex items-center space-x-4 mb-12">
            <SparklesIcon class="h-8 w-8 text-sky-500" />
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">À Propos</h2>
          </div>
          
          <div class="p-10 rounded-[3rem] bg-slate-50 border border-slate-100 relative group overflow-hidden">
            <div class="absolute top-0 right-0 p-8">
              <div class="h-12 w-12 rounded-full border-2 border-sky-100 flex items-center justify-center animate-pulse">
                <span class="text-[10px] font-black text-sky-200 tracking-widest uppercase">BIO</span>
              </div>
            </div>
            <p class="text-slate-600 text-xl font-medium leading-loose italic relative z-10">
              "{{ user.profile.bio || 'Cet Alumni n\'a pas encore configuré sa biographie numérique, mais son talent rayonne déjà dans le réseau.' }}"
            </p>
            <div class="mt-12 h-1 w-24 bg-sky-500 rounded-full"></div>
          </div>
          
          <!-- Skill hint -->
          <div class="mt-12 flex flex-wrap gap-4">
            <div v-for="tag in ['NETWORKING', 'TECH', 'ISI ALUMNI']" :key="tag" class="px-5 py-2 bg-sky-50 text-sky-600 text-[10px] font-black uppercase tracking-[0.3em] rounded-xl border border-sky-100">
               #{{ tag }}
            </div>
          </div>
        </section>

      </div>
    </div>

  </div>
</template>

<style scoped>
@keyframes page-reveal {
  from { opacity: 0; filter: blur(20px); transform: translateY(30px) scale(0.98); }
  to { opacity: 1; filter: blur(0); transform: translateY(0) scale(1); }
}
.animate-page-reveal { animation: page-reveal 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

@keyframes float-slow {
  0%, 100% { transform: translateY(0); opacity: 0.05; }
  50% { transform: translateY(-20px); opacity: 0.1; }
}
.animate-float-slow { animation: float-slow 8s infinite ease-in-out; }
</style>