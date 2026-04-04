<template>
  <div class="space-y-12 animate-page-reveal relative">
    
    <!-- Hero Header: Matrix / Plasma Experience -->
    <div class="relative overflow-hidden rounded-[4rem] bg-slate-950 p-16 md:p-24 text-white shadow-[0_40px_100px_rgba(14,165,233,0.15)] border-b-8 border-sky-500 group">
      
      <!-- Plasma Background (Crazy UI) -->
      <div class="absolute inset-0 z-0">
        <div class="absolute -top-1/4 -left-1/4 w-1/2 h-1/2 bg-sky-500/20 rounded-full blur-[120px] animate-plasma-slow"></div>
        <div class="absolute -bottom-1/4 -right-1/4 w-1/2 h-1/2 bg-blue-500/20 rounded-full blur-[120px] animate-plasma-fast"></div>
      </div>
      
      <!-- Binary Rain (Subtle) -->
      <div class="absolute inset-0 opacity-5 pointer-events-none select-none text-[8px] font-black grid grid-cols-12 gap-4">
        <div v-for="n in 36" :key="n" class="animate-float-slow text-sky-200">{{ n % 2 === 0 ? '1' : '0' }}</div>
      </div>

      <div class="relative z-10 flex flex-col items-center text-center">
        <!-- Status Indicator -->
        <div class="flex items-center space-x-4 mb-10 bg-white/5 px-6 py-2.5 rounded-full border border-white/10 backdrop-blur-2xl animate-scale-in">
          <div class="h-2 w-2 bg-green-400 rounded-full animate-pulse shadow-[0_0_10px_#4ade80]"></div>
          <span class="text-[10px] font-black uppercase tracking-[0.5em] text-sky-300">PROTOCOLE ALUMNI : ACTIF</span>
        </div>

        <!-- Beautiful Typography (Crazy Writing) -->
        <h1 class="text-7xl md:text-9xl font-black mb-8 tracking-tighter leading-none animate-title-reveal" v-if="auth.user && auth.user.value">
          HELLO, <span class="bg-gradient-to-r from-sky-400 via-blue-400 to-sky-600 bg-clip-text text-transparent animate-gradient-text">{{ auth.user.value.name.split(' ')[0] }}</span>
        </h1>
        
        <p class="text-slate-400 text-xl font-medium max-w-3xl leading-relaxed opacity-0 animate-fade-in-up">
          Votre écosystème numérique est déverrouillé. Connectez-vous, partagez et propulsez votre carrière au sein du réseau d'élite <span class="text-white font-black underline decoration-sky-500 underline-offset-8">SUPTECH</span>.
        </p>

        <!-- CTA Hub -->
        <div class="mt-12 flex space-x-6 opacity-0 animate-fade-in-up-delay">
          <RouterLink to="/annuaire" class="px-10 py-5 bg-sky-500 hover:bg-sky-400 text-white rounded-3xl font-black text-xs uppercase tracking-[0.3em] shadow-2xl shadow-sky-500/30 transition-all hover:-translate-y-2 active:scale-95">Explorer le Réseau</RouterLink>
          <RouterLink to="/profil" class="px-10 py-5 bg-white/5 hover:bg-white/10 text-white rounded-3xl font-black text-xs uppercase tracking-[0.3em] border border-white/10 transition-all hover:bg-white/15">Accès Profil</RouterLink>
        </div>
      </div>
    </div>

    <!-- Metrics Grid: Animated Numbers -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
      <div v-for="(stat, i) in metrics" :key="i" 
           class="wow-card rounded-[3.5rem] p-12 group relative overflow-hidden transition-all duration-700 hover:-translate-y-4 border-2 border-slate-50">
        <div class="flex items-center justify-between relative z-10">
          <div class="p-6 rounded-[2.5rem] bg-slate-50 group-hover:bg-sky-500 transition-all duration-700 shadow-inner group-hover:shadow-sky-500/20">
            <component :is="stat.icon" class="h-8 w-8 text-sky-500 group-hover:text-white transition-all duration-700 group-hover:rotate-12" />
          </div>
          <div class="text-right">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-2">{{ stat.label }}</p>
            <p class="text-5xl font-black text-slate-900 flex items-baseline justify-end group-hover:text-sky-600 transition-colors">
              <span class="animate-count-up">{{ stat.displayValue }}</span>
              <span class="text-lg ml-1 opacity-40">+</span>
            </p>
          </div>
        </div>
        <!-- Digital light beam -->
        <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-sky-100 rounded-full blur-[100px] opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
      </div>
    </div>

    <!-- Main Content Section: Slide Reveal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 pt-8">
      
      <!-- Modernized Agenda -->
      <section class="lg:col-span-2 wow-card rounded-[4rem] p-12 border-2 border-slate-50 bg-white hover:border-sky-50 transition-all duration-500 shadow-2xl shadow-slate-200/40">
        <div class="flex items-center justify-between mb-16">
          <h2 class="text-4xl font-black text-slate-900 flex items-center tracking-tighter">
            <span class="bg-slate-900 h-10 w-3 rounded-full mr-6 animate-pulse"></span>
            Flux Événements
          </h2>
          <RouterLink to="/evenements" class="group/btn flex items-center bg-slate-50 px-8 py-4 rounded-[2rem] hover:bg-slate-900 hover:text-white transition-all font-black text-[10px] uppercase tracking-widest text-slate-500">
            HUB EVENTS
            <ChevronRightIcon class="h-4 w-4 ml-4 group-hover/btn:translate-x-3 transition-transform" />
          </RouterLink>
        </div>

        <div v-if="latestEvents.length" class="space-y-8">
          <div v-for="event in latestEvents" :key="event.id" 
               class="flex items-center p-10 rounded-[3.5rem] bg-slate-50 hover:bg-white border-2 border-transparent hover:border-sky-100 group/card transition-all duration-700 hover:shadow-[0_20px_40px_rgba(0,0,0,0.05)]">
            <div class="flex flex-col items-center justify-center bg-slate-900 rounded-[2.5rem] px-10 py-6 mr-12 group-hover/card:bg-sky-500 transition-all duration-700">
              <span class="text-[10px] uppercase font-black text-sky-400 group-hover:text-sky-100 tracking-[0.4em] mb-2">
                {{ formatDateTime(event.starts_at, 'MMM') }}
              </span>
              <span class="text-4xl font-black text-white">{{ formatDateTime(event.starts_at, 'DD') }}</span>
            </div>
            <div class="flex-1">
              <h3 class="font-black text-slate-900 text-2xl mb-2 group-hover/card:text-sky-600 transition-colors">{{ event.title }}</h3>
              <div class="flex items-center text-slate-400 font-bold text-xs uppercase tracking-[0.2em]">
                <MapPinIcon class="h-4 w-4 mr-3 text-sky-500 animate-bounce" />
                <span>{{ event.location }}</span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="py-32 text-center bg-slate-50 rounded-[4rem] border-4 border-dashed border-slate-100 grayscale hover:grayscale-0 transition-all">
           <CalendarIcon class="h-20 w-20 mx-auto mb-8 text-sky-200/50" />
           <p class="text-[11px] font-black uppercase tracking-[0.5em] text-slate-300">VOTRE RÉSEAU EST EN ATTENTE D'ÉVÉNEMENTS</p>
        </div>
      </section>

      <!-- Advanced Career Section -->
      <section class="wow-card rounded-[4rem] p-12 bg-slate-900 text-white border-t-8 border-sky-500 shadow-2xl relative overflow-hidden group/jobs">
        <div class="absolute inset-0 bg-sky-500/5 opacity-0 group-hover/jobs:opacity-100 transition-opacity"></div>
        
        <div class="flex items-center justify-between mb-16 relative z-10">
          <h2 class="text-3xl font-black flex items-center tracking-tighter">
            Digital Jobs
          </h2>
          <div class="h-10 w-10 bg-white/10 rounded-2xl flex items-center justify-center animate-spin-slow">
            <SparklesIcon class="h-5 w-5 text-sky-400" />
          </div>
        </div>

        <div v-if="latestJobs.length" class="space-y-12 relative z-10">
          <div v-for="job in latestJobs" :key="job.id" class="group/job cursor-pointer">
            <div class="flex items-start mb-2 group-hover/job:translate-x-4 transition-transform duration-700">
              <div class="h-16 w-16 bg-white/5 rounded-[2rem] flex items-center justify-center mr-8 group-hover/job:bg-sky-500 transition-all duration-700 border border-white/5 group-hover/job:border-sky-400">
                <BriefcaseIcon class="h-7 w-7 text-sky-400 group-hover:text-white" />
              </div>
              <div class="flex-1">
                <h3 class="font-black text-white text-xl leading-tight group-hover/job:text-sky-300 transition-colors">{{ job.title }}</h3>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mt-2">{{ job.company_name }}</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="py-32 text-center bg-white/5 rounded-[3rem] border-2 border-dashed border-white/10 grayscale hover:grayscale-0 transition-all">
          <BriefcaseIcon class="h-16 w-16 mx-auto mb-8 text-sky-200/20" />
          <p class="text-[11px] font-black uppercase tracking-[0.5em] text-slate-500 uppercase">OFFRES EN TRANSIT</p>
        </div>
      </section>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuth } from '../auth'
