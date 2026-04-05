<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../api'
import { 
  CalendarIcon, 
  PlusIcon, 
  MapPinIcon, 
  ClockIcon,
  XMarkIcon,
  SparklesIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'

const eventsList = ref([])
const isLoading = ref(true)
const showModal = ref(false)
const showDetailModal = ref(false)
const selectedEvent = ref(null)
const errorMessage = ref(null)

const newEventForm = ref({
  title: '',
  location: '',
  starts_at: '',
  description: ''
})

const formatDateTime = (dateString, format) => {
  const date = new Date(dateString)
  if (format === 'MMM') return date.toLocaleDateString('fr-FR', { month: 'short' }).toUpperCase()
  if (format === 'DD') return date.getDate().toString().padStart(2, '0')
  if (format === 'FULL') {
    return date.toLocaleDateString('fr-FR', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric', 
      hour: '2-digit', 
      minute: '2-digit' 
    }).toUpperCase()
  }
  return date.toLocaleDateString('fr-FR')
}

const fetchEvents = async () => {
  isLoading.value = true
  try {
    const response = await apiClient.get('/events')
    eventsList.value = response.data.data
  } catch (error) {
    console.error("DÉFAILLANCE HUB AGENDA:", error)
    errorMessage.value = "IMPOSSIBLE DE CHARGER L'AGENDA."
  } finally {
    isLoading.value = false
  }
}

