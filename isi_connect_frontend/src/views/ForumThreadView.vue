<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import apiClient, { STORAGE_URL } from '../api'
import { useAuth } from '../auth'
import { 
  ArrowLeftIcon, 
  UserIcon, 
  ClockIcon, 
  TrashIcon, 
  PaperAirplaneIcon,
  ChatBubbleBottomCenterTextIcon,
  SparklesIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const auth = useAuth()

const thread = ref(null)
const isLoading = ref(true)
const errorMessage = ref(null)

const newReply = ref('')
const isReplying = ref(false)

const formatDateTime = (dateTimeString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateTimeString).toLocaleString('fr-FR', options).toUpperCase();
}

const fetchThread = async () => {
  isLoading.value = true
  try {
    const threadId = route.params.id
    const response = await apiClient.get(`/forum-threads/${threadId}`)
    thread.value = response.data
  } catch (error) {
    console.error("DÉFAILLANCE LECTURE SUJET:", error)
    errorMessage.value = "FLUX DE DISCUSSION NON DISPONIBLE."
  } finally {
    isLoading.value = false
  }
}

const handlePostReply = async () => {
  if (newReply.value.trim() === '') return
  isReplying.value = true
  errorMessage.value = null
  try {
    const threadId = route.params.id
    await apiClient.post(`/forum-threads/${threadId}/replies`, { body: newReply.value })
    newReply.value = ''
    await fetchThread()
  } catch (error) {
    console.error("ERREUR ENVOI RÉPONSE:", error)
    errorMessage.value = "ÉCHEC DU DÉPLOIEMENT DE LA RÉPONSE."
  } finally {
    isReplying.value = false
  }
}

const handleDeleteReply = async (replyId) => {
  if (!confirm("Voulez-vous vraiment supprimer cette réponse ?")) return
  try {
    await apiClient.delete(`/forum-replies/${replyId}`)
    await fetchThread()
  } catch (error) {
    console.error("ERREUR SUPPRESSION:", error)
    alert("Impossible de supprimer cette réponse.")
  }
}

onMounted(fetchThread)
</script>

