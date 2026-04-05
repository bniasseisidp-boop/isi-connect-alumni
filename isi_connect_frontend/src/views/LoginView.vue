<template>
  <div class="min-h-screen flex items-center justify-center p-6 relative overflow-y-auto bg-white scroll-smooth">


    
    <!-- Wow Digital Hub: Binary Rain & Particles -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <!-- Floating Binary Digits (JS Games Concept) -->
      <div 
        v-for="n in 40" :key="'bin-' + n"
        class="absolute font-black text-[10px] text-sky-400/20 animate-binary-float select-none"
        :style="getBinaryStyle(n)"
      >
        {{ n % 2 === 0 ? '0' : '1' }}
      </div>

      <!-- Data Transfer Beams -->
      <div 
        v-for="n in 8" :key="'beam-' + n"
        class="absolute h-[1px] bg-gradient-to-r from-transparent via-sky-500/40 to-transparent animate-data-beam"
        :style="getBeamStyle(n)"
      ></div>
    </div>

    <div class="w-full max-w-lg relative z-10 animate-scale-in">
      <div class="wow-card rounded-[3.5rem] p-12 border-2 border-sky-100 shadow-2xl relative overflow-hidden bg-white">
        
        <!-- Tech Logo Section -->
        <div class="flex flex-col items-center mb-12">
          <div class="bg-white p-5 rounded-[2.5rem] shadow-xl border-t-4 border-sky-400 transform transition-all duration-700 hover:rotate-3">
            <img src="../assets/images/isi.png" alt="Logo ISI" class="h-16 w-auto" />
          </div>
          <div class="mt-8 text-center">
            <h1 class="text-4xl font-black text-slate-900 tracking-tighter inline-block">
              Suptech <span class="text-sky-500">Alumni</span>
            </h1>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mt-2">HUB NUMÉRIQUE SÉCURISÉ</p>
          </div>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-8">
          <div class="space-y-2 group">
            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-sky-600 ml-4 transition-colors">COMPTE EMAIL</label>
            <div class="relative">
              <input 
                v-model="email" 
                type="email" 
                required 
                class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-100 rounded-[2.5rem] text-slate-900 placeholder-slate-300 focus:border-sky-500 focus:bg-white transition-all duration-500"
                placeholder="votre@email.com"
              />
            </div>
          </div>

          <div v-if="!isResetMode" class="space-y-2 group">
            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-sky-600 ml-4 transition-colors">CLEF D'ACCÈS</label>
            <input 
              v-model="password" 
              type="password" 
              required 
              class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-100 rounded-[2.5rem] text-slate-900 placeholder-slate-300 focus:border-sky-500 focus:bg-white transition-all duration-500"
              placeholder="••••••••"
            />
            <div class="flex justify-end pr-4">
              <button @click.prevent="isResetMode = true; error = null" class="text-[9px] font-black text-slate-400 hover:text-sky-500 uppercase tracking-widest transition-colors">Mot de passe oublié ?</button>
            </div>
          </div>

          <div v-else class="space-y-4">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center px-4">
                  SAISISSEZ VOTRE EMAIL POUR RECEVOIR UN NOUVEAU CODE D'ACCÈS TEMPORAIRE.
              </p>
          </div>

          <!-- Digital Hub Button: HIGH CONTRAST GUARANTEED -->
          <button 
            type="submit" 
            :disabled="loading"
            class="btn-digital w-full py-6 rounded-[2.5rem] uppercase tracking-[0.3em] text-xs relative overflow-hidden"
            :class="isResetMode ? 'bg-rose-500 hover:bg-rose-600' : ''"
          >
            <span class="relative z-10 flex items-center justify-center">
              {{ loading ? (isResetMode ? 'ENVOI...' : 'HUB: CONNEXION...') : (isResetMode ? 'RÉCUPÉRER MON ACCÈS' : 'ACCÉDER AU RÉSEAU') }}
            </span>
            <div class="absolute inset-0 bg-white/20 -translate-x-full animate-digital-scan"></div>
          </button>

          <button v-if="isResetMode" @click.prevent="isResetMode = false; error = null" class="w-full text-center text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-colors">
              RETOURNER À LA CONNEXION
          </button>

        </form>

        <div class="mt-12 text-center">
          <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">
            PAS ENCORE MEMBRE ? 
            <RouterLink to="/register" class="text-sky-600 hover:text-sky-800 font-extrabold underline decoration-sky-200 underline-offset-[12px] transition-all ml-2">S'INSCRIRE</RouterLink>
          </p>
        </div>

        <!-- Floating High-Tech Error -->
        <Transition name="slide-up">
          <div v-if="error" class="fixed bottom-10 left-1/2 -translate-x-1/2 w-80 p-5 bg-slate-900 text-white rounded-3xl text-[10px] font-black uppercase tracking-[0.2em] text-center shadow-2xl border-t-4 border-red-500 z-[99]">
            [ERREUR SYSTÈME]: {{ error }}
          </div>
        </Transition>

        <!-- Success Message -->
        <Transition name="slide-up">
          <div v-if="successMessage" class="fixed bottom-10 left-1/2 -translate-x-1/2 w-80 p-5 bg-green-500 text-white rounded-3xl text-[10px] font-black uppercase tracking-[0.2em] text-center shadow-2xl border-t-4 border-green-700 z-[99]">
            [ALERTE]: {{ successMessage }}
          </div>
        </Transition>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuth } from '../auth'