const handleSubmitEvent = async () => {
  isLoading.value = true
  errorMessage.value = null
  try {
    await apiClient.post('/events', newEventForm.value)
    showModal.value = false
    fetchEvents()
    newEventForm.value = { title: '', location: '', starts_at: '', description: '' }
  } catch (error) {
    console.error("ERREUR PUBLICATION ÉVÉNEMENT:", error)
    errorMessage.value = "ÉCHEC DU DÉPLOIEMENT ÉVÉNEMENTIEL."
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchEvents)
</script>

<template>
  <div class="space-y-12 animate-page-reveal">
    
    <!-- Digital Events Header -->
    <div class="wow-card rounded-3xl md:rounded-[3.5rem] p-6 md:p-16 bg-slate-950 text-white border-b-8 border-sky-500 relative overflow-hidden group shadow-[0_30px_60px_rgba(0,0,0,0.2)]">
      
      <!-- Binary Rain Overlay -->
      <div class="absolute inset-0 opacity-5 pointer-events-none select-none text-[8px] font-black grid grid-cols-12 gap-4">
        <div v-for="n in 24" :key="n" class="animate-float-slow text-sky-200">{{ n % 2 === 0 ? '1' : '0' }}</div>
      </div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-8 md:gap-10">
        <div class="max-w-2xl text-center md:text-left">
          <div class="mx-auto md:mx-0 flex items-center space-x-3 mb-6 bg-sky-500/20 w-fit px-5 py-2 rounded-full border border-sky-400/30">
            <SparklesIcon class="h-3 w-3 text-sky-400 animate-pulse" />
            <span class="text-[9px] font-black uppercase tracking-[0.4em] text-sky-300">HUB ÉVÉNEMENTIEL ISI</span>
          </div>
          <h1 class="text-3xl md:text-7xl font-black tracking-tighter leading-none mb-4">
            Agenda <span class="text-sky-400">Connecté</span>
          </h1>
          <p class="text-slate-400 font-medium text-sm md:text-lg leading-relaxed opacity-80 uppercase tracking-widest">ORGANISEZ ET PARTICIPEZ AUX RENCONTRES DU RÉSEAU.</p>
        </div>
        
        <button
          @click="showModal = true"
          class="flex items-center justify-center bg-sky-500 hover:bg-sky-400 text-white px-8 md:px-10 py-5 md:py-6 rounded-2xl md:rounded-[2rem] font-black text-[10px] uppercase tracking-[0.3em] transition-all duration-500 hover:-translate-y-2 active:scale-95 shadow-2xl shadow-sky-500/40"
        >
          <PlusIcon class="h-5 w-5 mr-3 md:mr-4" />
          CRÉER ÉVÉNEMENT
        </button>
      </div>
    </div>


    <!-- Loading Interface -->
    <div v-if="isLoading && !showModal" class="flex flex-col justify-center items-center h-96">
      <div class="relative h-20 w-20">
        <div class="absolute inset-0 rounded-full border-4 border-sky-100 opacity-20"></div>
        <div class="absolute inset-0 rounded-full border-4 border-t-sky-500 animate-spin"></div>
      </div>
      <p class="mt-8 text-[10px] font-black uppercase tracking-[0.6em] text-sky-600 animate-pulse">SYNCHRONISATION AGENDA...</p>
    </div>

    <!-- Events Stream -->
    <div v-else class="space-y-8 pb-10">
      
      <div 
        v-for="event in eventsList" 
        :key="event.id"
        class="wow-card rounded-[3rem] bg-white border-2 border-slate-50 p-10 flex flex-col md:flex-row items-center justify-between transition-all duration-700 hover:border-sky-100 hover:shadow-2xl hover:shadow-sky-500/[0.05] group/card"
      >
        <div class="flex flex-col md:flex-row items-center flex-1">
          <!-- Digital Date Sticker -->
          <div class="flex flex-col items-center justify-center bg-slate-900 rounded-[2.5rem] px-10 py-6 mr-0 md:mr-12 mb-6 md:mb-0 group-hover/card:bg-sky-600 transition-all duration-700 shadow-xl min-w-[120px]">
            <span class="text-[10px] uppercase font-black text-sky-400 group-hover:text-sky-100 tracking-[0.4em] mb-2">
              {{ formatDateTime(event.starts_at, 'MMM') }}
            </span>
            <span class="text-4xl font-black text-white">{{ formatDateTime(event.starts_at, 'DD') }}</span>
          </div>

          <div class="text-center md:text-left flex-1">
            <h3 class="text-2xl font-black text-slate-900 group-hover/card:text-sky-600 transition-colors uppercase tracking-tight mb-3">{{ event.title }}</h3>
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-8 gap-y-2">
              <div class="flex items-center text-slate-400">
                <ClockIcon class="h-4 w-4 mr-3 text-sky-500" />
                <span class="text-[10px] font-black uppercase tracking-widest">{{ formatDateTime(event.starts_at, 'FULL') }}</span>
              </div>
              <div class="flex items-center text-slate-400">
                <MapPinIcon class="h-4 w-4 mr-3 text-sky-500" />
                <span class="text-[10px] font-black uppercase tracking-widest">{{ event.location }}</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-8 md:mt-0">
          <button 
            @click="selectedEvent = event; showDetailModal = true"
            class="flex items-center bg-slate-50 hover:bg-slate-900 px-10 py-5 rounded-2xl group/btn transition-all duration-500 text-slate-500 hover:text-white border-2 border-transparent hover:border-sky-500/30"
          >
            <span class="font-black text-[10px] uppercase tracking-widest mr-4">DÉTAILS</span>
            <ChevronRightIcon class="h-4 w-4 transform group-hover/btn:translate-x-2 transition-transform" />
          </button>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="!isLoading && eventsList.length === 0" class="py-32 text-center wow-card rounded-[4rem] border-4 border-dashed border-slate-100">
        <CalendarIcon class="h-16 w-16 mx-auto mb-8 text-sky-200/50" />
        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-slate-300">PROTOCOLE AGENDA VIDE</h3>
        <p class="mt-4 text-slate-400 text-[10px] font-bold uppercase tracking-widest">INITIALISEZ LE RÉSEAU AVEC UN ÉVÉNEMENT.</p>
      </div>
    </div>
    
    <!-- Premium Event Creation Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-0">
      <div @click="showModal = false" class="absolute inset-0 bg-slate-950/80 backdrop-blur-xl animate-fade-in"></div>
      
      <form 
        @submit.prevent="handleSubmitEvent" 
        class="relative w-full max-w-2xl bg-white rounded-[4rem] shadow-[-20px_40px_80px_rgba(0,0,0,0.3)] border-b-[12px] border-sky-500 animate-slide-up-slow overflow-hidden"
      >
        <!-- Modal Header -->
        <div class="bg-slate-950 p-10 flex items-center justify-between border-b border-white/5 relative overflow-hidden">
          <div class="relative z-10">
            <h2 class="text-3xl font-black text-white tracking-tighter uppercase">Initialiser <span class="text-sky-400">Événement</span></h2>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.4em] mt-2">DÉPLOIEMENT AGENDA ISI</p>
          </div>
          <button @click="showModal = false" type="button" class="relative z-10 h-14 w-14 bg-white/5 hover:bg-red-500 rounded-2xl flex items-center justify-center text-white transition-all duration-500 group">
            <XMarkIcon class="h-6 w-6 group-hover:rotate-90 transition-transform" />
          </button>
          <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#0ea5e9_1px,transparent_1px)] bg-[size:10px_10px]"></div>
        </div>
        
        <!-- Form Content -->
        <div class="p-10 md:p-14 space-y-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
          <div v-if="errorMessage" class="rounded-3xl bg-red-50 border-2 border-red-100 p-6">
            <p class="text-[10px] font-black text-red-600 uppercase tracking-widest">{{ errorMessage }}</p>
          </div>

          <div class="grid grid-cols-1 gap-10 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Titre de la rencontre</label>
              <input type="text" v-model="newEventForm.title" required class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none" placeholder="EX: CONFERENCE IA & SUPTECH" />
            </div>
            
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Localisation / Hub</label>
              <input type="text" v-model="newEventForm.location" required class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none" placeholder="EX: DAKAR OU ZOOM" />
            </div>
            
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Temporalité</label>
              <input type="datetime-local" v-model="newEventForm.starts_at" required class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none" />
            </div>
            
            <div class="sm:col-span-2">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Description du Protocole</label>
              <textarea v-model="newEventForm.description" rows="5" required class="hub-input w-full rounded-[2rem] bg-slate-50 border-2 border-slate-100 p-8 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none"></textarea>
            </div>
          </div>
        </div>
        
        <!-- Modal Actions -->
        <div class="flex justify-end items-center space-x-6 p-10 bg-slate-50">
          <button 
            type="button" 
            @click="showModal = false"
            class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-colors"
          >
            ANNULER
          </button>
          <button 
            type="submit" 
            :disabled="isLoading"
            class="flex items-center justify-center bg-slate-950 text-white px-10 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all hover:bg-sky-600 disabled:opacity-50"
          >
            {{ isLoading ? 'SYNCHRONISATION...' : 'DÉPLOYER ÉVÉNEMENT' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Event Detail Modal Matrix -->
    <div v-if="showDetailModal && selectedEvent" class="fixed inset-0 z-[110] flex items-center justify-center p-6">
      <div @click="showDetailModal = false" class="absolute inset-0 bg-slate-950/90 backdrop-blur-3xl"></div>
      <div class="relative w-full max-w-3xl bg-white rounded-[4rem] overflow-hidden shadow-2xl border-2 border-white/20 animate-page-reveal">
         <div class="bg-slate-950 p-12 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#0ea5e9_1px,transparent_1px)] bg-[size:15px_15px]"></div>
            <div class="relative flex justify-between items-start">
               <div>
                  <div class="flex items-center space-x-3 mb-4 bg-sky-500/20 w-fit px-4 py-2 rounded-full border border-sky-400/30">
                    <CalendarIcon class="h-3 w-3 text-sky-400" />
                    <span class="text-[8px] font-black uppercase tracking-[0.3em] text-sky-300">DÉTAILS PROTOCOLE</span>
                  </div>
                  <h2 class="text-4xl md:text-5xl font-black text-white uppercase tracking-tighter">{{ selectedEvent.title }}</h2>
               </div>
               <button @click="showDetailModal = false" class="h-14 w-14 bg-white/10 rounded-2xl flex items-center justify-center text-white hover:bg-red-500 transition-all">
                  <XMarkIcon class="h-6 w-6" />
               </button>
            </div>
         </div>
         
         <div class="p-12 space-y-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
               <div class="space-y-2">
                  <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">TEMPORALITÉ ACTIVE</p>
                  <p class="text-lg font-bold text-slate-800">{{ formatDateTime(selectedEvent.starts_at, 'FULL') }}</p>
               </div>
               <div class="space-y-2">
                  <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">LOCALISATION HUB</p>
                  <p class="text-lg font-bold text-slate-800">{{ selectedEvent.location }}</p>
               </div>
            </div>
            
            <div class="pt-8 border-t border-slate-50">
               <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4">CORPS DU PROTOCOLE</p>
               <p class="text-slate-600 text-lg leading-relaxed whitespace-pre-wrap">{{ selectedEvent.description }}</p>
            </div>

            <div class="pt-10 flex justify-end">
               <button @click="showDetailModal = false" class="bg-slate-950 text-white px-12 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-sky-500 transition-all shadow-xl">
                  FERMER LE DOSSIER
               </button>
            </div>
         </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
@keyframes float-slow {
  0%, 100% { transform: translateY(0); opacity: 0.05; }
  50% { transform: translateY(-20px); opacity: 0.1; }
}
.animate-float-slow { animation: float-slow 10s infinite ease-in-out; }

@keyframes page-reveal {
  from { opacity: 0; filter: blur(20px); transform: translateY(20px); }
  to { opacity: 1; filter: blur(0); transform: translateY(0); }
}
.animate-page-reveal { animation: page-reveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

@keyframes slide-up-slow {
  from { opacity: 0; transform: translateY(100px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up-slow { animation: slide-up-slow 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f1f5f9;
  border-radius: 20px;
}
</style>