import apiClient from '../api'
import { 
  CalendarIcon, 
  BriefcaseIcon, 
  ChevronRightIcon,
  MapPinIcon,
  UsersIcon,
  ChatBubbleLeftRightIcon,
  SparklesIcon
} from '@heroicons/vue/24/outline'

const auth = useAuth()
const isLoading = ref(true)
const latestEvents = ref([])
const latestJobs = ref([])

// Digital metrics with animated display values
const metrics = ref([
  { label: 'SYSTÈME : RÉSEAU ISI', value: 4890, displayValue: 0, icon: UsersIcon },
  { label: 'CELLULE OPPORTUNITÉS', value: 112, displayValue: 0, icon: SparklesIcon },
  { label: 'FLUX DE DISCUSSIONS', value: 54, icon: ChatBubbleLeftRightIcon, displayValue: 0 }
])

const formatDateTime = (dateString, format) => {
  const date = new Date(dateString)
  if (format === 'MMM') return date.toLocaleDateString('fr-FR', { month: 'short' }).toUpperCase()
  if (format === 'DD') return date.getDate().toString().padStart(2, '0')
  return date.toLocaleDateString('fr-FR')
}

// Number Ticker Animation (Wow JS Game)
const animateNumbers = () => {
  metrics.value.forEach(m => {
    const target = m.value
    const duration = 2000
    const stepTime = 50
    const steps = duration / stepTime
    const increment = target / steps
    
    let current = 0
    const timer = setInterval(() => {
      current += increment
      if (current >= target) {
        m.displayValue = Math.floor(target).toLocaleString()
        clearInterval(timer)
      } else {
        m.displayValue = Math.floor(current).toLocaleString()
      }
    }, stepTime)
  })
}

