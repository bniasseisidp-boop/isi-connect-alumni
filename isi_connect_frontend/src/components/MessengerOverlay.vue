<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { messengerState, toggleMessenger } from '../messenger'
import apiClient, { STORAGE_URL } from '../api'
import { useAuth } from '../auth'
import {
  ChatBubbleLeftRightIcon,
  XMarkIcon,
  PaperAirplaneIcon,
  UserCircleIcon,
  SparklesIcon,
  VideoCameraIcon,
  PlusIcon,
  MicrophoneIcon,
  PhotoIcon,
  StopIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'
import VideoCallOverlay from './VideoCallOverlay.vue'

const auth = useAuth()
const messages = ref([])
const directConversations = ref([])
const groupConversations = ref([])
const newMessage = ref('')
const intervalId = ref(null)

// Media Recording State
const isRecording = ref(false)
const mediaRecorder = ref(null)
const audioChunks = ref([])

const videoCallRef = ref(null)

const fetchConversations = async () => {
  try {
    const response = await apiClient.get('/messenger')
    directConversations.value = response.data.direct
    groupConversations.value = response.data.groups
  } catch (error) {
    console.error("ERREUR MESSENGER LIST:", error)
  }
}

const fetchMessages = async () => {
  if (!messengerState.activeChat) return
  try {
    let endpoint = messengerState.activeChat.work_group_id
      ? `/messenger/group/${messengerState.activeChat.work_group_id}`
      : `/messenger/${messengerState.activeChat.id}`

    const response = await apiClient.get(endpoint)
    const incoming = response.data.messages || []
    if (incoming.length !== messages.value.length) {
      messages.value = incoming
      scrollToBottom()
    }
  } catch (error) {
    console.error("ERREUR MESSAGES:", error)
  }
}

const sendMessage = async (payload = null) => {
  if (!messengerState.activeChat) return
  
  try {
    const isGroup = !!messengerState.activeChat.work_group_id
    const endpoint = isGroup 
      ? `/messenger/group/${messengerState.activeChat.work_group_id}`
      : `/messenger/${messengerState.activeChat.id}`

    let response;
    if (payload instanceof FormData) {
      response = await apiClient.post(endpoint, payload, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      const text = newMessage.value.trim()
      if (!text) return
      newMessage.value = ''
      response = await apiClient.post(endpoint, {
        body: text,
        type: 'text'
      })
    }
    
    messages.value.push(response.data)
    scrollToBottom()
  } catch (error) {
    console.error("ERREUR ENVOI:", error)
  }
}

// --- VOICE RECORDING LOGIC ---
const startRecording = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    mediaRecorder.value = new MediaRecorder(stream)
    audioChunks.value = []

    mediaRecorder.value.ondataavailable = (event) => {
      audioChunks.value.push(event.data)
    }

    mediaRecorder.value.onstop = async () => {
      // Check for better compatibility mime types
      const mimeType = mediaRecorder.value.mimeType || 'audio/webm'
      const extension = mimeType.includes('mp4') ? 'mp4' : (mimeType.includes('mpeg') ? 'mp3' : 'webm')
      
      const audioBlob = new Blob(audioChunks.value, { type: mimeType })
      const formData = new FormData()
      formData.append('file', audioBlob, `voice.${extension}`)
      formData.append('type', 'voice')
      sendMessage(formData)
    }

    mediaRecorder.value.start()
    isRecording.value = true
  } catch (err) {
    alert("Microphone inaccessible : " + err.message)
  }
}

const stopRecording = () => {
  if (mediaRecorder.value && isRecording.value) {
    mediaRecorder.value.stop()
    isRecording.value = false
    // Stop all tracks to release mic
    mediaRecorder.value.stream.getTracks().forEach(track => track.stop())
  }
}

// --- FILE UPLOAD LOGIC ---
const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('file', file)
  
  if (file.type.startsWith('image/')) formData.append('type', 'image')
  else if (file.type.startsWith('video/')) formData.append('type', 'video')
  else formData.append('type', 'text') // fallback

  sendMessage(formData)
}

// --- INTEGRATED VIDEO CALL (WebRTC) ---
const startCall = () => {
  if (!messengerState.activeChat || !videoCallRef.value) return
  videoCallRef.value.startCall(messengerState.activeChat)
}

