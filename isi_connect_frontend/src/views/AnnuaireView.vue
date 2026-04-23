<script setup>
import { ref, onMounted, computed } from 'vue'
import apiClient, { STORAGE_URL } from '../api'
import { RouterLink } from 'vue-router'
import { 
  MagnifyingGlassIcon, 
  UserIcon, 
  BriefcaseIcon, 
  BuildingOfficeIcon, 
  SparklesIcon,
  ChevronRightIcon,
  ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline'
import { openChatWith } from '../messenger'
import { useAuth } from '../auth'

const auth = useAuth()
const alumniList = ref([])
const isLoading = ref(true)
const searchTerm = ref('')

onMounted(async () => {
  try {
    const response = await apiClient.get('/alumni')
    alumniList.value = response.data.data
  } catch (error) {
    console.error("DÉFAILLANCE HUB ANNUAIRE:", error)
  } finally {
    isLoading.value = false
  }
})

const toggleFeature = async (alumnus) => {
  try {
    const response = await apiClient.put(`/alumni/${alumnus.id}/feature`);
    // Ensure profile exists in ref, though it should
    if(alumnus.profile) {
       alumnus.profile.is_featured_in_showcase = response.data.is_featured;
    }
  } catch (error) {
    console.error("Erreur Toggle Vitrine", error);
    alert("Impossible de modifier le statut en vitrine.");
  }
}

const filteredAlumni = computed(() => {
  if (!searchTerm.value) return alumniList.value
  const search = searchTerm.value.toLowerCase()
  return alumniList.value.filter(alumnus => {
    const name = alumnus.name.toLowerCase()
    const job = alumnus.profile.job_title?.toLowerCase() || ''
    const company = alumnus.profile.company_name?.toLowerCase() || ''
    return name.includes(search) || job.includes(search) || company.includes(search)
  })
})
</script>

<template>
  <div class="space-y-10 animate-page-reveal">
    
    <!-- Digital Header & Search Hub -->
    <div class="wow-card rounded-3xl md:rounded-[3rem] p-6 md:p-10 bg-slate-950 text-white border-b-8 border-sky-500 relative overflow-hidden group">


      
      <!-- Binary Rain Overlay -->
      <div class="absolute inset-0 opacity-10 pointer-events-none select-none text-[8px] font-black grid grid-cols-12 gap-4">
        <div v-for="n in 24" :key="n" class="animate-float-slow text-sky-200">{{ n % 2 === 0 ? '1' : '0' }}</div>
      </div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
        <div>
          <div class="flex items-center space-x-3 mb-4 bg-sky-500/20 w-fit px-4 py-1.5 rounded-full border border-sky-400/30">
            <SparklesIcon class="h-3 w-3 text-sky-400 animate-pulse" />
            <span class="text-[9px] font-black uppercase tracking-[0.4em] text-sky-300">HUB DE TALENTS ISI</span>
          </div>
          <h1 class="text-3xl md:text-5xl font-black tracking-tighter leading-none uppercase">
            Annuaire des <span class="text-sky-400">Anciens</span>
          </h1>

        </div>
        
        <!-- Tech Search Bar -->
        <div class="relative md:w-96 group/search">
          <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
            <MagnifyingGlassIcon class="h-5 w-5 text-sky-400 group-focus-within/search:text-white transition-colors" />
          </div>
          <input 
            v-model="searchTerm"
            type="text"
            placeholder="RECHERCHER UN TALENT..."
            class="w-full bg-white/5 border-2 border-white/10 rounded-2xl md:rounded-[2rem] py-4 md:py-5 pl-14 pr-6 text-[10px] md:text-xs font-black tracking-widest uppercase text-white placeholder-slate-500 focus:border-sky-500 focus:bg-white/10 focus:ring-0 transition-all duration-500 outline-none"

          />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col justify-center items-center h-96">
      <div class="relative h-20 w-20">
        <div class="absolute inset-0 rounded-full border-4 border-sky-100 opacity-20"></div>
        <div class="absolute inset-0 rounded-full border-4 border-t-sky-500 animate-spin"></div>
      </div>
      <p class="mt-6 text-[10px] font-black uppercase tracking-[0.5em] text-sky-600 animate-pulse">SYNCHRONISATION ANNUAIRE...</p>
    </div>

    <!-- Alumni Grid: Digital Tech Cards -->
    <div v-else class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <TransitionGroup name="list">
        <div 
          v-for="(alumnus, idx) in filteredAlumni" 
          :key="alumnus.id"
          class="wow-card rounded-[3rem] bg-white border-2 border-slate-50 relative overflow-hidden group/card hover:-translate-y-3 transition-all duration-700"
          :style="{ transitionDelay: (idx * 50) + 'ms' }"
        >
          <!-- Header Area -->
          <div class="p-8 text-center pb-6">

            <!-- Avatar HUB -->
            <div class="h-28 w-28 mx-auto mb-8 relative group/avatar">
              <div class="absolute inset-0 bg-gradient-to-tr from-sky-400 to-blue-600 rounded-[2rem] rotate-6 group-hover/avatar:rotate-12 transition-all duration-500 shadow-lg group-hover/avatar:shadow-sky-500/20"></div>
              <div class="absolute inset-1.5 bg-slate-900 rounded-3xl flex items-center justify-center overflow-hidden z-10 border-4 border-slate-950 shadow-inner">
                 <img v-if="alumnus.profile?.profile_picture_url" :src="alumnus.profile.profile_picture_url.startsWith('http') ? alumnus.profile.profile_picture_url : (STORAGE_URL + alumnus.profile.profile_picture_url)" class="h-full w-full object-cover" />
                 <UserIcon v-else class="h-10 w-10 text-slate-700" />
              </div>
              <!-- Status Glow -->
              <div v-if="alumnus.is_online" class="absolute -bottom-2 -right-2 h-6 w-6 bg-green-500 border-4 border-white rounded-full z-20 shadow-lg"></div>
              <div v-else class="absolute -bottom-2 -right-2 h-6 w-6 bg-slate-400 border-4 border-white rounded-full z-20"></div>
            </div>
            
            <h3 class="text-xl font-black text-slate-900 tracking-tight leading-tight">{{ alumnus.name }}</h3>
            <div class="mt-2 inline-block px-3 py-1 bg-sky-50 rounded-full">
              <p class="text-[9px] font-black text-sky-600 uppercase tracking-widest">Promotion {{ alumnus.promotion_year }}</p>
            </div>
          </div>
          
          <!-- Info Section -->
          <div class="px-10 py-8 border-t-2 border-slate-50 space-y-4">
            <div class="flex items-center text-slate-500 group/item">
              <div class="p-2 rounded-xl bg-slate-50 mr-4 group-hover/item:bg-sky-500 transition-colors">
                <BriefcaseIcon class="h-4 w-4 text-sky-400 group-hover/item:text-white" />
              </div>
              <span class="text-[10px] font-black uppercase tracking-widest truncate">{{ alumnus.profile.job_title || 'POSTE NON DÉFINI' }}</span>
            </div>
            <div class="flex items-center text-slate-500 group/item">
              <div class="p-2 rounded-xl bg-slate-50 mr-4 group-hover/item:bg-sky-500 transition-colors">
                <BuildingOfficeIcon class="h-4 w-4 text-sky-400 group-hover/item:text-white" />
              </div>
              <span class="text-[10px] font-black uppercase tracking-widest truncate">{{ alumnus.profile.company_name || 'ISI CONNECT PARTNER' }}</span>
            </div>
          </div>
          
          <!-- Quick Actions HUB -->
          <div class="px-6 pb-8 flex items-center justify-center space-x-2">

            <button 
              @click="openChatWith(alumnus)"
              class="flex items-center justify-center space-x-2 bg-slate-950 text-white px-4 py-3 rounded-2xl font-black text-[9px] uppercase tracking-widest hover:bg-sky-500 transition-all shadow-xl hover:-translate-y-1 active:scale-95 flex-1"
            >
              <ChatBubbleLeftRightIcon class="h-4 w-4" />
              <span class="hidden sm:inline">MSG</span>
            </button>
            
            <RouterLink 
              :to="{ name: 'profil-public', params: { id: alumnus.id } }"
              class="flex items-center justify-center space-x-2 bg-white text-slate-900 border-2 border-slate-100 px-4 py-3 rounded-2xl font-black text-[9px] uppercase tracking-widest hover:border-sky-500 transition-all shadow-sm flex-1"
            >
              <span class="hidden sm:inline">PROFIL</span>
              <span class="sm:hidden">VOIR</span>
            </RouterLink>

            <!-- Admin Showcase Toggle feature -->
            <button
              v-if="auth.user.value?.role === 'admin'"
              @click="toggleFeature(alumnus)"
              class="flex items-center justify-center bg-white border-2 border-slate-100 p-3 rounded-2xl hover:border-yellow-400 transition-all shadow-sm shrink-0"
              :class="alumnus.profile?.is_featured_in_showcase ? 'ring-2 ring-yellow-400 bg-yellow-50' : ''"
              title="Mettre en Vitrine sur la page d'accueil"
            >
              <SparklesIcon 
                class="h-5 w-5 transition-colors" 
                :class="alumnus.profile?.is_featured_in_showcase ? 'text-yellow-500 fill-yellow-500' : 'text-slate-400'" 
              />
            </button>
          </div>
        </div>
      </TransitionGroup>
      
      <!-- Empty State -->
      <div v-if="!isLoading && filteredAlumni.length === 0" class="col-span-full py-24 text-center wow-card rounded-[4rem] border-4 border-dashed border-slate-100">
        <MagnifyingGlassIcon class="h-16 w-16 mx-auto mb-6 text-sky-200" />
        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-slate-300">AUCUN TALENT NE CORRESPOND</h3>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes float-slow {
  0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.1; }
  50% { transform: translateY(-20px) rotate(5deg); opacity: 0.3; }
}
.animate-float-slow { animation: float-slow 10s infinite ease-in-out; }

@keyframes shine {
  from { transform: translateX(-100%); }
  to { transform: translateX(300%); }
}
.animate-shine { animation: shine 0.8s ease-in-out; }

@keyframes page-reveal {
  from { opacity: 0; filter: blur(10px); transform: translateY(20px); }
  to { opacity: 1; filter: blur(0); transform: translateY(0); }
}
.animate-page-reveal { animation: page-reveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

/* List Transition */
.list-enter-active, .list-leave-active { transition: all 0.5s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(30px) scale(0.9); }
</style>