<script setup>
import { ref, onMounted } from 'vue'
import apiClient, { STORAGE_URL } from '../api'
import { 
  BriefcaseIcon, 
  PlusIcon, 
  MapPinIcon, 
  BuildingOfficeIcon, 
  ClockIcon,
  XMarkIcon,
  SparklesIcon,
  PaperAirplaneIcon,
  CloudArrowUpIcon
} from '@heroicons/vue/24/outline'

const jobList = ref([])
const isLoading = ref(true)
const showModal = ref(false)
const errorMessage = ref(null)

const selectedFile = ref(null)
const previewUrl = ref(null)
const showDetailModal = ref(false)
const selectedJob = ref(null)

const onFileSelected = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

const newJobForm = ref({
  title: '',
  company_name: '',
  location: '',
  type: 'CDI',
  description: '',
  apply_url: ''
})

const fetchJobs = async () => {
  isLoading.value = true
  try {
    const response = await apiClient.get('/job-postings')
    jobList.value = response.data.data
  } catch (error) {
    console.error("DÉFAILLANCE HUB EMPLOIS:", error)
    errorMessage.value = "IMPOSSIBLE DE SYNCHRONISER LES OFFRES."
  } finally {
    isLoading.value = false
  }
}

const handleSubmitJob = async () => {
  isLoading.value = true
  errorMessage.value = null
  try {
    const formData = new FormData()
    Object.keys(newJobForm.value).forEach(key => {
      formData.append(key, newJobForm.value[key])
    })
    
    if (selectedFile.value) {
      formData.append('image', selectedFile.value)
    }

    await apiClient.post('/job-postings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    showModal.value = false
    previewUrl.value = null
    selectedFile.value = null
    fetchJobs()
    newJobForm.value = { title: '', company_name: '', location: '', type: 'CDI', description: '', apply_url: '' }
    // Reset file input physically
    const fileInput = document.getElementById('job_image')
    if (fileInput) fileInput.value = ''
  } catch (error) {
    console.error("ERREUR PUBLICATION:", error)
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(' | ')
    } else {
      errorMessage.value = error.response?.data?.message || "PROTOCOLE DE PUBLICATION ÉCHOUÉ."
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchJobs)
</script>

<template>
  <div class="space-y-12 animate-page-reveal">
    
    <!-- Digital Career Header -->
    <div class="wow-card rounded-3xl md:rounded-[3rem] p-6 md:p-12 bg-slate-950 text-white border-b-8 border-sky-500 relative overflow-hidden group shadow-[0_30px_60px_rgba(0,0,0,0.2)]">

      
      <!-- Binary Rain Overlay -->
      <div class="absolute inset-0 opacity-5 pointer-events-none select-none text-[8px] font-black grid grid-cols-12 gap-4">
        <div v-for="n in 24" :key="n" class="animate-float-slow text-sky-200">{{ n % 2 === 0 ? '1' : '0' }}</div>
      </div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-8 md:gap-10">
        <div class="max-w-2xl text-center md:text-left">
          <div class="mx-auto md:mx-0 flex items-center space-x-3 mb-6 bg-sky-500/20 w-fit px-5 py-2 rounded-full border border-sky-400/30">
            <SparklesIcon class="h-3 w-3 text-sky-400 animate-pulse" />
            <span class="text-[9px] font-black uppercase tracking-[0.4em] text-sky-300">HUB CARRIÈRE SUPTECH</span>
          </div>
          <h1 class="text-3xl md:text-5xl font-black tracking-tighter leading-none mb-4">
            Opportunités <span class="text-sky-400">Digitales</span>
          </h1>
          <p class="text-slate-400 font-medium text-sm md:text-lg leading-relaxed opacity-80 uppercase tracking-widest">PROPULSEZ VOTRE AVENIR DANS L'ÉCOSYSTÈME ISI.</p>
        </div>
        
        <button
          @click="showModal = true"
          class="flex items-center justify-center bg-sky-500 hover:bg-sky-400 text-white px-8 py-5 rounded-2xl md:rounded-[1.5rem] font-black text-[10px] uppercase tracking-[0.3em] transition-all duration-500 hover:-translate-y-2 active:scale-95 shadow-2xl shadow-sky-500/40"
        >
          <PlusIcon class="h-5 w-5 mr-3 md:mr-4" />
          PUBLIER UNE OFFRE
        </button>
      </div>
    </div>

    <!-- Loading Interface -->
    <div v-if="isLoading && !showModal" class="flex flex-col justify-center items-center h-96">
      <div class="relative h-20 w-20">
        <div class="absolute inset-0 rounded-full border-4 border-sky-100 opacity-20"></div>
        <div class="absolute inset-0 rounded-full border-4 border-t-sky-500 animate-spin"></div>
      </div>
      <p class="mt-8 text-[10px] font-black uppercase tracking-[0.6em] text-sky-600 animate-pulse">SYNCHRONISATION CARRIÈRE...</p>
    </div>

    <!-- Jobs Stream -->
    <div v-else class="space-y-8 pb-10">
      
      <div 
        v-for="job in jobList" 
        :key="job.id"
        class="wow-card rounded-3xl md:rounded-[3rem] bg-white border-2 border-slate-50 p-6 md:p-8 flex flex-col md:flex-row items-start md:items-center justify-between transition-all duration-700 hover:border-sky-100 hover:shadow-2xl hover:shadow-sky-500/[0.05] group/card"
      >


        <div class="flex-1">
          <div v-if="job.image_path" class="mb-6 rounded-3xl overflow-hidden h-48 w-full border-2 border-slate-50">
             <img :src="STORAGE_URL + job.image_path" class="w-full h-full object-cover" />
          </div>
          <div class="flex flex-wrap items-center gap-6 mb-4">
            <h3 class="text-2xl font-black text-slate-900 group-hover/card:text-sky-600 transition-colors uppercase tracking-tight">{{ job.title }}</h3>
            <div class="px-5 py-1.5 rounded-full bg-slate-900 text-[9px] font-black text-sky-400 uppercase tracking-widest">
              {{ job.type }}
            </div>
          </div>
          <div class="flex flex-wrap gap-x-10 gap-y-3">
            <div class="flex items-center text-slate-400 group/info">
              <BuildingOfficeIcon class="h-4 w-4 mr-3 text-sky-500 group-hover/info:scale-125 transition-transform" />
              <span class="text-[10px] font-black uppercase tracking-widest">{{ job.company_name }}</span>
            </div>
            <div class="flex items-center text-slate-400 group/info">
              <MapPinIcon class="h-4 w-4 mr-3 text-sky-500 group-hover/info:scale-125 transition-transform" />
              <span class="text-[10px] font-black uppercase tracking-widest">{{ job.location }}</span>
            </div>
          </div>
        </div>
        
        <div class="mt-8 md:mt-0 flex flex-col sm:flex-row gap-4">
          <button 
            @click="selectedJob = job; showDetailModal = true"
            class="flex items-center bg-slate-50 hover:bg-slate-100 px-8 py-5 rounded-2xl transition-all duration-500 text-slate-500 font-black text-[10px] uppercase tracking-widest border-2 border-transparent hover:border-slate-200"
          >
            DÉTAILS
          </button>
          <a 
            :href="job.apply_url || 'mailto:recrutement@isi.sn'" 
            target="_blank"
            class="flex items-center bg-slate-950 hover:bg-sky-500 px-10 py-5 rounded-2xl group/btn transition-all duration-500 text-white shadow-xl hover:-translate-y-1"
          >
            <span class="font-black text-[10px] uppercase tracking-widest mr-4">REJOINDRE</span>
            <PaperAirplaneIcon class="h-4 w-4 transform group-hover/btn:translate-x-2 transition-transform" />
          </a>
        </div>
      </div>
      
      <!-- Empty State: Digital Art -->
      <div v-if="!isLoading && jobList.length === 0" class="py-32 text-center wow-card rounded-[4rem] border-4 border-dashed border-slate-100">
        <BriefcaseIcon class="h-16 w-16 mx-auto mb-8 text-sky-200/50" />
        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-slate-300">HUB CARRIÈRE EN ATTENTE DE DONNÉES</h3>
        <p class="mt-4 text-slate-400 text-[10px] font-bold uppercase tracking-widest">SOYEZ LE PREMIER À LANCER UNE OPPORTUNITÉ.</p>
      </div>
    </div>
    
    <!-- Premium Creation Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-0">
      
      <!-- Glass Background -->
      <div @click="showModal = false" class="absolute inset-0 bg-slate-950/80 backdrop-blur-xl animate-fade-in"></div>
      
      <form 
        @submit.prevent="handleSubmitJob" 
        class="relative w-full max-w-2xl bg-white rounded-[4rem] shadow-[-20px_40px_80px_rgba(0,0,0,0.3)] border-b-[12px] border-sky-500 animate-slide-up-slow overflow-hidden"
      >
        
        <!-- Modal Header -->
        <div class="bg-slate-950 p-10 flex items-center justify-between border-b border-white/5 relative overflow-hidden">
          <div class="relative z-10">
            <h2 class="text-3xl font-black text-white tracking-tighter uppercase">Nouvelle <span class="text-sky-400">Opportunité</span></h2>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.4em] mt-2">DÉPLOIEMENT RÉSEAU ISI</p>
          </div>
          <button @click="showModal = false" class="relative z-10 h-14 w-14 bg-white/5 hover:bg-red-500 rounded-2xl flex items-center justify-center text-white transition-all duration-500 group">
            <XMarkIcon class="h-6 w-6 group-hover:rotate-90 transition-transform" />
          </button>
          <!-- Grid in header -->
          <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#0ea5e9_1px,transparent_1px)] bg-[size:10px_10px]"></div>
        </div>
        
        <!-- Form Content -->
        <div class="p-10 md:p-14 space-y-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
          
          <div v-if="errorMessage" class="rounded-3xl bg-red-50 border-2 border-red-100 p-6">
            <p class="text-[10px] font-black text-red-600 uppercase tracking-widest">{{ errorMessage }}</p>
          </div>

          <div class="grid grid-cols-1 gap-10 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Titre du poste d'élite</label>
              <input type="text" v-model="newJobForm.title" required class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none" placeholder="EX: INGÉNIEUR SYSTÈME" />
            </div>
            
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Entreprise Visionnaire</label>
              <input type="text" v-model="newJobForm.company_name" required class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none" />
            </div>
            
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Localisation Hub</label>
              <input type="text" v-model="newJobForm.location" required class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none" placeholder="EX: DAKAR, SN" />
            </div>
            
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Type de Protocole</label>
              <select v-model="newJobForm.type" class="w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 text-sm font-black appearance-none focus:border-sky-500 focus:bg-white outline-none">
                <option>CDI</option>
                <option>CDD</option>
                <option>Stage</option>
                <option>Freelance</option>
              </select>
            </div>
            
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Lien d'Accès / Email</label>
              <input type="text" v-model="newJobForm.apply_url" required class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none" placeholder="HTTPS://... OU EMAIL@..." />
            </div>
            
            <div class="sm:col-span-2">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Description de la Mission</label>
              <textarea v-model="newJobForm.description" rows="5" required class="hub-input w-full rounded-[2rem] bg-slate-50 border-2 border-slate-100 p-8 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none"></textarea>
            </div>

            <!-- Image Upload HUB -->
            <div class="sm:col-span-2 space-y-4">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Visuel de l'opportunité (Optionnel)</label>
              <div class="flex items-center space-x-8">
                <div v-if="previewUrl" class="h-24 w-40 rounded-2xl overflow-hidden border-2 border-sky-400 shadow-lg shrink-0">
                  <img :src="previewUrl" class="w-full h-full object-cover" />
                </div>
                <label for="job_image" class="flex-1 cursor-pointer flex flex-col items-center justify-center border-4 border-dashed border-slate-100 hover:border-sky-300 rounded-3xl p-8 transition-colors group/upload">
                   <CloudArrowUpIcon class="h-8 w-8 text-slate-300 group-hover/upload:text-sky-500 transition-colors mb-2" />
                   <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em]">Cliquez pour uploader un visuel</span>
                   <input type="file" id="job_image" @change="onFileSelected" class="hidden" accept="image/*" />
                </label>
              </div>
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
            ANNULER LE DÉPLOIEMENT
          </button>
          <button 
            type="submit" 
            :disabled="isLoading"
            class="flex items-center justify-center bg-slate-950 text-white px-10 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all hover:bg-sky-600 disabled:opacity-50"
          >
            {{ isLoading ? 'SYNCHRONISATION...' : 'DÉPLOYER L\'OFFRE' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Job Detail Modal Matrix -->
    <div v-if="showDetailModal && selectedJob" class="fixed inset-0 z-[110] flex items-center justify-center p-6">
      <div @click="showDetailModal = false" class="absolute inset-0 bg-slate-950/90 backdrop-blur-3xl"></div>
      <div class="relative w-full max-w-3xl bg-white rounded-[4rem] overflow-hidden shadow-2xl border-2 border-white/20 animate-page-reveal">
         <div class="bg-slate-950 p-12 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#0ea5e9_1px,transparent_1px)] bg-[size:15px_15px]"></div>
            <div class="relative flex justify-between items-start">
               <div>
                  <div class="flex items-center space-x-3 mb-4 bg-sky-500/20 w-fit px-4 py-2 rounded-full border border-sky-400/30">
                    <BriefcaseIcon class="h-3 w-3 text-sky-400" />
                    <span class="text-[8px] font-black uppercase tracking-[0.3em] text-sky-300">HUB CARRIÈRE DÉTAILS</span>
                  </div>
                  <h2 class="text-4xl md:text-5xl font-black text-white uppercase tracking-tighter">{{ selectedJob.title }}</h2>
               </div>
               <button @click="showDetailModal = false" class="h-14 w-14 bg-white/10 rounded-2xl flex items-center justify-center text-white hover:bg-red-500 transition-all">
                  <XMarkIcon class="h-6 w-6" />
               </button>
            </div>
         </div>
         
         <div class="p-12 space-y-10 max-h-[60vh] overflow-y-auto custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
               <div class="space-y-2">
                  <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">ENTREPRISE</p>
                  <p class="text-lg font-bold text-slate-800">{{ selectedJob.company_name }}</p>
               </div>
               <div class="space-y-2">
                  <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">LOCALISATION</p>
                  <p class="text-lg font-bold text-slate-800">{{ selectedJob.location }}</p>
               </div>
               <div class="space-y-2">
                  <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">CONTRAT</p>
                  <p class="text-lg font-bold text-sky-500">{{ selectedJob.type }}</p>
               </div>
            </div>
            
            <div class="pt-8 border-t border-slate-50">
               <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4">MISSION COMPLÈTE</p>
               <p class="text-slate-600 text-lg leading-relaxed whitespace-pre-wrap">{{ selectedJob.description }}</p>
            </div>

            <div class="pt-10 flex space-x-6 justify-end">
               <button @click="showDetailModal = false" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-colors">
                  FERMER
               </button>
               <a :href="selectedJob.apply_url" target="_blank" class="bg-slate-950 text-white px-12 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-sky-500 transition-all shadow-xl">
                  POSTULER MAINTENANT
               </a>
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