const fetchDashboardData = async () => {
  isLoading.value = true
  try {
    const [eventsResponse, jobsResponse] = await Promise.all([
      apiClient.get('/events').catch(() => ({ data: [] })),
      apiClient.get('/job-postings').catch(() => ({ data: [] }))
    ]);

    latestEvents.value = (eventsResponse.data.data || eventsResponse.data || []).slice(0, 3);
    latestJobs.value = (jobsResponse.data.data || jobsResponse.data || []).slice(0, 3);
  } catch (error) {
    console.error("DÉFAILLANCE HUB DATA:", error)
  } finally {
    isLoading.value = false
    animateNumbers()
  }
}

onMounted(fetchDashboardData)
</script>

<style scoped>
/* CRAZY ANIMATIONS */
@keyframes plasma-slow {
  0%, 100% { transform: scale(1) translate(0, 0); }
  50% { transform: scale(1.2) translate(50px, 50px); }
}
.animate-plasma-slow { animation: plasma-slow 15s infinite ease-in-out; }

@keyframes plasma-fast {
  0%, 100% { transform: scale(1) translate(0, 0); }
  50% { transform: scale(1.3) translate(-30px, -70px); }
}
.animate-plasma-fast { animation: plasma-fast 10s infinite ease-in-out; }

@keyframes title-reveal {
  from { letter-spacing: -0.1em; transform: scale(0.8) translateY(50px); opacity: 0; filter: blur(20px); }
  to { letter-spacing: -0.05em; transform: scale(1) translateY(0); opacity: 1; filter: blur(0); }
}
.animate-title-reveal { animation: title-reveal 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

@keyframes gradient-text {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
.animate-gradient-text {
  background-size: 200% auto;
  animation: gradient-text 4s linear infinite;
}

@keyframes fade-in-up {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-fade-in-up { animation: fade-in-up 0.8s ease-out 0.4s forwards; }
.animate-fade-in-up-delay { animation: fade-in-up 0.8s ease-out 0.8s forwards; }

.animate-page-reveal {
  animation: page-reveal 0.8s ease-out;
}
@keyframes page-reveal {
  from { opacity: 0; filter: blur(10px); }
  to { opacity: 1; filter: blur(0); }
}

.animate-spin-slow {
  animation: spin 8s linear infinite;
}
</style>