const auth = useAuth()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref(null)
const successMessage = ref(null)
const isResetMode = ref(false)

onMounted(() => {
  if (route.query.registered === '1') {
    successMessage.value = 'COMPTE CRÉÉ AVEC SUCCÈS ! CONNECTEZ-VOUS.'
    setTimeout(() => successMessage.value = null, 6000)
  }
})

// Binary Rain Physics
const getBinaryStyle = (n) => {
  return {
    top: Math.random() * 100 + '%',
    left: Math.random() * 100 + '%',
    animationDuration: (Math.random() * 15 + 10) + 's',
    animationDelay: (Math.random() * 10) + 's',
    fontSize: (Math.random() * 10 + 10) + 'px'
  }
}

// Data Beam Physics
const getBeamStyle = (n) => {
  return {
    top: (n * 12) + '%',
    left: '-100%',
    width: (Math.random() * 200 + 200) + 'px',
    animationDuration: (Math.random() * 4 + 3) + 's',
    animationDelay: (Math.random() * 5) + 's'
  }
}

const handleLogin = async () => {
  loading.value = true
  error.value = null
  successMessage.value = null

  if (isResetMode.value) {
      try {
          await auth.forgotPassword(email.value);
          successMessage.value = 'CODE ENVOYÉ ! VÉRIFIEZ VOTRE BOÎTE MAIL.'
          isResetMode.value = false
      } catch (err) {
          if (err.response && err.response.status === 422) {
            error.value = 'EMAIL NON RECONNU DANS LA MATRICE'
          } else if (err.response && err.response.status === 500) {
            error.value = 'ERREUR ENVOI EMAIL - RÉESSAYEZ PLUS TARD'
          } else {
            error.value = 'ERREUR RÉSEAU - VÉRIFIEZ VOTRE CONNEXION'
          }
      } finally {
          loading.value = false
          if (successMessage.value) setTimeout(() => successMessage.value = null, 6000)
          if (error.value) setTimeout(() => error.value = null, 5000)
      }
      return
  }

  try {
    const success = await auth.login(email.value, password.value)
    if (success) {
      const userRole = auth.user.value?.role
      if (userRole === 'admin') {
        router.push({ name: 'admin' })
      } else {
        router.push({ name: 'dashboard' })
      }
    }
  } catch (err) {
    if (err.response && err.response.status === 401) {
      error.value = 'IDENTIFIANTS INCORRECTS'
    } else if (!err.response) {
      error.value = 'ERREUR RÉSEAU'
    } else {
      error.value = 'SYSTÈME HORS LIGNE (API ERROR)'
    }
    console.error("DEBUG LOGIN:", err);
  } finally {
    loading.value = false
    if (error.value) setTimeout(() => error.value = null, 4000)
  }
}
</script>

<style>
@keyframes binary-float {
  0% { transform: translateY(0) rotate(0deg); opacity: 0; }
  20% { opacity: 0.3; }
  80% { opacity: 0.3; }
  100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
}
.animate-binary-float {
  animation: binary-float linear infinite;
}

@keyframes data-beam {
  0% { transform: translateX(0); opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { transform: translateX(200vw); opacity: 0; }
}
.animate-data-beam {
  animation: data-beam linear infinite;
}

@keyframes digital-scan {
  from { transform: translateX(-100%) skewX(-20deg); }
  to { transform: translateX(300%) skewX(-20deg); }
}
.animate-digital-scan {
  animation: digital-scan 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes scale-in {
  from { transform: scale(0.95); opacity: 0; filter: blur(10px); }
  to { transform: scale(1); opacity: 1; filter: blur(0); }
}
.animate-scale-in {
  animation: scale-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.slide-up-enter-active, .slide-up-leave-active { transition: all 0.5s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translate(-50%, 100px); opacity: 0; }
</style>