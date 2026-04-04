<script setup>
import { ref } from 'vue'
import { useAuth } from '../auth'
import apiClient, { STORAGE_URL } from '../api'
import { 
  UserCircleIcon, 
  EnvelopeIcon, 
  BriefcaseIcon, 
  BuildingOfficeIcon, 
  PencilSquareIcon,
  MapPinIcon,
  LinkIcon,
  CloudArrowUpIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  IdentificationIcon,
  SparklesIcon
} from '@heroicons/vue/24/outline'

const auth = useAuth()

const form = ref({
  name: auth.user.value?.name || '',
  email: auth.user.value?.email || '',
  ...auth.user.value?.profile 
})

const isLoading = ref(false)
const successMessage = ref(null)
const errorMessage = ref(null)

const selectedFile = ref(null)
const previewUrl = ref(null)

const getUserPhoto = () => {
    const photo = auth.user.value?.profile?.profile_picture_url;
    if (!photo) return null;
    return photo.startsWith('http') ? photo : (STORAGE_URL + photo);
};

const onFileSelected = (event) => {
    const file = event.target.files[0];
    if (file) {
        selectedFile.value = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

const handleUpdateProfile = async () => {
  isLoading.value = true
  successMessage.value = null
  errorMessage.value = null

  try {
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
        let val = form.value[key];
        if (val !== null && val !== undefined) {
             formData.append(key, val);
        } else {
             formData.append(key, '');
        }
    });
    
    if (selectedFile.value) {
        formData.append('image', selectedFile.value);
    }

    const response = await apiClient.post('/profile?_method=PUT', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    auth.setUser(response.data.user);
    successMessage.value = 'SYSTÈME MIS À JOUR.';
    previewUrl.value = null;
    setTimeout(() => successMessage.value = null, 5000);
  } catch (error) {
    console.error("DÉFAILLANCE SYSTÈME:", error)
    if (error.response && error.response.data) {
        // Collecter toutes les erreurs de validation
        let errors = [];
        if (typeof error.response.data === 'object' && !error.response.data.message) {
             for (let key in error.response.data) {
                 errors.push(error.response.data[key][0]);
             }
        }
        errorMessage.value = errors.length > 0 ? errors.join(" | ") : (error.response.data.message || 'ÉCHEC DE LA SYNCHRONISATION DES DONNÉES.');
    } else {
        errorMessage.value = 'ÉCHEC DE LA SYNCHRONISATION DES DONNÉES.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="space-y-12 animate-page-reveal">
    
    <!-- Digital Profile Header -->
    <div class="wow-card rounded-[3.5rem] p-12 bg-slate-950 text-white border-b-8 border-sky-500 relative overflow-hidden group shadow-2xl">
      <div class="absolute inset-0 opacity-5 pointer-events-none select-none text-[8px] font-black grid grid-cols-12 gap-4">
        <div v-for="n in 12" :key="n" class="animate-float-slow text-sky-200">{{ n % 2 === 0 ? '1' : '0' }}</div>
      </div>

      <div class="relative z-10 flex flex-col md:flex-row items-center gap-10">
        <!-- Avatar HUB -->
        <div class="relative h-40 w-40 group/avatar">
          <div class="absolute inset-0 bg-gradient-to-tr from-sky-400 via-blue-500 to-sky-600 rounded-[3rem] rotate-6 group-hover/avatar:rotate-12 transition-all duration-700 animate-pulse"></div>
          <div class="absolute inset-1.5 bg-slate-900 rounded-[2.5rem] flex items-center justify-center overflow-hidden z-10 border-4 border-slate-950">
            <img v-if="previewUrl || getUserPhoto()" :src="previewUrl || getUserPhoto()" class="h-full w-full object-cover" />
            <UserCircleIcon v-else class="h-20 w-20 text-slate-700 group-hover/avatar:scale-110 transition-transform duration-700" />
          </div>
        </div>

        <div class="text-center md:text-left">
          <div class="flex items-center justify-center md:justify-start space-x-3 mb-4 bg-sky-500/20 w-fit px-5 py-1.5 rounded-full border border-sky-400/30">
            <SparklesIcon class="h-3 w-3 text-sky-400 animate-pulse" />
            <span class="text-[9px] font-black uppercase tracking-[0.4em] text-sky-300">PANNEAU DE CONTRÔLE PROFIL</span>
          </div>
          <h1 class="text-5xl font-black tracking-tighter leading-none mb-3">Mon <span class="text-sky-400">Espace</span></h1>
          <p class="text-slate-400 font-medium text-lg opacity-80 uppercase tracking-widest uppercase">GÉREZ VOTRE IDENTITÉ DANS L'ÉCOSYSTÈME ISI.</p>
        </div>
      </div>
    </div>

    <!-- Configuration Form -->
    <form @submit.prevent="handleUpdateProfile" class="grid grid-cols-1 lg:grid-cols-2 gap-12 pb-20">
      
      <!-- Primary Data Block -->
      <section class="wow-card rounded-[4rem] p-10 md:p-14 bg-white border-2 border-slate-50 space-y-10 shadow-xl shadow-slate-200/50">
        <div class="flex items-center space-x-4 mb-4">
          <IdentificationIcon class="h-8 w-8 text-sky-500" />
          <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Spécifications</h2>
        </div>

        <div class="space-y-8">
          <div class="group/input">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 group-focus-within/input:text-sky-500 transition-colors">Nom de l'Agent</label>
            <div class="relative">
              <input type="text" v-model="form.name" class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 pl-14 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none shadow-inner" />
              <UserCircleIcon class="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within/input:text-sky-500 transition-colors" />
            </div>
          </div>

          <div class="group/input">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 group-focus-within/input:text-sky-500 transition-colors">Canal Email</label>
            <div class="relative">
              <input type="email" v-model="form.email" class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 pl-14 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none shadow-inner" />
              <EnvelopeIcon class="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within/input:text-sky-500 transition-colors" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
            <div class="group/input">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 group-focus-within/input:text-sky-500 transition-colors">Poste Actuel</label>
              <div class="relative">
                <input type="text" v-model="form.job_title" placeholder="EX: DEV FULLSTACK" class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 pl-14 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none shadow-inner" />
                <BriefcaseIcon class="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within/input:text-sky-500 transition-colors" />
              </div>
            </div>
            <div class="group/input">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 group-focus-within/input:text-sky-500 transition-colors">Entreprise</label>
              <div class="relative">
                <input type="text" v-model="form.company_name" placeholder="EX: GOOGLE" class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 pl-14 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none shadow-inner" />
                <BuildingOfficeIcon class="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within/input:text-sky-500 transition-colors" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Bio & Contact Block -->
      <section class="space-y-10">
        <!-- Bio Block -->
        <div class="wow-card rounded-[4rem] p-10 md:p-14 bg-white border-2 border-slate-50 space-y-8">
          <div class="flex items-center space-x-4">
            <PencilSquareIcon class="h-8 w-8 text-sky-500" />
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Bio-Data</h2>
          </div>
          <textarea v-model="form.bio" rows="6" placeholder="VOTRE PARCOURS EN QUELQUES MOTS..." class="hub-input w-full rounded-[2.5rem] bg-slate-50 border-2 border-slate-100 p-10 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none shadow-inner"></textarea>
          
          <div class="mt-6 flex items-center space-x-10">
            <!-- Preview HUB -->
            <div class="relative h-32 w-32 group/avatar">
              <div class="absolute inset-0 bg-gradient-to-tr from-sky-400 to-blue-600 rounded-[2.5rem] rotate-6 group-hover/avatar:rotate-12 transition-all"></div>
              <div class="absolute inset-1.5 bg-slate-900 rounded-[2rem] flex items-center justify-center overflow-hidden z-10 border-4 border-slate-950">
                <img v-if="previewUrl || getUserPhoto()" :src="previewUrl || getUserPhoto()" class="h-full w-full object-cover" />
                <UserCircleIcon v-else class="h-16 w-16 text-slate-700" />
              </div>
            </div>

            <div>
              <label for="profile_photo" class="cursor-pointer inline-flex items-center space-x-3 bg-slate-950 text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-sky-500 transition-all shadow-xl">
                 <CloudArrowUpIcon class="h-5 w-5" />
                 <span>SÉLECTIONNER PHOTO</span>
                 <input type="file" id="profile_photo" @change="onFileSelected" class="hidden" accept="image/*" />
              </label>
              <p class="mt-3 text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">MAX : 2MB (JPG, PNG)</p>
            </div>
          </div>
        </div>

        <!-- Links Block -->
        <div class="wow-card rounded-[4rem] p-10 md:p-14 bg-white border-2 border-slate-50 space-y-8">
          <div class="flex items-center space-x-4">
            <LinkIcon class="h-8 w-8 text-sky-500" />
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Matrice Réseau</h2>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
            <div class="group/input">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 group-focus-within/input:text-sky-500 transition-colors">Ville / Hub</label>
              <div class="relative">
                <input type="text" v-model="form.city" placeholder="EX: DAKAR, SN" class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 pl-14 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none shadow-inner" />
                <MapPinIcon class="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within/input:text-sky-500 transition-colors" />
              </div>
            </div>
            <div class="group/input">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 group-focus-within/input:text-sky-500 transition-colors">Matrice LinkedIn (URL)</label>
              <div class="relative">
                <input type="text" v-model="form.linkedin_url" placeholder="LINKEDIN.COM/IN/..." class="hub-input w-full rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 p-6 pl-14 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none shadow-inner" />
                <LinkIcon class="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within/input:text-sky-500 transition-colors" />
              </div>
            </div>
          </div>
        </div>
        
        <!-- Status Messages -->
        <Transition name="fade">
          <div v-if="successMessage" class="rounded-[2.5rem] bg-green-500 p-6 flex items-center space-x-4 shadow-2xl shadow-green-500/20 border-b-4 border-green-700">
            <CheckCircleIcon class="h-10 w-10 text-white shrink-0" />
            <div>
              <p class="text-[10px] font-black text-white/70 uppercase tracking-[0.3em]">SYSTÈME OPÉRATIONNEL</p>
              <p class="text-sm font-black text-white uppercase tracking-widest">{{ successMessage }}</p>
            </div>
          </div>
        </Transition>
        
        <Transition name="fade">
          <div v-if="errorMessage" class="rounded-[2.5rem] bg-red-500 p-6 flex items-center space-x-4 shadow-2xl shadow-red-500/20 border-b-4 border-red-700">
            <ExclamationCircleIcon class="h-10 w-10 text-white shrink-0" />
            <div>
              <p class="text-[10px] font-black text-white/70 uppercase tracking-[0.3em]">ALERTE SYSTÈME</p>
              <p class="text-sm font-black text-white uppercase tracking-widest">{{ errorMessage }}</p>
            </div>
          </div>
        </Transition>

        <!-- Final Action -->
        <button 
          type="submit" 
          :disabled="isLoading"
          class="w-full flex items-center justify-center rounded-[2.5rem] bg-slate-950 px-10 py-8 text-white shadow-2xl transition-all duration-500 hover:bg-sky-500 hover:-translate-y-2 active:scale-95 disabled:opacity-50 overflow-hidden relative group/save"
        >
          <div class="relative z-10 flex items-center">
            <CloudArrowUpIcon v-if="!isLoading" class="h-6 w-6 mr-4 transition-transform group-hover/save:scale-125" />
            <div v-else class="h-6 w-6 border-4 border-white/20 border-t-white rounded-full animate-spin mr-4"></div>
            <span class="text-[11px] font-black uppercase tracking-[0.4em]">{{ isLoading ? 'SYNCHRONISATION...' : 'ENREGISTRER LA CONFIGURATION' }}</span>
          </div>
          <!-- Internal Shine -->
          <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover/save:animate-shine transition-transform"></div>
        </button>
      </section>
    </form>

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

@keyframes shine {
  from { transform: translateX(-100%); }
  to { transform: translateX(300%); }
}
.animate-shine { animation: shine 1s ease-in-out infinite; }

.fade-enter-active, .fade-leave-active { transition: all 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
</style>