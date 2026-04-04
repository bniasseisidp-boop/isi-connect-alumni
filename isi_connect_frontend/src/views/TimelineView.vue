<script setup>
import { ref, onMounted } from 'vue'
import apiClient, { STORAGE_URL } from '../api'
import { useAuth } from '../auth'
import { 
  PaperAirplaneIcon, 
  PhotoIcon, 
  ChatBubbleLeftRightIcon, 
  HandThumbUpIcon,
  TrashIcon,
  SparklesIcon,
  UserCircleIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'
import { HandThumbUpIcon as HandThumbUpSolidIcon } from '@heroicons/vue/24/solid'

const auth = useAuth()
const posts = ref([])
const isLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref(null)

const newPost = ref({
  body: '',
  image: null
})
const previewUrl = ref(null)

const fetchPosts = async () => {
  isLoading.value = true
  try {
    const response = await apiClient.get('/posts')
    posts.value = response.data.data
  } catch (error) {
    console.error("ERREUR CHARGEMENT TIMELINE:", error)
  } finally {
    isLoading.value = false
  }
}

const onFileSelected = (event) => {
  const file = event.target.files[0]
  if (file) {
    newPost.value.image = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

const handleCreatePost = async () => {
  if (!newPost.value.body && !newPost.value.image) return
  isSubmitting.value = true
  errorMessage.value = null
  try {
    const formData = new FormData()
    formData.append('body', newPost.value.body || '')
    if (newPost.value.image) {
      formData.append('image', newPost.value.image)
    }

    await apiClient.post('/posts', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    newPost.value = { body: '', image: null }
    previewUrl.value = null
    fetchPosts()
  } catch (error) {
    console.error("ERREUR PUBLICATION:", error)
    errorMessage.value = "DÉFAILLANCE PROTOCOLE : L'ÉMISSION DU SIGNAL A ÉCHOUÉ."
  } finally {
    isSubmitting.value = false
  }
}

const toggleLike = async (post) => {
  try {
    const response = await apiClient.post(`/posts/${post.id}/like`)
    // On met à jour localement pour la réactivité instantanée
    if (response.data.liked) {
      post.likes_count++
      post.is_liked = true
    } else {
      post.likes_count--
      post.is_liked = false
    }
  } catch (error) {
    console.error("ERREUR LIKE:", error)
  }
}

const handleComment = async (post, commentBody) => {
  if (!commentBody.trim()) return
  try {
    const response = await apiClient.post(`/posts/${post.id}/comment`, { body: commentBody })
    post.comments.push(response.data.comment)
  } catch (error) {
    console.error("ERREUR COMMENTAIRE:", error)
  }
}

const deletePost = async (postId) => {
  if (!confirm("Supprimer cette publication ?")) return
  try {
    await apiClient.delete(`/posts/${postId}`)
    posts.value = posts.value.filter(p => p.id !== postId)
  } catch (error) {
    console.error("ERREUR SUPPRESSION:", error)
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
  }).toUpperCase()
}

const getUserPhoto = (user) => {
  const photo = user?.profile?.profile_picture_url;
  if (!photo) return null;
  return photo.startsWith('http') ? photo : (STORAGE_URL + photo);
}

onMounted(fetchPosts)

// Gestion locale des inputs de commentaires par post
const commentInputs = ref({})
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-12 animate-page-reveal pb-20">
    
    <!-- Header Hero -->
    <div class="wow-card rounded-[3.5rem] p-12 bg-slate-950 text-white border-b-8 border-sky-500 relative overflow-hidden group shadow-2xl">
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#0ea5e9_1px,transparent_1px)] bg-[size:20px_20px]"></div>
      <div class="relative z-10">
        <div class="flex items-center space-x-3 mb-6 bg-sky-500/20 w-fit px-5 py-2 rounded-full border border-sky-400/30">
          <SparklesIcon class="h-3 w-3 text-sky-400 animate-pulse" />
          <span class="text-[9px] font-black uppercase tracking-[0.4em] text-sky-300">SOCIAL HUB DYNAMIQUE</span>
        </div>
        <h1 class="text-5xl md:text-7xl font-black tracking-tighter leading-none mb-4">
          Fil <span class="text-sky-400 text-glow">Actu</span>
        </h1>
        <p class="text-slate-400 font-medium text-lg leading-relaxed opacity-80 uppercase tracking-widest">DÉPLOYEZ VOS IDÉES, CONNECTEZ L'INTELLIGENCE.</p>
      </div>
    </div>

    <!-- Post Creation Matrix -->
    <div class="wow-card rounded-[3rem] p-10 bg-white border-4 border-slate-50 shadow-xl shadow-slate-200/50 relative overflow-hidden">
      <div class="flex gap-6">
        <div class="h-16 w-16 bg-slate-900 rounded-2xl flex items-center justify-center shrink-0 overflow-hidden border-2 border-slate-950 shadow-lg">
           <img v-if="getUserPhoto(auth.user.value)" :src="getUserPhoto(auth.user.value)" class="h-full w-full object-cover" />
           <UserCircleIcon v-else class="h-8 w-8 text-slate-700" />
        </div>
        <div class="flex-1 space-y-6">
          <div v-if="errorMessage" class="p-5 bg-red-50 border-2 border-red-200 rounded-2xl text-red-500 text-[10px] font-black uppercase tracking-widest animate-pulse">
             {{ errorMessage }}
          </div>
          <textarea 
            v-model="newPost.body"
            rows="3"
            class="w-full bg-slate-50 rounded-[2rem] p-8 text-lg font-black placeholder-slate-300 border-2 border-transparent focus:border-sky-500 focus:bg-white transition-all outline-none resize-none shadow-inner"
            placeholder="QUOI DE NEUF DANS LE RÉSEAU ?"
          ></textarea>

          <!-- Local Preview -->
          <div v-if="previewUrl" class="relative rounded-[2.5rem] overflow-hidden border-4 border-sky-50 shadow-2xl group/preview max-h-96">
             <img :src="previewUrl" class="w-full object-contain bg-slate-900" />
             <button @click="previewUrl = null; newPost.image = null" class="absolute top-4 right-4 h-12 w-12 bg-red-500 text-white rounded-2xl flex items-center justify-center shadow-xl hover:scale-110 transition-transform">
                <TrashIcon class="h-6 w-6" />
             </button>
          </div>

          <div class="flex items-center justify-between">
            <label for="post-img" class="flex items-center space-x-3 px-6 py-4 rounded-2xl bg-slate-100 text-slate-500 hover:bg-sky-100 hover:text-sky-600 transition-all cursor-pointer font-black text-[10px] uppercase tracking-widest">
               <PhotoIcon class="h-5 w-5" />
               <span>AJOUTER MÉDIA</span>
               <input type="file" id="post-img" @change="onFileSelected" class="hidden" accept="image/*" />
            </label>
            
            <button 
              @click="handleCreatePost"
              :disabled="isSubmitting || (!newPost.body && !newPost.image)"
              class="flex items-center space-x-4 bg-slate-950 text-white px-10 py-5 rounded-[1.8rem] font-black text-[10px] uppercase tracking-widest shadow-2xl hover:bg-sky-500 hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-50"
            >
              <PaperAirplaneIcon v-if="!isSubmitting" class="h-5 w-5" />
              <div v-else class="h-5 w-5 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
              <span>PUBLIER</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Timeline Stream -->
    <div v-if="isLoading" class="flex flex-col items-center py-20">
       <div class="h-20 w-20 border-4 border-sky-100 border-t-sky-500 rounded-full animate-spin"></div>
       <p class="mt-8 text-[10px] font-black uppercase tracking-[0.5em] text-sky-600 animate-pulse">DÉCRYPTAGE DU FLUX SOCIAL...</p>
    </div>

    <div v-else class="space-y-12">
      <div v-for="post in posts" :key="post.id" class="wow-card rounded-[4rem] bg-white border-2 border-slate-50 shadow-2xl shadow-slate-200/30 overflow-hidden transition-all duration-700 hover:shadow-sky-500/[0.05]">
        
        <!-- Post Header -->
        <div class="p-10 flex items-center justify-between border-b border-slate-50 bg-slate-50/30">
          <div class="flex items-center space-x-6">
            <div class="h-16 w-16 bg-slate-950 rounded-2xl overflow-hidden border-2 border-slate-950 shadow-lg">
                <img v-if="getUserPhoto(post.user)" :src="getUserPhoto(post.user)" class="h-full w-full object-cover" />
                <UserCircleIcon v-else class="h-8 w-8 text-slate-700" />
            </div>
            <div>
              <p class="text-sm font-black text-slate-900 uppercase tracking-widest">{{ post.user.name }}</p>
              <div class="flex items-center text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-widest">
                <ClockIcon class="h-3 w-3 mr-2" />
                {{ formatDate(post.created_at) }}
              </div>
            </div>
          </div>
          
          <button v-if="auth.user.value?.id === post.user_id" @click="deletePost(post.id)" class="p-4 rounded-2xl bg-red-50 text-red-400 hover:bg-red-500 hover:text-white transition-all active:scale-90 shadow-sm">
             <TrashIcon class="h-5 w-5" />
          </button>
        </div>

        <!-- Post Content -->
        <div class="p-10 md:p-14 space-y-10">
          <p class="text-xl font-medium text-slate-700 leading-relaxed whitespace-pre-wrap">
            {{ post.body }}
          </p>
          
          <div v-if="post.image_path" class="rounded-[3rem] overflow-hidden border-4 border-slate-50 shadow-2xl">
             <img :src="STORAGE_URL + post.image_path" class="w-full object-contain max-h-[600px] bg-slate-900" />
          </div>
        </div>

        <!-- Post Actions HUB -->
        <div class="px-10 py-8 bg-slate-50/50 flex items-center space-x-12 border-t border-slate-50">
          <button 
            @click="toggleLike(post)"
            class="group flex items-center space-x-3 text-slate-400 hover:text-sky-500 transition-all font-black text-[10px] uppercase tracking-widest"
          >
            <div class="h-14 w-14 rounded-2xl bg-white border-2 border-slate-100 flex items-center justify-center transition-all group-hover:scale-110 shadow-sm group-active:scale-95" :class="post.is_liked ? 'bg-sky-500 border-sky-400 text-white' : ''">
               <component :is="post.is_liked ? HandThumbUpSolidIcon : HandThumbUpIcon" class="h-6 w-6" />
            </div>
            <span>{{ post.likes_count || 0 }} Likes</span>
          </button>

          <div class="flex items-center space-x-3 text-slate-400 font-black text-[10px] uppercase tracking-widest">
             <div class="h-14 w-14 rounded-2xl bg-white border-2 border-slate-100 flex items-center justify-center shadow-sm">
                <ChatBubbleLeftRightIcon class="h-6 w-6" />
             </div>
             <span>{{ post.comments.length }} Commentaires</span>
          </div>
        </div>

        <!-- Comments Section Matrix -->
        <div class="p-10 bg-white border-t border-slate-50 space-y-8">
          <div v-for="comment in post.comments" :key="comment.id" class="flex gap-4 group/comment">
            <div class="h-10 w-10 bg-slate-100 rounded-xl overflow-hidden shrink-0 border border-slate-200 shadow-sm">
                <img v-if="getUserPhoto(comment.user)" :src="getUserPhoto(comment.user)" class="h-full w-full object-cover" />
                <UserCircleIcon v-else class="h-5 w-5 text-slate-400 m-2" />
            </div>
            <div class="flex-1 bg-slate-50 rounded-2xl p-6 transition-all group-hover/comment:bg-slate-100 shadow-sm">
              <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-black text-slate-800 uppercase tracking-widest">{{ comment.user.name }}</span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ formatDate(comment.created_at) }}</span>
              </div>
              <p class="text-sm font-medium text-slate-600">{{ comment.body }}</p>
            </div>
          </div>

          <!-- Add Comment Input -->
          <div class="flex gap-4 mt-10">
            <div class="h-12 w-12 bg-slate-900 rounded-xl flex items-center justify-center shrink-0 overflow-hidden shadow-lg">
                <img v-if="getUserPhoto(auth.user.value)" :src="getUserPhoto(auth.user.value)" class="h-full w-full object-cover" />
                <UserCircleIcon v-else class="h-6 w-6 text-slate-700" />
            </div>
            <div class="flex-1 relative">
              <input 
                v-model="commentInputs[post.id]"
                @keyup.enter="handleComment(post, commentInputs[post.id]); commentInputs[post.id] = ''"
                type="text" 
                class="w-full bg-slate-50 rounded-2xl p-5 pr-14 text-sm font-bold border-2 border-transparent focus:border-sky-500 focus:bg-white transition-all outline-none truncate shadow-inner"
                placeholder="RÉPONDRE À LA MATRICE..."
              />
              <button @click="handleComment(post, commentInputs[post.id]); commentInputs[post.id] = ''" class="absolute right-4 top-1/2 -translate-y-1/2 text-sky-500 hover:scale-125 transition-transform">
                <PaperAirplaneIcon class="h-6 w-6" />
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="posts.length === 0" class="py-32 text-center wow-card rounded-[4rem] border-4 border-dashed border-slate-100">
        <SparklesIcon class="h-16 w-16 mx-auto mb-8 text-sky-200/50" />
        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-slate-300">PROTOCOLE SOCIAL VIDE</h3>
        <p class="mt-4 text-slate-400 text-[10px] font-bold uppercase tracking-widest">SOYEZ LE PREMIER À ÉMETTRE UN SIGNAL.</p>
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

.text-glow {
  text-shadow: 0 0 30px rgba(14, 165, 233, 0.5);
}
</style>
