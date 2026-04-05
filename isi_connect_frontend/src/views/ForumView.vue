<script setup>
import { ref, onMounted } from 'vue'
import apiClient, { STORAGE_URL } from '../api'
import { RouterLink } from 'vue-router'
import { 
  ChatBubbleLeftRightIcon, 
  PlusIcon, 
  UserIcon, 
  ClockIcon,
  XMarkIcon,
  SparklesIcon,
  ChatBubbleOvalLeftEllipsisIcon,
  CloudArrowUpIcon
} from '@heroicons/vue/24/outline'

const threadsList = ref([])
const isLoading = ref(true)
const showModal = ref(false)
const errorMessage = ref(null)

const selectedFile = ref(null)
const previewUrl = ref(null)

const onFileSelected = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

const newThreadForm = ref({
  title: '',
  body: ''
})

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('fr-FR', options).toUpperCase();
}

const fetchThreads = async () => {
  isLoading.value = true
  try {
    const response = await apiClient.get('/forum-threads')
    threadsList.value = response.data.data
  } catch (error) {
    console.error("DÉFAILLANCE HUB FORUM:", error)
    errorMessage.value = "SYNCHRONISATION FORUM ÉCHOUÉE."
  } finally {
    isLoading.value = false
  }
}

const handleSubmitThread = async () => {
  isLoading.value = true
  errorMessage.value = null
  try {
    const formData = new FormData()
    formData.append('title', newThreadForm.value.title)
    formData.append('body', newThreadForm.value.body)
    
    if (selectedFile.value) {
      formData.append('image', selectedFile.value)
    }

    await apiClient.post('/forum-threads', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    showModal.value = false
    previewUrl.value = null
    selectedFile.value = null
    fetchThreads()
    newThreadForm.value = { title: '', body: '' }
  } catch (error) {
    console.error("ERREUR PUBLICATION FORUM:", error)
    errorMessage.value = "ÉCHEC DU DÉPLOIEMENT DU SUJET."
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchThreads)
</script>

<template>
  <div class="space-y-12 animate-page-reveal">
    
    <!-- Digital Forum Header -->
    <div class="wow-card rounded-3xl md:rounded-[3rem] p-6 md:p-12 bg-slate-950 text-white border-b-8 border-sky-500 relative overflow-hidden group shadow-[0_30px_60px_rgba(0,0,0,0.2)]">

      
      <!-- Binary Rain Overlay -->
      <div class="absolute inset-0 opacity-5 pointer-events-none select-none text-[8px] font-black grid grid-cols-12 gap-4">
        <div v-for="n in 24" :key="n" class="animate-float-slow text-sky-200">{{ n % 2 === 0 ? '1' : '0' }}</div>
      </div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-10">
        <div class="max-w-2xl text-center md:text-left">
          <div class="mx-auto md:mx-0 flex items-center space-x-3 mb-6 bg-sky-500/20 w-fit px-5 py-2 rounded-full border border-sky-400/30">
            <SparklesIcon class="h-3 w-3 text-sky-400 animate-pulse" />
            <span class="text-[9px] font-black uppercase tracking-[0.4em] text-sky-300">HUB DE DISCUSSION ISI</span>
          </div>
          <h1 class="text-3xl md:text-5xl font-black tracking-tighter leading-none mb-4">
            Collectif <span class="text-sky-400">Hub</span>
          </h1>
          <p class="text-slate-400 font-medium text-sm md:text-lg leading-relaxed opacity-80 uppercase tracking-widest">ÉCHANGEZ, APPRENEZ ET PARTAGEZ AVEC L'INTELLIGENCE ISI.</p>

        </div>
        
        <button
          @click="showModal = true"
          class="flex items-center justify-center bg-sky-500 hover:bg-sky-400 text-white px-8 md:px-10 py-5 md:py-6 rounded-2xl md:rounded-[2rem] font-black text-[10px] uppercase tracking-[0.3em] transition-all duration-500 hover:-translate-y-2 active:scale-95 shadow-2xl shadow-sky-500/40"
        >
          <PlusIcon class="h-5 w-5 mr-3 md:mr-4" />
          NOUVEAU SUJET
        </button>
      </div>
    </div>


    <!-- Loading HUB -->
    <div v-if="isLoading && !showModal" class="flex flex-col justify-center items-center h-96">
      <div class="relative h-20 w-20">
        <div class="absolute inset-0 rounded-full border-4 border-sky-100 opacity-20"></div>
        <div class="absolute inset-0 rounded-full border-4 border-t-sky-500 animate-spin"></div>
      </div>
      <p class="mt-8 text-[10px] font-black uppercase tracking-[0.6em] text-sky-600 animate-pulse">SYNCHRONISATION FLUX FORUM...</p>
    </div>

    <!-- Forum Stream -->
    <div v-else class="space-y-6 pb-10">
      
      <div 
        v-for="thread in threadsList" 
        :key="thread.id"
        class="wow-card rounded-[3rem] bg-white border-2 border-slate-50 p-8 flex flex-col md:flex-row items-center justify-between transition-all duration-700 hover:border-sky-100 hover:shadow-2xl hover:shadow-sky-500/[0.05] group/card"
      >
        <div class="flex flex-1 items-center w-full">
          <!-- Author Avatar Hub -->
          <div class="h-16 w-16 bg-slate-900 rounded-2xl flex items-center justify-center text-white mr-8 group-hover/card:bg-sky-500 transition-all duration-500 shadow-lg shrink-0">
             <UserIcon class="h-7 w-7 opacity-50 group-hover/card:opacity-100 transition-opacity" />
          </div>

          <div class="flex-1 truncate pr-4">
            <RouterLink 
              :to="{ name: 'forum-thread', params: { id: thread.id } }"
              class="text-2xl font-black text-slate-800 hover:text-sky-600 transition-colors uppercase tracking-tight block truncate mb-1"
            >
              {{ thread.title }}
            </RouterLink>
            
            <div v-if="thread.image_path" class="mt-4 mb-4 rounded-2xl overflow-hidden h-32 w-full border border-slate-100">
               <img :src="STORAGE_URL + thread.image_path" class="w-full h-full object-cover" />
            </div>

            <div class="flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest gap-4">
               <span class="text-sky-500">{{ thread.user.name }}</span>
               <span class="opacity-30">|</span>
               <div class="flex items-center">
                 <ClockIcon class="h-3.5 w-3.5 mr-2" />
                 {{ formatDate(thread.created_at) }}
               </div>
            </div>
          </div>
        </div>
        
        <div class="mt-6 md:mt-0 flex items-center gap-10">
          <div class="flex flex-col items-center justify-center">
             <span class="text-3xl font-black text-slate-900 group-hover/card:text-sky-500 transition-colors">{{ thread.replies_count }}</span>
             <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Réponses</span>
          </div>
          <RouterLink 
            :to="{ name: 'forum-thread', params: { id: thread.id } }"
            class="flex items-center space-x-3 px-8 py-4 rounded-2xl bg-slate-900 text-white font-black text-[9px] uppercase tracking-widest hover:bg-sky-500 transition-all shadow-xl hover:-translate-y-1"
          >
            <span>VOIR LA DISCUSSION</span>
            <ChatBubbleOvalLeftEllipsisIcon class="h-4 w-4" />
          </RouterLink>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="!isLoading && threadsList.length === 0" class="py-32 text-center wow-card rounded-[4rem] border-4 border-dashed border-slate-100">
        <ChatBubbleLeftRightIcon class="h-16 w-16 mx-auto mb-8 text-sky-200/50" />
        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-slate-300">PROTOCOLE COMMUNICATION VIDE</h3>
        <p class="mt-4 text-slate-400 text-[10px] font-bold uppercase tracking-widest">LANCEZ LE PREMIER ÉCHANGE RÉSEAU.</p>
      </div>
    </div>
    
    <!-- Premium Thread Creation Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-0">
      <div @click="showModal = false" class="absolute inset-0 bg-slate-950/80 backdrop-blur-xl animate-fade-in"></div>
      
      <form 
        @submit.prevent="handleSubmitThread" 
        class="relative w-full max-w-2xl bg-white rounded-[4rem] shadow-[-20px_40px_80px_rgba(0,0,0,0.3)] border-b-[12px] border-sky-500 animate-slide-up-slow overflow-hidden"
      >
        <!-- Modal Header -->
        <div class="bg-slate-950 p-10 flex items-center justify-between border-b border-white/5 relative overflow-hidden">
          <div class="relative z-10">
            <h2 class="text-3xl font-black text-white tracking-tighter uppercase">Initialiser <span class="text-sky-400">Discussion</span></h2>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.4em] mt-2">DÉPLOIEMENT FORUM ISI</p>
          </div>
          <button @click="showModal = false" type="button" class="relative z-10 h-14 w-14 bg-white/5 hover:bg-red-500 rounded-2xl flex items-center justify-center text-white transition-all duration-500 group">
            <XMarkIcon class="h-6 w-6 group-hover:rotate-90 transition-transform" />
          </button>
          <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#0ea5e9_1px,transparent_1px)] bg-[size:10px_10px]"></div>
        </div>
        
        <!-- Form Content -->
        <div class="p-10 md:p-14 space-y-10 max-h-[60vh] overflow-y-auto custom-scrollbar">
          <div v-if="errorMessage" class="rounded-3xl bg-red-50 border-2 border-red-100 p-6">
            <p class="text-[10px] font-black text-red-600 uppercase tracking-widest">{{ errorMessage }}</p>
          </div>

          <div class="space-y-10">
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-2">Titre du sujet (Wow Factor)</label>
              <input type="text" v-model="newThreadForm.title" required class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-7 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none" placeholder="EX: CONSEILS POUR CARRIÈRE IA" />
            </div>
            
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-2">Corps de la Pensée</label>
              <textarea v-model="newThreadForm.body" rows="7" required class="hub-input w-full rounded-[2rem] bg-slate-50 border-2 border-slate-100 p-8 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none" placeholder="PARTAGEZ VOTRE INTELLIGENCE ICI..."></textarea>
            </div>

            <!-- Forum Image HUB -->
            <div class="space-y-4">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Visuel Illustratif (Optionnel)</label>
              <div class="flex items-center space-x-8">
                <div v-if="previewUrl" class="h-24 w-40 rounded-2xl overflow-hidden border-2 border-sky-400 shadow-lg shrink-0">
                  <img :src="previewUrl" class="w-full h-full object-cover" />
                </div>
                <label for="forum_image" class="flex-1 cursor-pointer flex flex-col items-center justify-center border-4 border-dashed border-slate-100 hover:border-sky-300 rounded-3xl p-8 transition-colors group/upload">
                   <CloudArrowUpIcon class="h-8 w-8 text-slate-300 group-hover/upload:text-sky-500 transition-colors mb-2" />
                   <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em]">Cliquez pour uploader une image</span>
                   <input type="file" id="forum_image" @change="onFileSelected" class="hidden" accept="image/*" />
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
            ANNULER
          </button>
          <button 
            type="submit" 
            :disabled="isLoading"
            class="flex items-center justify-center bg-slate-950 text-white px-10 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all hover:bg-sky-600 disabled:opacity-50"
          >
            {{ isLoading ? 'PUBLICATION...' : 'DÉPLOYER LE SUJET' }}
          </button>
        </div>
      </form>
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