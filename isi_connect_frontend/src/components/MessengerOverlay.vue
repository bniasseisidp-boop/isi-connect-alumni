<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { messengerState, toggleMessenger } from '../messenger'
import apiClient, { STORAGE_URL } from '../api'
import { useAuth } from '../auth'
import { 
  ChatBubbleLeftRightIcon, 
  XMarkIcon, 
  PaperAirplaneIcon,
  ChevronRightIcon,
  UserCircleIcon,
  SparklesIcon,
  VideoCameraIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

const auth = useAuth()
const messages = ref([])
const conversations = ref([])
const newMessage = ref('')
const intervalId = ref(null)

const fetchConversations = async () => {
  try {
    const response = await apiClient.get('/messenger')
    conversations.value = response.data
  } catch (error) {
    console.error("ERREUR MESSENGER LIST:", error)
  }
}

const fetchMessages = async () => {
  if (!messengerState.activeChat) return
  try {
    const response = await apiClient.get(`/messenger/${messengerState.activeChat.id}`)
    messages.value = response.data.messages
  } catch (error) {
    console.error("ERREUR MESSAGES:", error)
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !messengerState.activeChat) return
  try {
    const response = await apiClient.post(`/messenger/${messengerState.activeChat.id}`, {
      body: newMessage.value
    })
    messages.value.push(response.data)
    newMessage.value = ''
    scrollToBottom()
  } catch (error) {
    console.error("ERREUR ENVOI:", error)
  }
}

const startCall = () => {
  if (!messengerState.activeChat) return
  // On génère un nom de salon unique basé sur les IDs des deux utilisateurs
  const myId = auth.user.value.id
  const otherId = messengerState.activeChat.id
  const roomName = `ISI_CONNECT_CALL_${Math.min(myId, otherId)}_${Math.max(myId, otherId)}`
  const jitsiUrl = `https://meet.jit.si/${roomName}`
  
  // On envoie un message automatique pour dire qu'on lance un appel
  newMessage.value = `📞 INITIATION APPEL VIDÉO : ${jitsiUrl}`
  sendMessage()
  
  window.open(jitsiUrl, '_blank')
}

const scrollToBottom = () => {
  setTimeout(() => {
    const container = document.getElementById('chat-container')
    if (container) container.scrollTop = container.scrollHeight
  }, 100)
}

const selectConversation = (user) => {
  messengerState.activeChat = user
  fetchMessages()
}

const getUserPhoto = (user) => {
  const photo = user?.profile?.profile_picture_url;
  if (!photo) return null;
  return photo.startsWith('http') ? photo : (STORAGE_URL + photo);
}

// Polling pour le "temps réel" simulé
onMounted(() => {
  fetchConversations()
  intervalId.value = setInterval(() => {
    if (messengerState.isOpen) {
      fetchConversations()
      if (messengerState.activeChat) fetchMessages()
    }
  }, 5000)
})

onUnmounted(() => {
  if (intervalId.value) clearInterval(intervalId.value)
})

watch(() => messengerState.activeChat, (newVal) => {
  if (newVal) {
    fetchMessages()
    scrollToBottom()
  }
})
</script>

<template>
  <div class="fixed bottom-4 md:bottom-5 right-4 md:right-5 z-[200] flex flex-col items-end space-y-3">



    
    <!-- Chat Window Matrix -->
    <transition
      enter-active-class="transition duration-500 ease-out"
      enter-from-class="translate-y-20 opacity-0 scale-95 blur-xl"
      enter-to-class="translate-y-0 opacity-100 scale-100 blur-0"
      leave-active-class="transition duration-300 ease-in"
      leave-from-class="translate-y-0 opacity-100 scale-100 blur-0"
      leave-to-class="translate-y-20 opacity-0 scale-90 blur-xl"
    >
      <div v-if="messengerState.isOpen" class="w-[calc(100vw-32px)] sm:w-[380px] md:w-[410px] h-[75vh] md:h-[580px] bg-white rounded-3xl md:rounded-[2.5rem] shadow-[0_50px_100px_rgba(0,0,0,0.2)] border-2 border-slate-50 flex overflow-hidden relative transition-all">



        
        <!-- Sidebar Conversations -->
        <div class="w-16 md:w-16 bg-slate-950 flex flex-col items-center py-6 md:py-6 space-y-6 md:space-y-6 border-r border-white/5">



           <div v-for="conv in conversations" :key="conv.id" 
                @click="selectConversation(conv.user_one_id === auth.user.value.id ? conv.user_two : conv.user_one)"
                class="relative cursor-pointer group"
           >
              <div class="h-10 w-10 md:h-10 md:w-10 rounded-xl md:rounded-xl bg-white/10 flex items-center justify-center overflow-hidden border-2 border-transparent transition-all hover:border-sky-500 group-hover:rotate-6"
                   :class="messengerState.activeChat?.id === (conv.user_one_id === auth.user.value.id ? conv.user_two.id : conv.user_one.id) ? 'border-sky-500 rotate-6 scale-110 shadow-[0_0_20px_rgba(14,165,233,0.4)]' : ''"
              >
                  <img v-if="getUserPhoto(conv.user_one_id === auth.user.value.id ? conv.user_two : conv.user_one)" 
                       :src="getUserPhoto(conv.user_one_id === auth.user.value.id ? conv.user_two : conv.user_one)" 
                       class="h-full w-full object-cover" />
                  <UserCircleIcon v-else class="h-5 w-5 md:h-5 md:w-5 text-white/20" />
              </div>


              <!-- Online indicator snippet -->
              <div class="absolute -top-1 -right-1 h-4 w-4 bg-green-500 border-4 border-slate-950 rounded-full"></div>
           </div>
           
           <button 
             @click="toggleMessenger(); $router.push('/annuaire')"
             class="h-10 w-10 md:h-10 md:w-10 rounded-xl md:rounded-xl bg-white/5 flex items-center justify-center text-white/20 hover:text-sky-400 transition-colors"
           >
              <PlusIcon class="h-4 w-4 md:h-4 md:w-4" />
           </button>


        </div>

        <!-- Message View Matrix -->
        <div class="flex-1 flex flex-col bg-white">
           <!-- Chat Header -->
           <div class="p-4 md:p-4 border-b border-slate-50 flex items-center justify-between bg-white relative z-20">
             <div v-if="messengerState.activeChat" class="flex items-center space-x-3 md:space-x-3 min-w-0">
                <div class="h-8 w-8 md:h-10 md:w-10 bg-slate-900 rounded-lg md:rounded-lg overflow-hidden shadow-lg border border-slate-950 shrink-0">
                   <img v-if="getUserPhoto(messengerState.activeChat)" :src="getUserPhoto(messengerState.activeChat)" class="h-full w-full object-cover" />
                   <UserCircleIcon v-else class="h-4 w-4 md:h-5 md:w-5 text-slate-700 m-2 md:m-2.5" />
                </div>
                <div class="min-w-0">
                  <p class="text-[9px] md:text-[9px] font-black text-slate-900 uppercase tracking-widest truncate">{{ messengerState.activeChat.name }}</p>
                  <p class="text-[8px] md:text-[8px] font-bold text-green-500 uppercase tracking-widest opacity-80 leading-none mt-0.5 uppercase">EN LIGNE</p>
                </div>
             </div>



            <div v-else>
               <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3rem]">ISI-MESSENGER</p>
            </div>
            
             <div class="flex items-center space-x-1 md:space-x-2">
               <button v-if="messengerState.activeChat" @click="startCall" class="h-8 w-8 md:h-10 md:w-10 bg-sky-50 rounded-lg md:rounded-xl flex items-center justify-center text-sky-500 hover:bg-sky-500 hover:text-white transition-all shadow-sm">
                  <VideoCameraIcon class="h-4 w-4 md:h-5 md:w-5" />
               </button>
               <button @click="toggleMessenger" class="h-8 w-8 md:h-10 md:w-10 bg-slate-50 rounded-lg md:rounded-xl flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all">
                  <XMarkIcon class="h-4 w-4 md:h-5 md:w-5" />
               </button>
             </div>
           </div>


           <!-- Messages Stream -->
           <div id="chat-container" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 md:space-y-5 custom-scrollbar bg-slate-50/30">


            <template v-if="messengerState.activeChat">
              <div v-for="msg in messages" :key="msg.id" 
                   class="flex" :class="msg.sender_id === auth.user.value.id ? 'justify-end' : 'justify-start'"
              >
                <div class="max-w-[85%] md:max-w-[80%] rounded-2xl md:rounded-[2rem] p-4 md:p-6 text-[11px] md:text-sm font-medium shadow-sm transition-all hover:shadow-md"
                     :class="msg.sender_id === auth.user.value.id ? 'bg-slate-950 text-white rounded-br-none' : 'bg-white border border-slate-100 text-slate-700 rounded-bl-none'"
                >
                  {{ msg.body }}
                </div>
              </div>

            </template>
            <div v-else class="h-full flex flex-col items-center justify-center opacity-20 select-none">
               <SparklesIcon class="h-20 w-20 text-slate-300 mb-6" />
               <p class="text-[10px] font-black uppercase tracking-[0.5rem] text-slate-400">SÉLECTIONNEZ UNE FRÉQUENCE</p>
            </div>
          </div>

           <!-- Input Matrix -->
           <div v-if="messengerState.activeChat" class="p-4 md:p-4 border-t border-slate-50 bg-white">


             <div class="relative">
               <input 
                 v-model="newMessage"
                 @keyup.enter="sendMessage"
                 type="text" 
                 class="w-full bg-slate-50 rounded-xl md:rounded-2xl p-4 md:p-6 pr-14 md:pr-16 text-xs md:text-sm font-bold border-2 border-transparent focus:border-sky-500 transition-all outline-none shadow-inner"
                 placeholder="ÉMETTRE UN SIGNAL..."
               />
               <button @click="sendMessage" class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 h-8 w-8 md:h-10 md:w-10 bg-slate-950 text-white rounded-lg md:rounded-xl flex items-center justify-center hover:bg-sky-500 transition-all shadow-xl">
                  <PaperAirplaneIcon class="h-4 w-4 md:h-5 md:w-5" />
               </button>
             </div>
           </div>

        </div>
      </div>
    </transition>

     <!-- Global Toggle Button -->
     <button 
       @click="toggleMessenger"
       class="group relative h-14 w-14 md:h-14 md:w-14 rounded-2xl md:rounded-2xl bg-slate-950 text-white flex items-center justify-center shadow-[0_20px_50px_rgba(0,0,0,0.3)] hover:bg-sky-500 hover:-translate-y-2 transition-all duration-500 overflow-hidden"
     >


       <div v-if="messengerState.unreadCount > 0" class="absolute top-1 right-1 md:top-2 md:right-2 h-4 w-4 md:h-6 md:w-6 bg-red-500 border-2 md:border-4 border-slate-950 rounded-full z-10 animate-ping"></div>
       <div v-if="messengerState.unreadCount > 0" class="absolute top-1 right-1 md:top-2 md:right-2 h-4 w-4 md:h-6 md:w-6 bg-red-500 border-2 md:border-4 border-slate-950 rounded-full z-20 flex items-center justify-center text-[7px] md:text-[8px] font-black">{{ messengerState.unreadCount }}</div>
       
       <transition mode="out-in">
         <XMarkIcon v-if="messengerState.isOpen" class="h-6 w-6 md:h-10 md:w-10" />
         <ChatBubbleLeftRightIcon v-else class="h-6 w-6 md:h-10 md:w-10 group-hover:rotate-12 transition-transform" />
       </transition>
     </button>

  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.text-glow { text-shadow: 0 0 20px rgba(14, 165, 233, 0.5); }
</style>