const scrollToBottom = () => {
  setTimeout(() => {
    const container = document.getElementById('chat-container')
    if (container) container.scrollTop = container.scrollHeight
  }, 100)
}

const selectConversation = (chatObj) => {
  messengerState.activeChat = chatObj
  fetchMessages()
}

const getUserPhoto = (user) => {
  const photo = user?.profile?.profile_picture_url;
  if (!photo) return null;
  return photo.startsWith('http') ? photo : (STORAGE_URL + photo);
}

const getGroupPhoto = (group) => {
  if (!group?.image_path) return null
  return STORAGE_URL + group.image_path
}

onMounted(() => {
  fetchConversations()
  intervalId.value = setInterval(() => {
    if (messengerState.isOpen && messengerState.activeChat) fetchMessages()
  }, 2000)
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

    <!-- Video Call WebRTC -->
    <VideoCallOverlay ref="videoCallRef" />

    <!-- Chat Window Matrix -->
    <transition
      enter-active-class="transition duration-500 ease-out"
      enter-from-class="translate-x-full opacity-0 scale-95 blur-xl"
      enter-to-class="translate-x-0 opacity-100 scale-100 blur-0"
      leave-active-class="transition duration-300 ease-in"
      leave-from-class="translate-x-0 opacity-100 scale-100 blur-0"
      leave-to-class="translate-x-full opacity-0 scale-90 blur-xl"
    >
      <div v-if="messengerState.isOpen" class="w-[340px] sm:w-[420px] md:w-[480px] h-[75vh] md:h-[650px] bg-white dark:bg-slate-950 rounded-3xl md:rounded-[3rem] shadow-[0_50px_100px_rgba(0,0,0,0.4)] border-2 border-slate-50 dark:border-white/5 flex overflow-hidden relative transition-all">

        <!-- Sidebar Conversations Hub -->
        <div class="w-16 md:w-20 bg-slate-950 flex flex-col items-center py-6 space-y-8 border-r border-white/5 overflow-y-auto custom-scrollbar no-scrollbar">
           
           <!-- Direct Chats -->
           <div v-for="conv in directConversations" :key="conv.id" 
                @click="selectConversation(conv.user_one_id === auth.user.value.id ? conv.user_two : conv.user_one)"
                class="relative cursor-pointer group"
           >
              <div class="h-10 w-10 md:h-12 md:w-12 rounded-2xl bg-white/10 flex items-center justify-center overflow-hidden border-2 border-transparent transition-all hover:border-sky-500 group-hover:rotate-6"
                   :class="messengerState.activeChat?.id === (conv.user_one_id === auth.user.value.id ? conv.user_two.id : conv.user_one.id) ? 'border-sky-500 rotate-6 scale-110 shadow-[0_0_25px_rgba(14,165,233,0.5)]' : ''"
              >
                  <img v-if="getUserPhoto(conv.user_one_id === auth.user.value.id ? conv.user_two : conv.user_one)" 
                       :src="getUserPhoto(conv.user_one_id === auth.user.value.id ? conv.user_two : conv.user_one)" 
                       class="h-full w-full object-cover" />
                  <UserCircleIcon v-else class="h-6 w-6 text-white/20" />
              </div>
              <div class="absolute -top-1 -right-1 h-3 w-3 border-2 border-slate-950 rounded-full"
                   :class="(conv.user_one_id === auth.user.value.id ? conv.user_two : conv.user_one)?.is_online ? 'bg-green-500' : 'bg-slate-600'"></div>
           </div>

           <div class="w-8 h-px bg-white/10 rounded-full"></div>

           <!-- Group Chats -->
           <div v-for="gc in groupConversations" :key="gc.id" 
                @click="selectConversation({ ...gc.work_group, work_group_id: gc.work_group_id })"
                class="relative cursor-pointer group"
           >
              <div class="h-10 w-10 md:h-12 md:w-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center overflow-hidden border-2 border-transparent transition-all hover:border-indigo-400 group-hover:-rotate-6"
                   :class="messengerState.activeChat?.work_group_id === gc.work_group_id ? 'border-indigo-400 -rotate-6 scale-110 shadow-[0_0_25px_rgba(129,140,248,0.5)]' : ''"
              >
                  <img v-if="getGroupPhoto(gc.work_group)" :src="getGroupPhoto(gc.work_group)" class="h-full w-full object-cover" />
                  <UserGroupIcon v-else class="h-6 w-6 text-indigo-400/50" />
              </div>
           </div>
           
           <button @click="toggleMessenger(); $router.push('/annuaire')" class="h-10 w-10 md:h-12 md:w-12 rounded-2xl bg-white/5 flex items-center justify-center text-white/20 hover:text-sky-400 hover:bg-white/10 transition-all">
              <PlusIcon class="h-6 w-6" />
           </button>
        </div>

        <!-- Chat Main Area -->
        <div class="flex-1 flex flex-col bg-white dark:bg-slate-900">
           <!-- Header -->
           <div class="p-4 md:p-6 border-b border-slate-50 dark:border-white/5 flex items-center justify-between bg-white dark:bg-slate-900 relative z-20">
             <div v-if="messengerState.activeChat" class="flex items-center space-x-4 min-w-0">
                <div class="h-10 w-10 md:h-12 md:w-12 bg-slate-900 rounded-2xl overflow-hidden shadow-lg border border-slate-950 shrink-0">
                   <img v-if="messengerState.activeChat.work_group_id" :src="getGroupPhoto(messengerState.activeChat)" class="h-full w-full object-cover" />
                   <img v-else-if="getUserPhoto(messengerState.activeChat)" :src="getUserPhoto(messengerState.activeChat)" class="h-full w-full object-cover" />
                   <UserCircleIcon v-else class="h-6 w-6 text-slate-700 m-3" />
                </div>
                <div class="min-w-0">
                  <p class="text-[10px] md:text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-[0.2em] truncate">{{ messengerState.activeChat.name }}</p>
                  <p class="text-[8px] font-bold text-sky-500 uppercase tracking-widest leading-none mt-1">{{ messengerState.activeChat.work_group_id ? 'HUB COLLECTIF' : 'SIGNAL PRIVÉ' }}</p>
                </div>
             </div>
             <div v-else><p class="text-[10px] font-black text-slate-400 dark:text-slate-600 uppercase tracking-[0.3rem]">ISI-MESOSPHERE</p></div>
            
             <div class="flex items-center space-x-2">
               <button v-if="messengerState.activeChat" @click="startCall" class="h-10 w-10 bg-sky-50 dark:bg-sky-500/10 rounded-xl flex items-center justify-center text-sky-500 hover:bg-sky-500 hover:text-white transition-all">
                  <VideoCameraIcon class="h-5 w-5" />
               </button>
               <button @click="toggleMessenger" class="h-10 w-10 bg-slate-50 dark:bg-white/5 rounded-xl flex items-center justify-center text-slate-400 hover:bg-red-500 hover:text-white transition-all">
                  <XMarkIcon class="h-5 w-5" />
               </button>
             </div>
           </div>

           <!-- Messages Flow -->
           <div id="chat-container" class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/20 dark:bg-slate-950/20">
            <div class="flex flex-col justify-end min-h-full p-4 md:p-6 space-y-4">
              <template v-if="messengerState.activeChat">
                <div v-for="msg in messages" :key="msg.id" class="flex" :class="msg.sender_id === auth.user.value.id ? 'justify-end' : 'justify-start'">
                  <div class="max-w-[80%] space-y-1">
                    <p v-if="messengerState.activeChat.work_group_id && msg.sender_id !== auth.user.value.id" class="text-[8px] font-black text-slate-400 uppercase ml-3">{{ msg.sender?.name }}</p>
                    <div class="rounded-[1.5rem] px-4 py-3 text-[12px] md:text-[13px] font-medium shadow-sm"
                         :class="msg.sender_id === auth.user.value.id ? 'bg-slate-950 text-white rounded-br-sm' : 'bg-white dark:bg-slate-800 border border-slate-100 dark:border-white/5 text-slate-700 dark:text-slate-200 rounded-bl-sm'"
                    >
                      <p v-if="msg.type === 'text'" class="whitespace-pre-wrap break-words">{{ msg.body }}</p>
                      <img v-else-if="msg.type === 'image'" :src="msg.file_url" class="rounded-xl max-w-full hover:scale-[1.02] transition-transform" />
                      <video v-else-if="msg.type === 'video'" :src="msg.file_url" controls class="rounded-xl max-w-full"></video>
                      <div v-else-if="msg.type === 'voice'" class="flex flex-col space-y-1 py-1">
                        <div class="flex items-center space-x-2 text-[10px] opacity-60 font-bold uppercase tracking-widest">
                          <MicrophoneIcon class="h-3 w-3" />
                          <span>Message vocal</span>
                        </div>
                        <audio v-if="msg.file_url" :src="msg.file_url" controls preload="none" class="voice-audio" style="width:200px;"></audio>
                        <span v-else class="text-[10px] text-red-400">Fichier audio indisponible</span>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
              <div v-else class="flex flex-col items-center justify-center py-16 opacity-30 select-none">
                <SparklesIcon class="h-16 w-16 text-sky-500/50 mb-4 animate-pulse" />
                <p class="text-[9px] font-black uppercase tracking-[0.5rem] text-slate-400">CHOISISSEZ UNE MATRICE</p>
              </div>
            </div>
           </div>

           <!-- Input Matrix -->
           <div v-if="messengerState.activeChat" class="p-4 md:p-6 border-t border-slate-50 dark:border-white/5 bg-white dark:bg-slate-900">
             <div class="flex items-end space-x-3">
               <!-- File Upload Button -->
               <label class="h-12 w-12 shrink-0 bg-slate-50 dark:bg-white/5 rounded-2xl flex items-center justify-center text-slate-400 hover:text-sky-500 cursor-pointer transition-all">
                  <PhotoIcon class="h-6 w-6" />
                  <input type="file" @change="handleFileUpload" class="hidden" accept="image/*,video/*" />
               </label>
               
               <div class="flex-1 relative">
                 <textarea 
                   v-model="newMessage"
                   @keydown.enter.exact.prevent="sendMessage()"
                   rows="1"
                   class="w-full bg-slate-50 dark:bg-white/5 rounded-2xl p-4 pr-14 text-xs md:text-sm font-bold border-2 border-transparent focus:border-sky-500 transition-all outline-none resize-none no-scrollbar shadow-inner text-slate-800 dark:text-white"
                   placeholder="ÉMETTRE SIGNAL..."
                 ></textarea>
                 <button @click="sendMessage()" class="absolute right-2 bottom-2 h-10 w-10 bg-slate-950 dark:bg-sky-500 text-white rounded-xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all shadow-xl">
                    <PaperAirplaneIcon class="h-5 w-5" />
                 </button>
               </div>

               <!-- Voice Recording Button -->
               <button 
                 @click="isRecording ? stopRecording() : startRecording()"
                 class="h-12 w-12 shrink-0 rounded-2xl flex items-center justify-center transition-all shadow-lg"
                 :class="isRecording ? 'bg-red-500 text-white animate-pulse' : 'bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-red-500'"
               >
                  <StopIcon v-if="isRecording" class="h-6 w-6" />
                  <MicrophoneIcon v-else class="h-6 w-6" />
               </button>
             </div>
           </div>
        </div>
      </div>
    </transition>

    <!-- Floating Toggle Button -->
    <button 
      @click="toggleMessenger"
      class="group relative h-16 w-16 md:h-20 md:w-20 rounded-[2rem] bg-slate-950 dark:bg-sky-600 text-white flex items-center justify-center shadow-[0_25px_60px_rgba(0,0,0,0.4)] hover:-translate-y-3 transition-all duration-500 overflow-hidden"
    >
      <div v-if="messengerState.unreadCount > 0" class="absolute top-2 right-2 h-6 w-6 bg-red-500 border-4 border-slate-950 rounded-full z-20 flex items-center justify-center text-[8px] font-black">{{ messengerState.unreadCount }}</div>
      <transition mode="out-in">
        <XMarkIcon v-if="messengerState.isOpen" class="h-8 w-8 md:h-10 md:w-10" />
        <ChatBubbleLeftRightIcon v-else class="h-8 w-8 md:h-10 md:w-10 group-hover:rotate-12 transition-transform" />
      </transition>
    </button>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(14, 165, 233, 0.2); border-radius: 10px; }
.no-scrollbar::-webkit-scrollbar { display: none; }

/* Fix audio player on mobile browsers */
.voice-audio {
  background: transparent;
  border-radius: 8px;
  outline: none;
  color-scheme: light;
  display: block;
  height: 40px;
}
</style>
