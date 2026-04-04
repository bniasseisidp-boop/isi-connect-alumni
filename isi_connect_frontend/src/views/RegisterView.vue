<template>
  <div class="min-h-screen flex items-center justify-center p-6 relative overflow-hidden bg-white">
    
    <!-- Wow Digital Hub: Binary Rain & Particles -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <!-- Floating Binary Digits (Blue Matrix) -->
      <div 
        v-for="n in 30" :key="'bin-' + n"
        class="absolute font-black text-[10px] text-sky-400/20 animate-binary-float select-none"
        :style="getBinaryStyle(n)"
      >
        {{ n % 2 === 0 ? '1' : '0' }}
      </div>

      <!-- Data Transfer Beams -->
      <div 
        v-for="n in 6" :key="'beam-' + n"
        class="absolute h-[1px] bg-gradient-to-r from-transparent via-sky-500/40 to-transparent animate-data-beam"
        :style="getBeamStyle(n)"
      ></div>
    </div>

    <div class="w-full max-w-2xl relative z-10 animate-scale-in">
      <div class="wow-card rounded-[3.5rem] p-10 border-2 border-sky-100 shadow-2xl relative overflow-hidden bg-white">
        
        <!-- Tech Logo Section -->
        <div class="flex flex-col items-center mb-10">
          <div class="bg-white p-4 rounded-[2.5rem] shadow-xl border-t-4 border-sky-400 transform transition-all duration-700 hover:rotate-2">
            <img src="../assets/images/isi.png" alt="Logo ISI" class="h-14 w-auto" />
          </div>
          <div class="mt-6 text-center">
            <h1 class="text-3xl font-black text-slate-900 tracking-tighter">
              Rejoindre le <span class="text-sky-500">Hub Alumni</span>
            </h1>
            <p class="text-[9px] font-black uppercase tracking-[0.4em] text-slate-400 mt-2">INSCRIPTION RÉSEAU NUMÉRIQUE</p>
          </div>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nom complet -->
            <div class="space-y-1 group">
              <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-sky-600 ml-4 transition-colors">Identité</label>
              <input v-model="name" type="text" required class="w-full px-7 py-4 bg-slate-50 border-2 border-slate-100 rounded-[2rem] text-slate-900 focus:border-sky-500 focus:bg-white transition-all" placeholder="Jean Dupont" />
            </div>
            <!-- Promo -->
            <div class="space-y-1 group">
              <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-sky-600 ml-4 transition-colors">Promo</label>
              <input v-model="promotionYear" type="number" required class="w-full px-7 py-4 bg-slate-50 border-2 border-slate-100 rounded-[2rem] text-slate-900 focus:border-sky-500 focus:bg-white transition-all" placeholder="2026" />
            </div>
          </div>

          <!-- Email -->
          <div class="space-y-1 group">
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-sky-600 ml-4 transition-colors">Point d'accès Email</label>
            <input v-model="email" type="email" required class="w-full px-8 py-4 bg-slate-50 border-2 border-slate-100 rounded-[2.5rem] text-slate-900 focus:border-sky-500 focus:bg-white transition-all" placeholder="votre@email.com" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Password -->
            <div class="space-y-1 group">
              <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-sky-600 ml-4 transition-colors">Clef Sécurisée</label>
              <input v-model="password" type="password" required class="w-full px-7 py-4 bg-slate-50 border-2 border-slate-100 rounded-[2rem] text-slate-900 focus:border-sky-500 focus:bg-white transition-all" placeholder="••••••••" />
            </div>
            <!-- Confirm -->
            <div class="space-y-1 group">
              <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-sky-600 ml-4 transition-colors">Confirmation</label>
              <input v-model="passwordConfirmation" type="password" required class="w-full px-7 py-4 bg-slate-50 border-2 border-slate-100 rounded-[2rem] text-slate-900 focus:border-sky-500 focus:bg-white transition-all" placeholder="••••••••" />
            </div>
          </div>

          <!-- Digital Hub Button: HIGH CONTRAST GUARANTEED -->
          <button 
            type="submit" 
            :disabled="isLoading"
            class="btn-digital w-full py-6 rounded-[2.5rem] uppercase tracking-[0.3em] text-xs relative overflow-hidden mt-4"
          >
            <span class="relative z-10 flex items-center justify-center">
              {{ isLoading ? 'HUB: ENREGISTREMENT...' : 'CRÉER MON ACCÈS NUMÉRIQUE' }}
            </span>
            <div class="absolute inset-0 bg-white/20 -translate-x-full animate-digital-scan"></div>
          </button>
        </form>

        <div class="mt-10 text-center">
          <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">
            DÉJÀ CONNECTÉ ? 
            <RouterLink to="/login" class="text-sky-600 hover:text-sky-800 font-extrabold underline decoration-sky-200 underline-offset-[10px] transition-all ml-2">ACCÉDER AU HUB</RouterLink>
          </p>
        </div>

        <!-- Floating High-Tech Error -->
        <Transition name="slide-up">
          <div v-if="errorMessage" class="fixed bottom-10 left-1/2 -translate-x-1/2 w-80 p-5 bg-slate-900 text-white rounded-3xl text-[10px] font-black uppercase tracking-[0.2em] text-center shadow-2xl border-t-4 border-red-500 z-[99]">
            [ERREUR SYSTÈME]: {{ errorMessage }}
          </div>
        </Transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuth } from '../auth';

