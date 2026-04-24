<template>
  <div>
    <!-- Incoming Call Toast -->
    <Transition name="slide-up">
      <div v-if="incomingCall" class="fixed bottom-24 right-5 z-[400] bg-slate-950 border-2 border-sky-500 rounded-3xl p-5 shadow-[0_0_60px_rgba(14,165,233,0.4)] w-72">
        <div class="flex items-center space-x-3 mb-4">
          <div class="h-10 w-10 rounded-2xl bg-sky-500/20 flex items-center justify-center">
            <VideoCameraIcon class="h-5 w-5 text-sky-400" />
          </div>
          <div>
            <p class="text-[9px] font-black text-sky-400 uppercase tracking-widest">Appel entrant</p>
            <p class="text-white font-black text-sm">{{ incomingCall.caller?.name }}</p>
          </div>
        </div>
        <div class="flex space-x-3">
          <button @click="acceptCall" class="flex-1 py-2.5 bg-green-500 hover:bg-green-400 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
            Accepter
          </button>
          <button @click="rejectCall" class="flex-1 py-2.5 bg-red-500 hover:bg-red-400 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
            Refuser
          </button>
        </div>
      </div>
    </Transition>

    <!-- Call Modal -->
    <div v-if="showCall" class="fixed inset-0 z-[500] bg-slate-950 flex flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 bg-slate-900 border-b border-white/5">
        <div class="flex items-center space-x-3">
          <div class="h-2 w-2 bg-green-400 rounded-full animate-pulse"></div>
          <span class="text-white font-black text-sm uppercase tracking-widest">
            {{ activeCall?.callee?.name || activeCall?.caller?.name || 'Appel en cours' }}
          </span>
        </div>
        <div class="flex items-center space-x-2">
          <span class="text-slate-400 text-xs">{{ callDuration }}</span>
        </div>
      </div>

      <!-- Videos -->
      <div class="flex-1 relative bg-slate-950 overflow-hidden">
        <!-- Remote video (full) -->
        <video ref="remoteVideo" autoplay playsinline class="w-full h-full object-cover"></video>
        <div v-if="!remoteConnected" class="absolute inset-0 flex flex-col items-center justify-center">
          <div class="h-24 w-24 rounded-[2rem] bg-sky-500/20 flex items-center justify-center mb-4 animate-pulse">
            <UserCircleIcon class="h-12 w-12 text-sky-400" />
          </div>
          <p class="text-white font-black">{{ connecting ? 'Connexion...' : 'En attente...' }}</p>
          <div class="flex space-x-1 mt-3">
            <div v-for="i in 3" :key="i" class="h-2 w-2 bg-sky-500 rounded-full animate-bounce" :style="{animationDelay: i*0.15+'s'}"></div>
          </div>
        </div>

        <!-- Local video (pip) -->
        <div class="absolute bottom-6 right-6 w-32 h-24 md:w-48 md:h-36 rounded-2xl overflow-hidden border-2 border-white/20 shadow-2xl bg-slate-900">
          <video ref="localVideo" autoplay playsinline muted class="w-full h-full object-cover scale-x-[-1]"></video>
        </div>
      </div>

      <!-- Controls -->
      <div class="flex items-center justify-center space-x-6 py-6 bg-slate-900 border-t border-white/5">
        <button @click="toggleMic" class="h-14 w-14 rounded-2xl flex items-center justify-center transition-all" :class="micOn ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-red-500 text-white'">
          <MicrophoneIcon v-if="micOn" class="h-6 w-6" />
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
          </svg>
        </button>

        <button @click="toggleCam" class="h-14 w-14 rounded-2xl flex items-center justify-center transition-all" :class="camOn ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-red-500 text-white'">
          <VideoCameraIcon v-if="camOn" class="h-6 w-6" />
          <VideoCameraSlashIcon v-else class="h-6 w-6" />
        </button>

        <button @click="endCall" class="h-16 w-16 rounded-2xl bg-red-500 hover:bg-red-400 text-white flex items-center justify-center shadow-lg shadow-red-500/30 transition-all hover:scale-110">
          <PhoneXMarkIcon class="h-7 w-7" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { VideoCameraIcon, VideoCameraSlashIcon, MicrophoneIcon, PhoneXMarkIcon, UserCircleIcon } from '@heroicons/vue/24/outline'
import apiClient from '../api'
import { useAuth } from '../auth'

const auth = useAuth()

const incomingCall = ref(null)
const showCall = ref(false)
const activeCall = ref(null)
const remoteConnected = ref(false)
const connecting = ref(false)
const micOn = ref(true)
const camOn = ref(true)
const callDuration = ref('00:00')

const localVideo = ref(null)
const remoteVideo = ref(null)

let pc = null
let localStream = null
let pollInterval = null
let durationInterval = null
let durationSeconds = 0
let processedIceCandidates = new Set()

const STUN_SERVERS = {
  iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' },
    { urls: 'stun:stun2.l.google.com:19302' },
    {
      urls: [
        'turn:openrelay.metered.ca:80',
        'turn:openrelay.metered.ca:443',
        'turn:openrelay.metered.ca:443?transport=tcp',
      ],
      username: 'openrelayproject',
      credential: 'openrelayproject',
    }
  ],
  iceCandidatePoolSize: 10,
}

