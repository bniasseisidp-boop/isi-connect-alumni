<script setup>
import { ref, onMounted } from 'vue'
import apiClient, { STORAGE_URL } from '../api'
import { useAuth } from '../auth'
import { 
  UserGroupIcon, 
  PlusIcon, 
  ArrowRightCircleIcon,
  ChatBubbleBottomCenterTextIcon,
  TrashIcon,
  ShieldCheckIcon,
  CloudArrowUpIcon
} from '@heroicons/vue/24/outline'

const auth = useAuth()
const groups = ref([])
const isLoading = ref(true)
const showModal = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref(null)

// Member management
const showMemberModal = ref(false)
const selectedGroup = ref(null)
const searchQuery = ref('')
const searchResults = ref([])
const isSearching = ref(false)
const isAddingMember = ref(false)

const newGroupForm = ref({
  name: '',
  description: '',
  image: null
})
const previewUrl = ref(null)

const fetchGroups = async () => {
  isLoading.value = true
  try {
    const response = await apiClient.get('/work-groups')
    groups.value = response.data
  } catch (error) {
    console.error("ERREUR CHARGEMENT GROUPES:", error)
  } finally {
    isLoading.value = false
  }
}

const onFileSelected = (event) => {
  const file = event.target.files[0]
  if (file) {
    newGroupForm.value.image = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

const handleCreateGroup = async () => {
  if (!newGroupForm.value.name || !newGroupForm.value.description) {
    errorMessage.value = "CHAMPS NOM ET MISSION OBLIGATOIRES."
    return
  }
  
  isSubmitting.value = true
  errorMessage.value = null
  try {
    const formData = new FormData()
    formData.append('name', newGroupForm.value.name)
    formData.append('description', newGroupForm.value.description)
    if (newGroupForm.value.image) {
      formData.append('image', newGroupForm.value.image)
    }

    await apiClient.post('/work-groups', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    showModal.value = false
    newGroupForm.value = { name: '', description: '', image: null }
    previewUrl.value = null
    fetchGroups()
  } catch (error) {
    console.error("ERREUR CRÉATION GROUPE:", error)
    errorMessage.value = "ÉCHEC DU DÉPLOIEMENT : VÉRIFIEZ VOS DONNÉES."
  } finally {
    isSubmitting.value = false
  }
}

const joinGroup = async (group) => {
  try {
    await apiClient.post(`/work-groups/${group.id}/join`)
    fetchGroups()
  } catch (error) {
    console.error("ERREUR JOIN:", error)
  }
}

const leaveGroup = async (group) => {
  if (!confirm("Quitter ce groupe de travail ?")) return
  try {
    await apiClient.post(`/work-groups/${group.id}/leave`)
    fetchGroups()
  } catch (error) {
    console.error("ERREUR LEAVE:", error)
  }
}

const deleteGroup = async (groupId) => {
  if (!confirm("Supprimer définitivement ce hub de travail ?")) return
  try {
    await apiClient.delete(`/work-groups/${groupId}`)
    fetchGroups()
  } catch (error) {
    console.error("ERREUR SUPPRESSION:", error)
  }
}

const isMember = (group) => {
  return group.members.some(m => m.user_id === auth.user.value?.id)
}

const isCreator = (group) => {
  return group.creator_id === auth.user.value?.id
}

const openMemberModal = (group) => {
  selectedGroup.value = group
  searchQuery.value = ''
  searchResults.value = []
  showMemberModal.value = true
}

const searchUsers = async () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }
  isSearching.value = true
  try {
    const response = await apiClient.get(`/users/search?query=${searchQuery.value}`)
    searchResults.value = response.data
  } catch (error) {
    console.error("ERREUR RECHERCHE:", error)
  } finally {
    isSearching.value = false
  }
}

const addMember = async (userId) => {
  isAddingMember.value = true
  try {
    await apiClient.post(`/work-groups/${selectedGroup.value.id}/members`, { user_id: userId })
    alert("MEMBRE AJOUTÉ AVEC SUCCÈS À LA MATRICE.")
    fetchGroups()
    // Refresh selected group data if needed
    const updatedGroup = groups.value.find(g => g.id === selectedGroup.value.id)
    if (updatedGroup) selectedGroup.value = updatedGroup
  } catch (error) {
    console.error("ERREUR ADD MEMBER:", error)
    alert(error.response?.data?.message || "ÉCHEC DE L'AJOUT.")
  } finally {
    isAddingMember.value = false
  }
}

onMounted(fetchGroups)
</script>

<template>
  <div class="space-y-12 animate-page-reveal pb-20">
    
    <!-- Premium Header HUB -->
    <div class="wow-card rounded-[3.5rem] p-12 bg-slate-950 text-white border-b-8 border-sky-500 relative overflow-hidden group shadow-2xl">
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] bg-[size:15px_15px]"></div>
      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div>
           <div class="flex items-center space-x-3 mb-6 bg-sky-500/10 w-fit px-5 py-2 rounded-full border border-sky-400/30">
            <UserGroupIcon class="h-4 w-4 text-sky-400 animate-pulse" />
            <span class="text-[9px] font-black uppercase tracking-[0.4rem] text-sky-300">HUB COLLABORATIF ALPHA</span>
          </div>
          <h1 class="text-5xl md:text-7xl font-black tracking-tighter leading-none mb-4 uppercase">
            Groupes de <span class="text-sky-400 text-glow">Travail</span>
          </h1>
          <p class="text-slate-400 font-medium text-lg opacity-80 uppercase tracking-widest max-w-xl">
            L'INTELLIGENCE COLLECTIVE EN ACTION. CRÉEZ OU REJOIGNEZ LA MATRICE.
          </p>
        </div>
        
        <button 
          @click="showModal = true"
          class="group/btn relative flex items-center space-x-6 bg-white text-slate-950 px-10 py-6 rounded-[2.5rem] font-black text-[11px] uppercase tracking-[0.3em] shadow-2xl hover:bg-sky-500 hover:text-white transition-all duration-500 hover:-translate-y-2"
        >
          <div class="h-10 w-10 bg-slate-950 text-white rounded-full flex items-center justify-center group-hover/btn:bg-white group-hover/btn:text-sky-500 transition-colors">
            <PlusIcon class="h-6 w-6" />
          </div>
          <span>CRÉER UN HUB</span>
        </button>
      </div>
    </div>

    <!-- Groups Grid Matrix -->
    <div v-if="isLoading" class="flex flex-col items-center py-32">
       <div class="h-24 w-24 border-8 border-sky-100 border-t-sky-500 rounded-full animate-spin"></div>
       <p class="mt-8 text-[11px] font-black uppercase tracking-[0.5em] text-sky-600 animate-pulse">SYNCHRONISATION DES HUBS...</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
      <div 
        v-for="group in groups" 
        :key="group.id"
        class="wow-card rounded-[3.5rem] bg-white border-2 border-slate-50 flex flex-col overflow-hidden transition-all duration-700 hover:shadow-2xl hover:shadow-sky-500/[0.05] hover:border-sky-100 group/card"
      >
        <!-- Group Visual -->
        <div class="h-56 relative overflow-hidden bg-slate-900">
           <img 
            v-if="group.image_path" 
            :src="STORAGE_URL + group.image_path" 
            class="h-full w-full object-cover opacity-60 group-hover/card:scale-110 transition-transform duration-1000" 
           />
           <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-950">
             <UserGroupIcon class="h-20 w-20 text-slate-700 opacity-20" />
           </div>
           
           <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-80"></div>
           
           <!-- Member Chips -->
           <div class="absolute bottom-6 left-8 flex -space-x-4">
              <div v-for="(member, idx) in group.members.slice(0, 4)" :key="idx" class="h-10 w-10 rounded-xl border-2 border-slate-950 bg-slate-800 overflow-hidden shadow-lg">
                 <img v-if="member.user.profile?.profile_picture_url" :src="STORAGE_URL + member.user.profile.profile_picture_url" class="h-full w-full object-cover" />
                 <div v-else class="h-full w-full flex items-center justify-center text-[10px] font-black text-white bg-sky-500">{{ member.user.name.charAt(0) }}</div>
              </div>
              <div v-if="group.members_count > 4" class="h-10 w-10 rounded-xl border-2 border-slate-950 bg-slate-900 flex items-center justify-center text-[9px] font-black text-sky-400 z-10">
                 +{{ group.members_count - 4 }}
              </div>
           </div>
        </div>

        <!-- Group Body -->
        <div class="p-10 flex-1 flex flex-col">
          <div class="flex items-center justify-between mb-6">
             <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight group-hover/card:text-sky-600 transition-colors">{{ group.name }}</h3>
             <div v-if="isCreator(group)" class="h-8 w-8 bg-sky-500/10 rounded-lg flex items-center justify-center text-sky-500" title="ADMINISTRATEUR">
                <ShieldCheckIcon class="h-5 w-5" />
             </div>
          </div>
          
          <p class="text-slate-500 text-sm font-medium leading-relaxed mb-10 line-clamp-3">
            {{ group.description }}
          </p>

          <div class="mt-auto pt-8 border-t border-slate-50 flex items-center justify-between">
            <div class="space-y-1">
               <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">CRÉÉ PAR</p>
               <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">{{ group.creator.name }}</p>
            </div>
            
            <div class="flex items-center space-x-3">
              <button 
                v-if="isCreator(group)"
                @click="deleteGroup(group.id)"
                class="p-4 rounded-2xl bg-red-50 text-red-400 hover:bg-red-500 hover:text-white transition-all shadow-sm active:scale-90"
              >
                <TrashIcon class="h-5 w-5" />
              </button>

              <button 
                v-if="isCreator(group)"
                @click="openMemberModal(group)"
                class="flex items-center space-x-2 px-6 py-4 rounded-2xl bg-sky-500 text-white font-black text-[9px] uppercase tracking-widest hover:bg-sky-600 transition-all shadow-lg shadow-sky-500/20 active:scale-95"
              >
                <PlusIcon class="h-4 w-4" />
                <span>+ MEMBRE</span>
              </button>

              <button 
                v-else-if="isMember(group)"
                @click="leaveGroup(group)"
                class="px-8 py-4 rounded-2xl bg-slate-100 text-slate-500 font-black text-[9px] uppercase tracking-widest hover:bg-red-50 hover:text-red-500 transition-all shadow-sm"
              >
                QUITTER
              </button>

              <button 
                v-else
                @click="joinGroup(group)"
                class="flex items-center space-x-3 px-8 py-4 rounded-2xl bg-slate-950 text-white font-black text-[9px] uppercase tracking-[0.2em] hover:bg-sky-500 transition-all shadow-xl hover:-translate-y-1 active:scale-95"
              >
                <span>REJOINDRE</span>
                <ArrowRightCircleIcon class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="groups.length === 0" class="col-span-full py-32 text-center wow-card rounded-[4rem] border-4 border-dashed border-slate-100">
        <ChatBubbleBottomCenterTextIcon class="h-20 w-20 mx-auto mb-8 text-sky-200/50" />
        <h3 class="text-[12px] font-black uppercase tracking-[0.6em] text-slate-300 italic">AUCUNE ENTITÉ COLLABORATIVE TROUVÉE</h3>
        <p class="mt-4 text-slate-400 text-[10px] font-bold uppercase tracking-widest">INSPIRATION NÉCESSAIRE. DÉPLOYEZ LE PREMIER HUB.</p>
      </div>
    </div>

    <!-- Create Group Modal Matrix -->
    <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-2xl animate-fade-in">
      <div class="wow-card w-full max-w-2xl bg-white rounded-[4rem] overflow-hidden relative shadow-[0_0_100px_rgba(14,165,233,0.3)] border-2 border-white/20">
        
        <div class="p-10 md:p-14 bg-slate-950 text-white relative">
          <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
          <div class="relative flex justify-between items-center">
            <h2 class="text-4xl font-black tracking-tighter uppercase">Nouveau <span class="text-sky-400">Hub</span></h2>
            <button @click="showModal = false" class="h-14 w-14 rounded-2xl bg-white/10 flex items-center justify-center hover:bg-red-500 transition-all font-black group/close">
               <span class="text-2xl group-hover/close:rotate-90 transition-transform">×</span>
            </button>
          </div>
        </div>

        <div class="p-10 md:p-14 space-y-10">
          <div v-if="errorMessage" class="p-5 bg-red-50 border-2 border-red-200 rounded-2xl text-red-500 text-[10px] font-black uppercase tracking-widest animate-pulse text-center">
             {{ errorMessage }}
          </div>
          <div class="space-y-3">
             <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-3">Dénomination du Hub</label>
             <input v-model="newGroupForm.name" type="text" placeholder="EX: RECHERCHE IA ALPHA..." class="hub-input w-full rounded-[2.5rem] bg-slate-50 border-2 border-slate-100 p-8 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none shadow-inner" />
          </div>

          <div class="space-y-3">
             <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-3">Mission & Objectifs</label>
             <textarea v-model="newGroupForm.description" rows="4" placeholder="DÉCRIVEZ LA FINALITÉ DE CETTE ENTITÉ..." class="hub-input w-full rounded-[2.5rem] bg-slate-50 border-2 border-slate-100 p-8 text-sm font-black transition-all focus:border-sky-500 focus:bg-white outline-none shadow-inner"></textarea>
          </div>

          <div class="space-y-4">
             <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-3">Identité Visuelle (Optionnel)</label>
             <div class="flex items-center space-x-8">
                <div v-if="previewUrl" class="h-24 w-40 rounded-2xl overflow-hidden border-2 border-sky-400 shadow-xl shrink-0">
                  <img :src="previewUrl" class="w-full h-full object-cover" />
                </div>
                <label for="group_img" class="flex-1 cursor-pointer flex flex-col items-center justify-center border-4 border-dashed border-slate-100 hover:border-sky-300 rounded-[2.5rem] p-10 transition-colors group/upload bg-slate-50/50">
                   <CloudArrowUpIcon class="h-8 w-8 text-slate-300 group-hover/upload:text-sky-500 transition-colors mb-3" />
                   <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">SÉLECTIONNEZ UN VISUEL</span>
                   <input type="file" id="group_img" @change="onFileSelected" class="hidden" accept="image/*" />
                </label>
             </div>
          </div>

          <button 
            @click="handleCreateGroup"
            :disabled="isSubmitting || !newGroupForm.name"
            class="w-full bg-slate-950 text-white py-8 rounded-[2.5rem] font-black text-[11px] title-glow uppercase tracking-[0.4em] shadow-2xl hover:bg-sky-500 hover:-translate-y-2 transition-all active:scale-95 disabled:opacity-50"
          >
            <span v-if="!isSubmitting">DÉPLOYER LE HUB MAINTENANT</span>
            <div v-else class="h-6 w-6 border-2 border-white/20 border-t-white rounded-full animate-spin mx-auto"></div>
          </button>
        </div>
      </div>
    </div>

    <!-- Management Modal ALPHA -->
    <div v-if="showMemberModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-2xl animate-fade-in">
       <div class="wow-card w-full max-w-xl bg-white rounded-[3.5rem] overflow-hidden shadow-2xl border-2 border-white/20">
          <div class="p-10 bg-slate-950 text-white flex justify-between items-center">
             <div>
                <h2 class="text-2xl font-black uppercase tracking-tighter">Gestion du <span class="text-sky-400">Hub</span></h2>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ selectedGroup?.name }}</p>
             </div>
             <button @click="showMemberModal = false" class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center hover:bg-red-500 transition-all">
                <span class="text-xl">×</span>
             </button>
          </div>

          <div class="p-10 space-y-8">
             <div class="space-y-4">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rechercher un candidat</label>
                <div class="relative">
                   <input 
                    v-model="searchQuery" 
                    @input="searchUsers"
                    type="text" 
                    placeholder="NOM OU EMAIL..." 
                    class="w-full rounded-2xl bg-slate-50 border-2 border-slate-100 p-5 text-xs font-black outline-none focus:border-sky-500 transition-all"
                   />
                   <div v-if="isSearching" class="absolute right-4 top-4 h-5 w-5 border-2 border-sky-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
             </div>

             <div v-if="searchResults.length > 0" class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                <div v-for="user in searchResults" :key="user.id" class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 group/user">
                   <div class="flex items-center space-x-3">
                      <div class="h-10 w-10 rounded-xl bg-sky-100 flex items-center justify-center text-sky-600 font-black text-xs overflow-hidden">
                         <img v-if="user.profile?.profile_picture_url" :src="STORAGE_URL + user.profile.profile_picture_url" class="h-full w-full object-cover" />
                         <span v-else>{{ user.name.charAt(0) }}</span>
                      </div>
                      <div>
                         <p class="text-[11px] font-black text-slate-900 uppercase tracking-tight">{{ user.name }}</p>
                         <p class="text-[9px] font-medium text-slate-400">{{ user.email }}</p>
                      </div>
                   </div>
                   <button 
                    @click="addMember(user.id)"
                    :disabled="isAddingMember || selectedGroup.members.some(m => m.user_id === user.id)"
                    class="p-3 rounded-xl bg-white text-slate-950 border border-slate-200 hover:bg-sky-500 hover:text-white hover:border-sky-500 transition-all disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-slate-300"
                   >
                    <PlusIcon class="h-4 w-4" />
                   </button>
                </div>
             </div>
             
             <div v-else-if="searchQuery.length >= 2 && !isSearching" class="text-center py-6">
                <p class="text-[10px] font-black text-slate-300 uppercase italic tracking-widest">AUCUN CANDIDAT DÉTECTÉ</p>
             </div>

             <div class="pt-6 border-t border-slate-100">
                <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-4">MEMBRES ACTUELS ({{ selectedGroup?.members.length }})</h4>
                <div class="flex flex-wrap gap-2">
                   <div v-for="member in selectedGroup?.members" :key="member.id" class="px-3 py-2 bg-slate-100 rounded-lg text-[9px] font-black text-slate-600 uppercase">
                      {{ member.user.name }}
                   </div>
                </div>
             </div>
          </div>
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

.animate-fade-in { animation: fadeIn 0.4s ease forwards; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.text-glow {
  text-shadow: 0 0 30px rgba(14, 165, 233, 0.6);
}
.title-glow:hover {
  box-shadow: 0 20px 50px rgba(14, 165, 233, 0.3);
}
</style>
