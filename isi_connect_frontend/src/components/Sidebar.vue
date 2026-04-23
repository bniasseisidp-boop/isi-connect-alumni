<template>
  <div class="flex flex-col w-60 h-screen p-4 relative z-20" style="background: var(--card-bg); border-right: 2px solid var(--card-border); color: var(--text-primary); box-shadow: 10px 0 40px rgba(0,0,0,0.04);">

    <!-- Tech Logo Section -->
    <div class="mb-6 relative group text-center">

      <div class="flex items-center justify-center p-3 rounded-2xl bg-white border-2 border-sky-50 shadow-lg shadow-sky-500/5 transition-all duration-700 group-hover:rotate-2 group-hover:scale-105 active:scale-95 cursor-pointer">
        <img 
          src="../assets/images/isi.png" 
          alt="Logo ISI" 
          class="h-8 w-auto" 
        />
      </div>

      <!-- Digital under-glow -->
      <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-1/2 h-1 bg-sky-400/20 blur-md rounded-full"></div>
    </div>

    <!-- Navigation Hub (Matrix Style) -->
    <nav class="flex-1 space-y-1 overflow-y-auto pr-1 custom-scrollbar">
      <RouterLink
        v-for="(item, index) in menuItems"
        :key="item.to"
        :to="item.to"
        class="group relative flex items-center px-3 py-3 rounded-[1.2rem] transition-all duration-500 border-2 border-transparent nav-item"
        active-class="active-hub"
        @mouseenter="hoverIdx = index"
        @mouseleave="hoverIdx = null"
      >
        <!-- Icon Hub -->
        <div class="relative z-10 p-2.5 rounded-xl nav-icon-bg transition-all duration-500 group-hover:bg-[var(--accent)] group-[.active-hub]:bg-[var(--accent)] shadow-sm group-hover:shadow-lg">
          <component
            :is="item.icon"
            class="h-5 w-5 transition-all duration-500"
            :class="[
              hoverIdx === index ? 'text-white scale-110' : '',
              'group-[.active-hub]:text-white'
            ]"
            :style="hoverIdx !== index ? { color: 'var(--text-secondary)' } : {}"
          />
        </div>

        <div class="ml-4 flex-1 relative z-10 transition-all duration-500 group-hover:translate-x-2">
          <span class="text-[10px] font-black tracking-[0.2em] uppercase transition-colors duration-500"
                :style="hoverIdx === index ? { color: 'var(--accent)' } : { color: 'var(--text-secondary)' }"
                :class="'group-[.active-hub]:!text-[var(--accent)]'">
            {{ item.label }}
          </span>
        </div>

        <!-- Digital Indicator -->
        <div class="absolute right-3 w-1 h-1 rounded-full opacity-0 group-hover:opacity-100 group-[.active-hub]:opacity-100 group-[.active-hub]:scale-150 transition-all duration-700"
             :style="{ background: 'var(--accent)', boxShadow: '0 0 10px var(--accent)' }"></div>
      </RouterLink>
    </nav>

    <!-- User & Data Access -->
    <div class="mt-auto pt-4" style="border-top: 2px solid var(--card-border);">
      <div v-if="auth.user.value"
           class="flex items-center mb-4 px-3 py-3 rounded-[1.5rem] border-2 border-transparent transition-all duration-500 group/user cursor-help hover:border-[var(--accent)]/20"
           style="background: var(--bg-secondary);">
        <div class="relative h-9 w-9 rounded-xl flex items-center justify-center font-black text-white text-base shadow-2xl group-hover/user:rotate-6 transition-all duration-500 overflow-hidden"
             style="background: var(--accent);">
          <img v-if="getUserPhoto()" :src="getUserPhoto()" class="h-full w-full object-cover" />
          <span v-else>{{ auth.user.value.name.charAt(0) }}</span>
          <div class="absolute -top-1 -right-1 h-3 w-3 bg-green-500 border-2 rounded-full" style="border-color: var(--card-bg);"></div>
        </div>
        <div class="ml-4 truncate">
          <p class="text-[10px] font-black truncate uppercase tracking-tighter" style="color: var(--text-primary);">{{ auth.user.value.name }}</p>
          <p class="text-[8px] font-bold truncate tracking-[0.2em] uppercase mt-0.5" style="color: var(--accent);">SESSION ACTIVE</p>
        </div>
      </div>

      <!-- 3 Themes Selector -->
      <div class="px-3 mb-4">
        <p class="text-[7px] font-black uppercase tracking-[0.3em] mb-2 ml-1" style="color: var(--text-secondary);">THÈME</p>
        <div class="flex space-x-1.5">
          <button v-for="t in themes" :key="t.id"
            @click="setTheme(t.id)"
            :title="t.label"
            class="flex-1 h-10 rounded-xl flex items-center justify-center transition-all duration-300 border-2 text-xs"
            :class="theme === t.id ? 'scale-105' : 'hover:opacity-80'"
            :style="{
              background: t.bg,
              color: t.color,
              borderColor: theme === t.id ? 'var(--accent)' : 'transparent',
              boxShadow: theme === t.id ? '0 0 14px color-mix(in srgb, var(--accent) 40%, transparent)' : 'none'
            }"
          >
            <component :is="t.icon" class="h-4 w-4" />
          </button>
        </div>
      </div>

      <button
        @click="handleLogout"
        class="logout-btn group flex items-center justify-center w-full px-3 py-4 rounded-[1.2rem] transition-all duration-500 active:scale-95"
      >
        <ArrowRightOnRectangleIcon class="h-4 w-4 mr-2 transform transition-transform duration-500 group-hover:translate-x-2" />
        <span class="font-black text-[8px] uppercase tracking-[0.3em]">QUITTER LE HUB</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuth } from '../auth'