// Expose startCall for external use
const startCall = async (user) => {
  await setupLocalStream()
  pc = new RTCPeerConnection(STUN_SERVERS)
  setupPCEvents()

  localStream.getTracks().forEach(t => pc.addTrack(t, localStream))

  const offer = await pc.createOffer()
  await pc.setLocalDescription(offer)

  const res = await apiClient.post('/video-calls', {
    callee_id: user.id,
    offer: JSON.stringify(offer),
  })

  activeCall.value = res.data.call
  showCall.value = true
  connecting.value = true
  startPolling()
  startDurationTimer()
}

const acceptCall = async () => {
  await setupLocalStream()
  pc = new RTCPeerConnection(STUN_SERVERS)
  setupPCEvents()
  localStream.getTracks().forEach(t => pc.addTrack(t, localStream))

  const offer = JSON.parse(incomingCall.value.offer)
  await pc.setRemoteDescription(new RTCSessionDescription(offer))

  const answer = await pc.createAnswer()
  await pc.setLocalDescription(answer)

  await apiClient.post(`/video-calls/${incomingCall.value.room_id}/answer`, {
    answer: JSON.stringify(answer)
  })

  activeCall.value = incomingCall.value
  incomingCall.value = null
  showCall.value = true
  connecting.value = true
  startPolling()
  startDurationTimer()
}

const rejectCall = async () => {
  if (incomingCall.value) {
    await apiClient.post(`/video-calls/${incomingCall.value.room_id}/end`).catch(() => {})
    incomingCall.value = null
  }
}

const endCall = async () => {
  if (activeCall.value) {
    await apiClient.post(`/video-calls/${activeCall.value.room_id}/end`).catch(() => {})
  }
  cleanup()
}

const setupLocalStream = async () => {
  localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true })
  if (localVideo.value) localVideo.value.srcObject = localStream
}

const setupPCEvents = () => {
  pc.ontrack = (e) => {
    if (remoteVideo.value) {
      remoteVideo.value.srcObject = e.streams[0]
      remoteConnected.value = true
      connecting.value = false
    }
  }

  pc.onicecandidate = async (e) => {
    if (e.candidate && activeCall.value) {
      await apiClient.post(`/video-calls/${activeCall.value.room_id}/ice`, {
        candidate: JSON.stringify(e.candidate)
      }).catch(() => {})
    }
  }

  pc.onconnectionstatechange = () => {
    if (pc?.connectionState === 'disconnected' || pc?.connectionState === 'failed') {
      cleanup()
    }
  }
}

const startPolling = () => {
  pollInterval = setInterval(async () => {
    if (!activeCall.value) return
    try {
      const { data } = await apiClient.get(`/video-calls/${activeCall.value.room_id}`)
      const call = data.call

      if (call.status === 'ended') { cleanup(); return }

      // If we're caller and call is now active, set remote answer
      if (call.answer && pc && pc.signalingState === 'have-local-offer') {
        await pc.setRemoteDescription(new RTCSessionDescription(JSON.parse(call.answer)))
      }

      // Get ICE candidates from other party
      const iceRes = await apiClient.get(`/video-calls/${activeCall.value.room_id}/ice`)
      for (const ice of iceRes.data.candidates) {
        if (!processedIceCandidates.has(ice.id) && pc) {
          processedIceCandidates.add(ice.id)
          await pc.addIceCandidate(new RTCIceCandidate(JSON.parse(ice.candidate))).catch(() => {})
        }
      }
    } catch {}
  }, 1500)
}

const startDurationTimer = () => {
  durationSeconds = 0
  durationInterval = setInterval(() => {
    durationSeconds++
    const m = String(Math.floor(durationSeconds / 60)).padStart(2, '0')
    const s = String(durationSeconds % 60).padStart(2, '0')
    callDuration.value = `${m}:${s}`
  }, 1000)
}

const cleanup = () => {
  clearInterval(pollInterval)
  clearInterval(durationInterval)
  localStream?.getTracks().forEach(t => t.stop())
  pc?.close()
  pc = null
  localStream = null
  showCall.value = false
  activeCall.value = null
  remoteConnected.value = false
  connecting.value = false
  durationSeconds = 0
  callDuration.value = '00:00'
  processedIceCandidates.clear()
}

const toggleMic = () => {
  micOn.value = !micOn.value
  localStream?.getAudioTracks().forEach(t => { t.enabled = micOn.value })
}

const toggleCam = () => {
  camOn.value = !camOn.value
  localStream?.getVideoTracks().forEach(t => { t.enabled = camOn.value })
}

// Poll for incoming calls
let incomingPoll = null
onMounted(() => {
  incomingPoll = setInterval(async () => {
    if (showCall.value) return
    try {
      const { data } = await apiClient.get('/video-calls/incoming')
      incomingCall.value = data.call || null
    } catch {}
  }, 3000)
})

onUnmounted(() => {
  clearInterval(incomingPoll)
  cleanup()
})

defineExpose({ startCall })
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateY(20px) scale(0.95); }
</style>