const auth = useAuth();
const router = useRouter();

const name = ref('');
const email = ref('');
const promotionYear = ref(new Date().getFullYear());
const password = ref('');
const passwordConfirmation = ref('');
const errorMessage = ref(null);
const isLoading = ref(false);

// Binary Rain Physics
const getBinaryStyle = (n) => {
  return {
    top: Math.random() * 100 + '%',
    left: Math.random() * 100 + '%',
    animationDuration: (Math.random() * 12 + 8) + 's',
    animationDelay: (Math.random() * 5) + 's',
    fontSize: (Math.random() * 8 + 8) + 'px'
  }
}

// Data Beam Physics
const getBeamStyle = (n) => {
  return {
    top: (n * 15) + '%',
    left: '-100%',
    width: (Math.random() * 150 + 150) + 'px',
    animationDuration: (Math.random() * 4 + 2) + 's',
    animationDelay: (Math.random() * 4) + 's'
  }
}

const handleRegister = async () => {
  errorMessage.value = null;
  isLoading.value = true;
  
  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'LES CLEFS NE CORRESPONDENT PAS';
    isLoading.value = false;
    return;
  }

  try {
    const success = await auth.register(
      name.value,
      email.value,
      promotionYear.value,
      password.value,
      passwordConfirmation.value
    );
    
    if (success) {
      router.push({ name: 'dashboard' });
    } else {
      errorMessage.value = "ERREUR D'ACCÈS HUB";
    }
  } catch (error) {
    errorMessage.value = "DÉFAILLANCE RÉSEAU";
    console.error(error);
  } finally {
    isLoading.value = false;
    if (errorMessage.value) setTimeout(() => errorMessage.value = null, 4000)
  }
};
</script>

<style scoped>
@keyframes binary-float {
  0% { transform: translateY(0) rotate(0deg); opacity: 0; }
  20% { opacity: 0.25; }
  80% { opacity: 0.25; }
  100% { transform: translateY(-80px) rotate(360deg); opacity: 0; }
}
.animate-binary-float {
  animation: binary-float linear infinite;
}

@keyframes data-beam {
  0% { transform: translateX(0); opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { transform: translateX(180vw); opacity: 0; }
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
  from { transform: scale(0.98); opacity: 0; filter: blur(5px); }
  to { transform: scale(1); opacity: 1; filter: blur(0); }
}
.animate-scale-in {
  animation: scale-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.slide-up-enter-active, .slide-up-leave-active { transition: all 0.5s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translate(-50%, 100px); opacity: 0; }
</style>