import { STORAGE_URL } from '../api'
import {
  Squares2X2Icon,
  UsersIcon,
  BriefcaseIcon,
  CalendarIcon,
  ChatBubbleLeftRightIcon,
  UserCircleIcon,
  UserGroupIcon,
  SparklesIcon,
  ArrowRightOnRectangleIcon,
  SunIcon,
  MoonIcon,
  StarIcon
} from '@heroicons/vue/24/outline'
import { theme, setTheme } from '../theme'

const themes = [
  { id: 'light',       label: 'Blanc',         bg: '#f8fafc', color: '#0ea5e9', icon: SunIcon    },
  { id: 'dark-blue',   label: 'Sombre Bleu',   bg: '#0f172a', color: '#38bdf8', icon: MoonIcon   },
  { id: 'dark-purple', label: 'Sombre Violet',  bg: '#0d0d1a', color: '#a78bfa', icon: StarIcon   },
]

const auth = useAuth()

const getUserPhoto = () => {
    const photo = auth.user.value?.profile?.profile_picture_url;
    if (!photo) return null;
    return photo.startsWith('http') ? photo : (STORAGE_URL + photo);
};
const router = useRouter()
const hoverIdx = ref(null)

const menuItems = [
  { to: '/dashboard', label: 'ACCUEIL', icon: Squares2X2Icon },
  { to: '/actualites', label: 'ACTUALITÉS', icon: SparklesIcon },
  { to: '/groupes', label: 'GROUPES', icon: UserGroupIcon },
  { to: '/annuaire', label: 'ANNUAIRE', icon: UsersIcon },
  { to: '/offres', label: 'EMPLOIS', icon: BriefcaseIcon },
  { to: '/evenements', label: 'AGENDA', icon: CalendarIcon },
  { to: '/forum', label: 'FORUM', icon: ChatBubbleLeftRightIcon },
  { to: '/profil', label: 'PROFIL', icon: UserCircleIcon },
]

const handleLogout = async () => {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<style scoped>
.active-hub {
  background: color-mix(in srgb, var(--accent) 8%, transparent) !important;
  border-color: color-mix(in srgb, var(--accent) 20%, transparent) !important;
  box-shadow: 0 10px 40px color-mix(in srgb, var(--accent) 10%, transparent);
}
.nav-item:hover {
  background: color-mix(in srgb, var(--accent) 6%, var(--bg-secondary));
  border-color: color-mix(in srgb, var(--accent) 15%, transparent) !important;
}
.nav-icon-bg {
  background: var(--bg-secondary);
}
.logout-btn {
  background: rgba(239, 68, 68, 0.08);
  color: #ef4444;
  border: 2px solid transparent;
}
.logout-btn:hover {
  background: #ef4444;
  color: white;
  border-color: rgba(239,68,68,0.3);
}
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--accent);
  opacity: 0.2;
  border-radius: 10px;
}
</style>