<template>
  <div class="space-y-10 animate-page-reveal">
    
    <!-- Header Stream & Navigation -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <RouterLink 
        to="/forum" 
        class="group inline-flex items-center px-6 py-3 rounded-2xl bg-white border-2 border-slate-50 text-slate-500 hover:text-sky-600 hover:border-sky-100 transition-all duration-500"
      >
        <ArrowLeftIcon class="h-4 w-4 mr-3 transition-transform group-hover:-translate-x-2" />
        <span class="text-[10px] font-black uppercase tracking-[0.2em]">Retour au Forum</span>
      </RouterLink>

      <div v-if="thread" class="flex items-center space-x-3 bg-sky-500/10 px-6 py-2 rounded-full border border-sky-400/20">
         <SparklesIcon class="h-3.5 w-3.5 text-sky-500 animate-pulse" />
         <span class="text-[9px] font-black uppercase tracking-[0.4em] text-sky-600">FIL DE DISCUSSION ACTIF</span>
      </div>
    </div>

    <!-- Loading HUB -->
    <div v-if="isLoading" class="flex flex-col justify-center items-center h-96">
      <div class="relative h-20 w-20">
        <div class="absolute inset-0 rounded-full border-4 border-sky-100 opacity-20"></div>
        <div class="absolute inset-0 rounded-full border-4 border-t-sky-500 animate-spin"></div>
      </div>
      <p class="mt-8 text-[10px] font-black uppercase tracking-[0.6em] text-sky-600 animate-pulse">DÉCRYPTAGE DU FIL...</p>
    </div>
    
    <!-- Error State -->
    <div v-else-if="errorMessage" class="wow-card rounded-3xl md:rounded-[3.5rem] p-12 md:p-24 text-center border-4 border-dashed border-red-50">
      <ChatBubbleBottomCenterTextIcon class="h-12 w-12 md:h-16 md:w-16 mx-auto mb-6 text-red-200" />
      <h3 class="text-lg md:text-xl font-black text-red-500 tracking-widest uppercase">{{ errorMessage }}</h3>
    </div>

    <!-- Thread Content -->
    <div v-else-if="thread" class="space-y-8 md:space-y-12">
      
      <!-- Original Message (OP Card) -->
      <div class="wow-card rounded-3xl md:rounded-[4rem] bg-slate-950 p-8 md:p-20 text-white border-b-8 border-sky-500 relative overflow-hidden group shadow-2xl">
        <div class="absolute inset-0 opacity-5 pointer-events-none text-[8px] font-black grid grid-cols-12 gap-4">
          <div v-for="n in 12" :key="n" class="animate-float-slow text-sky-200">{{ n % 2 === 0 ? '1' : '0' }}</div>
        </div>

        <div class="relative z-10">
          <h1 class="text-2xl md:text-6xl font-black mb-6 md:mb-8 tracking-tighter leading-tight uppercase font-black">
            {{ thread.title }}
          </h1>
          
          <div v-if="thread.image_path" class="mb-8 md:mb-10 rounded-2xl md:rounded-[3rem] overflow-hidden border-4 border-white/10 shadow-2xl">
             <img :src="STORAGE_URL + thread.image_path" class="w-full object-contain max-h-[400px] md:max-h-[500px] bg-slate-900" />
          </div>
          
          <div class="flex items-center mb-8 md:mb-10 pb-8 md:pb-10 border-b border-white/10">
            <div class="h-12 w-12 md:h-16 md:w-16 bg-sky-500 rounded-2xl md:rounded-3xl flex items-center justify-center mr-4 md:mr-6 shadow-xl shadow-sky-500/20 shrink-0">
              <UserIcon class="h-6 w-6 md:h-8 md:w-8 text-white" />
            </div>
            <div class="min-w-0">
              <p class="text-xs font-black uppercase tracking-widest text-sky-400 mb-1 truncate">{{ thread.user.name }}</p>
              <div class="flex items-center text-[9px] md:text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-none">
                <ClockIcon class="h-3 w-3 md:h-3.5 md:w-3.5 mr-2" />
                {{ formatDateTime(thread.created_at) }}
              </div>
            </div>
          </div>

          <p class="text-slate-300 text-base md:text-xl font-medium leading-relaxed md:leading-loose whitespace-pre-wrap italic">
            "{{ thread.body }}"
          </p>
        </div>
      </div>
      
      <!-- Replies Stream -->
      <div class="space-y-8 md:space-y-14">
        <div class="flex items-center justify-between px-2 md:px-6">
          <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tighter uppercase flex items-center">
            <span class="bg-sky-500 h-6 md:h-8 w-1.5 md:w-2 rounded-full mr-3 md:mr-4"></span>
            Réponses <span class="text-sky-500 ml-2 md:ml-3">({{ thread.replies.length }})</span>
          </h2>
        </div>
        
        <div class="space-y-6 md:space-y-8">
          <div 
            v-for="reply in thread.replies" 
            :key="reply.id"
            class="wow-card rounded-[2rem] md:rounded-[3rem] p-6 md:p-10 bg-white border-2 border-slate-50 flex gap-4 md:gap-8 transition-all duration-500 hover:border-sky-50 hover:shadow-xl hover:shadow-sky-500/[0.03] group/reply"
          >
            <!-- Reply Avatar -->
            <div class="shrink-0 flex flex-col items-center">
              <div class="h-10 w-10 md:h-16 md:w-16 bg-slate-50 rounded-xl md:rounded-[1.5rem] flex items-center justify-center text-slate-300 border-2 border-slate-100 group-hover/reply:bg-sky-500 group-hover/reply:border-sky-400 group-hover/reply:text-white transition-all duration-500 shadow-inner">
                <UserIcon class="h-5 w-5 md:h-7 md:w-7" />
              </div>
              <div class="w-0.5 md:w-1 h-full bg-slate-50 mt-3 md:mt-4 rounded-full group-hover/reply:bg-sky-100 transition-colors"></div>
            </div>

            <!-- Reply Content -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 md:mb-6 gap-2">
                <div class="min-w-0">
                  <span class="block md:inline text-[10px] md:text-xs font-black text-slate-800 uppercase tracking-widest md:mr-4 truncate">{{ reply.user.name }}</span>
                  <span class="block md:inline text-[8px] md:text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ formatDateTime(reply.created_at) }}</span>
                </div>
                
                <button 
                  v-if="auth.user.value && auth.user.value.id === reply.user_id"
                  @click="handleDeleteReply(reply.id)"
                  class="p-2 md:p-3 rounded-xl md:rounded-2xl bg-red-50 text-red-400 hover:bg-red-500 hover:text-white transition-all duration-500 active:scale-90 w-fit"
                >
                  <TrashIcon class="h-4 w-4 md:h-5 md:w-5" />
                </button>
              </div>
              <p class="text-slate-600 text-sm md:text-lg font-medium leading-relaxed whitespace-pre-wrap">{{ reply.body }}</p>
            </div>
          </div>
          
          <div v-if="thread.replies.length === 0" class="py-16 md:py-24 text-center rounded-3xl md:rounded-[3rem] bg-slate-50 border-4 border-dashed border-slate-100">
            <ChatBubbleBottomCenterTextIcon class="h-12 w-12 md:h-16 md:w-16 mx-auto mb-6 text-sky-200/50" />
            <p class="text-[9px] md:text-[10px] font-black uppercase tracking-[0.5em] text-slate-300">HUB DE RÉPONSES SILENCIEUX</p>
          </div>
        </div>
      </div>
      
      <!-- Global Reply Interface -->
      <div class="wow-card rounded-3xl md:rounded-[4rem] p-6 md:p-12 bg-white border-4 border-sky-50 relative overflow-hidden group/replyzone">
        <h3 class="text-xl md:text-2xl font-black text-slate-900 mb-6 md:mb-8 flex items-center tracking-tighter uppercase font-black uppercase">
          Injecter une réponse
        </h3>
        <form @submit.prevent="handlePostReply" class="space-y-6 md:space-y-8">
          <div class="relative">
            <textarea 
              v-model="newReply"
              rows="4" md:rows="6"
              placeholder="VOTRE INTELLIGENCE ICI..."
              class="hub-input w-full rounded-2xl md:rounded-[2.5rem] bg-slate-50 border-2 border-slate-100 p-6 md:p-10 text-base md:text-lg font-black transition-all focus:border-sky-500 focus:bg-white outline-none placeholder-slate-300 shadow-inner"
              :disabled="isReplying"
            ></textarea>
            
            <!-- Send Button inside input -->
            <button 
              type="submit" 
              :disabled="isReplying"
              class="absolute bottom-4 md:bottom-8 right-4 md:right-8 h-12 w-12 md:h-20 md:w-20 rounded-xl md:rounded-[1.8rem] bg-slate-900 hover:bg-sky-500 text-white flex items-center justify-center transition-all duration-500 shadow-2xl hover:-translate-y-2 active:scale-90 disabled:opacity-50 group/send"
            >
              <PaperAirplaneIcon v-if="!isReplying" class="h-5 w-5 md:h-8 md:w-8 transform group-hover/send:-rotate-45 transition-transform" />
              <div v-else class="h-5 w-5 md:h-8 md:w-8 border-4 border-white/20 border-t-white rounded-full animate-spin"></div>
            </button>
          </div>
          
          <div v-if="errorMessage" class="rounded-xl bg-red-50 border border-red-100 p-4">
             <p class="text-[9px] font-black text-red-600 uppercase tracking-widest text-center">{{ errorMessage }}</p>
          </div>
        </form>
      </div>

    </div>
  </div>
</template>

<style scoped>
@keyframes page-reveal {
  from { opacity: 0; filter: blur(20px); transform: translateY(30px); }
  to { opacity: 1; filter: blur(0); transform: translateY(0); }
}
.animate-page-reveal { animation: page-reveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

@keyframes float-slow {
  0%, 100% { transform: translateY(0); opacity: 0.05; }
  50% { transform: translateY(-20px); opacity: 0.1; }
}
.animate-float-slow { animation: float-slow 10s infinite ease-in-out; }

.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f1f5f9;
  border-radius: 20px;
}
</style>