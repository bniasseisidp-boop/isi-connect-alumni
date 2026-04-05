<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
const isMenuOpen = ref(false)

import { RouterLink } from 'vue-router'
import apiClient, { STORAGE_URL } from '../api'
import isiLogo from '@/assets/images/isi.png'
import heroBg from '@/assets/images/hero-bg.jpg'
import {
  SparklesIcon,
  UserGroupIcon,
  BriefcaseIcon,
  MapPinIcon,
  EnvelopeIcon,
  PhoneIcon,
  GlobeAltIcon,
  UserCircleIcon,
  BuildingOfficeIcon
} from '@heroicons/vue/24/outline'

const alumniProfiles = ref([])
const isLoadingAlumni = ref(true)

const mouseX = ref(0)
const mouseY = ref(0)

const getAvatarUrl = (profileUrl) => {
  if (!profileUrl) return null
  return profileUrl.startsWith('http') ? profileUrl : (STORAGE_URL + profileUrl)
}

// 3D Tilt Effect logic
const handleMouseMove = (e) => {
  mouseX.value = e.clientX
  mouseY.value = e.clientY
  
  // Appliquer le tilt sur les cartes alumni
  document.querySelectorAll('.tilt-card').forEach(card => {
    const rect = card.getBoundingClientRect()
    const x = e.clientX - rect.left
    const y = e.clientY - rect.top
    const centerX = rect.width / 2
    const centerY = rect.height / 2
    
    // Calcul de la rotation en fonction de la position de la souris vs le centre de la carte
    const rotateX = ((y - centerY) / centerY) * -10
    const rotateY = ((x - centerX) / centerX) * 10
    
    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`
    
    // Glow effect following mouse inside the card
    const glow = card.querySelector('.glow-effect')
    if(glow) {
       glow.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(14,165,233,0.15), transparent 40%)`
    }
  })
}

const handleMouseLeave = () => {
  document.querySelectorAll('.tilt-card').forEach(card => {
    card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`
    const glow = card.querySelector('.glow-effect')
    if(glow) {
       glow.style.background = 'transparent'
    }
  })
}

onMounted(async () => {
  window.addEventListener('mousemove', handleMouseMove)
  
  // Cinematic Intersection observer
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('opacity-100', 'translate-y-0', 'scale-100')
        entry.target.classList.remove('opacity-0', 'translate-y-16', 'scale-95', 'translate-y-10')
      }
    })
  }, { threshold: 0.15 })
  
  document.querySelectorAll('.animate-cinematic, .animate-on-scroll').forEach((el) => {
    el.classList.add('transition-all', 'duration-1000', 'ease-out', 'opacity-0')
    if(!el.classList.contains('translate-y-10')) {
      el.classList.add('translate-y-16')
    }
    observer.observe(el)
  })

  // Fetch true alumni
  try {
    const response = await apiClient.get('/public/showcase-alumni')
    alumniProfiles.value = response.data
  } catch (error) {
    console.error("Impossible de récupérer les anciens élèves", error)
  } finally {
    isLoadingAlumni.value = false
  }

  // --- INTERACTIVE JS "GAME/PLAY" CANVAS ---
  const canvas = document.getElementById('network-canvas')
  if (canvas) {
    const ctx = canvas.getContext('2d')
    let width = window.innerWidth
    let height = window.innerHeight
    canvas.width = width
    canvas.height = height

    const particles = []
    const properties = {
      bgColor: 'transparent',
      particleColor: 'rgba(14, 165, 233, 0.4)', // Sky-500 tint
      particleRadius: 3,
      particleCount: 60,
      particleMaxVelocity: 0.8,
      lineLength: 150,
      particleLife: 6,
    }

    window.addEventListener('resize', () => {
      width = window.innerWidth
      height = window.innerHeight
      canvas.width = width
      canvas.height = height
    })

    class Particle {
      constructor() {
        this.x = Math.random() * width
        this.y = Math.random() * height
        this.velocityX = Math.random() * (properties.particleMaxVelocity * 2) - properties.particleMaxVelocity
        this.velocityY = Math.random() * (properties.particleMaxVelocity * 2) - properties.particleMaxVelocity
        this.life = Math.random() * properties.particleLife * 60
      }
      position() {
        // Interactivity with mouse: nodes flee the mouse lightly
        const dx = mouseX.value - this.x
        const dy = mouseY.value - this.y
        const distance = Math.sqrt(dx * dx + dy * dy)
        if (distance < 100) {
          this.x -= dx * 0.05
          this.y -= dy * 0.05
        }

        this.x + this.velocityX > width && this.velocityX > 0 || this.x + this.velocityX < 0 && this.velocityX < 0 ? this.velocityX *= -1 : this.velocityX
        this.y + this.velocityY > height && this.velocityY > 0 || this.y + this.velocityY < 0 && this.velocityY < 0 ? this.velocityY *= -1 : this.velocityY
        this.x += this.velocityX
        this.y += this.velocityY
      }
      reDraw() {
        ctx.beginPath()
        ctx.arc(this.x, this.y, properties.particleRadius, 0, Math.PI * 2)
        ctx.closePath()
        ctx.fillStyle = properties.particleColor
        ctx.fill()
      }
    }

    const reDrawBackground = () => {
      ctx.clearRect(0, 0, width, height)
    }

    const drawLines = () => {
      let x1, y1, x2, y2, length, opacity
      for (let i in particles) {
        for (let j in particles) {
          x1 = particles[i].x
          y1 = particles[i].y
          x2 = particles[j].x
          y2 = particles[j].y
          length = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2))
          if (length < properties.lineLength) {
            opacity = 1 - length / properties.lineLength
            ctx.lineWidth = '0.5'
            ctx.strokeStyle = `rgba(14, 165, 233, ${opacity})`
            ctx.beginPath()
            ctx.moveTo(x1, y1)
            ctx.lineTo(x2, y2)
            ctx.closePath()
            ctx.stroke()
          }
        }
      }
    }

    const loop = () => {
      reDrawBackground()
      for (let i in particles) {
        particles[i].position()
        particles[i].reDraw()
      }
      drawLines()
      requestAnimationFrame(loop)
    }

    for (let i = 0; i < properties.particleCount; i++) {
      particles.push(new Particle())
    }
    loop()
  }
  
  // --- PWA INSTALL LOGIC ---
  window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt.value = e;
  });
})

onUnmounted(() => {
  window.removeEventListener('mousemove', handleMouseMove)
})

const deferredPrompt = ref(null);
const installPWA = async () => {
  if (deferredPrompt.value) {
    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    if (outcome === 'accepted') {
      console.log('User accepted the A2HS prompt');
    }
    deferredPrompt.value = null; // Can only be used once
  } else {
    alert("Votre navigateur ne supporte pas d'installation directe ou l'application est déjà installée.");
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 font-sans overflow-x-hidden selection:bg-sky-500 selection:text-white">
    
    <!-- Background Effects & Interactive Canvas -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <canvas id="network-canvas" class="absolute inset-0 z-10 w-full h-full pointer-events-auto mix-blend-multiply opacity-60"></canvas>
      <div class="absolute inset-0 bg-[linear-gradient(rgba(14,165,233,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(14,165,233,0.05)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

      <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-sky-200/40 rounded-full blur-[120px] mix-blend-multiply opacity-50 animate-pulse"></div>
      <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-blue-200/40 rounded-full blur-[100px] mix-blend-multiply opacity-40"></div>
    </div>

    <!-- Floating Glassmorphic Header / Nav -->
    <nav class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-6 md:px-16 py-4 md:py-5 transition-all duration-500 bg-white/70 backdrop-blur-xl border-b border-white/40 shadow-[0_8px_30px_rgb(0,0,0,0.08)]" id="main-nav">
      
      <!-- Brand Logo -->
      <a href="#home" class="flex items-center space-x-3 md:space-x-4 group cursor-pointer outline-none">
        <div class="relative bg-white p-2 md:p-2.5 rounded-xl md:rounded-2xl shadow-lg shadow-sky-500/20 border border-slate-100 group-hover:scale-105 transition-all duration-500 z-10 overflow-hidden">
          <img :src="isiLogo" alt="ISI Logo" class="h-8 md:h-12 w-auto relative z-10 drop-shadow-sm" />
        </div>
        <span class="text-lg md:text-2xl font-black tracking-tighter drop-shadow-md flex items-center shrink-0">
          <span class="text-slate-800 mr-2 hidden xs:inline">ISI Suptech</span> 
          <span class="text-sky-500 block">Alumni</span>
        </span>
      </a>

      <!-- Center Anchor Links (Desktop) -->
      <div class="hidden lg:flex items-center" style="gap: 2.5rem;">
        <a href="#features" class="text-sm font-black text-slate-500 hover:text-sky-600 uppercase tracking-widest transition-all drop-shadow-sm hover:scale-105">Le Hub</a>
        <a href="#alumni" class="text-sm font-black text-slate-500 hover:text-sky-600 uppercase tracking-widest transition-all drop-shadow-sm hover:scale-105">Fierté</a>
        <a href="#app-download" class="text-sm font-black text-slate-500 hover:text-sky-600 uppercase tracking-widest transition-all drop-shadow-sm hover:scale-105">Mobile</a>
        <a href="#campus" class="text-sm font-black text-slate-500 hover:text-sky-600 uppercase tracking-widest transition-all drop-shadow-sm hover:scale-105">Campus</a>
      </div>
      
      <!-- Nav Actions (Desktop & Trigger) -->
      <div class="flex items-center gap-4">
        <RouterLink to="/login" class="text-[10px] md:text-sm font-black text-slate-600 hover:text-sky-600 uppercase tracking-widest transition-colors hidden md:block drop-shadow-sm">
          Connexion
        </RouterLink>
        
        <RouterLink to="/register" class="relative overflow-hidden bg-slate-900 text-white px-5 md:px-8 py-3 md:py-4 rounded-full text-[9px] md:text-xs font-black uppercase tracking-widest shadow-xl transition-all hover:scale-105 active:scale-95 group hidden sm:block">
          <span class="relative z-10 flex items-center gap-2">
            Rejoindre
            <SparklesIcon class="h-4 w-4 text-sky-400" />
          </span>
          <div class="absolute inset-0 -z-10 translate-x-[100%] bg-gradient-to-r from-sky-500 via-blue-500 to-sky-500 transition-transform duration-500 group-hover:translate-x-0 opacity-80"></div>
        </RouterLink>

        <!-- Mobile Menu Trigger -->
        <button @click="isMenuOpen = !isMenuOpen" class="lg:hidden p-3 rounded-2xl bg-slate-100/50 text-slate-600 hover:text-sky-600 transition-all border border-slate-200">
          <div class="w-6 h-5 relative flex flex-col justify-between">
            <span :class="isMenuOpen ? 'rotate-45 translate-y-2' : ''" class="w-full h-1 bg-current rounded-full transition-all duration-300 transform-gpu"></span>
            <span :class="isMenuOpen ? 'opacity-0 scale-0' : ''" class="w-full h-1 bg-current rounded-full transition-all duration-300"></span>
            <span :class="isMenuOpen ? '-rotate-45 -translate-y-2' : ''" class="w-full h-1 bg-current rounded-full transition-all duration-300 transform-gpu"></span>
          </div>
        </button>
      </div>
    </nav>

    <!-- MOBILE NAVIGATION OVERLAY -->
    <div v-if="isMenuOpen" class="fixed inset-0 z-[60] bg-slate-950/98 backdrop-blur-3xl animate-in fade-in zoom-in duration-300 flex flex-col p-8 md:p-10">
      <button @click="isMenuOpen = false" class="self-end p-4 text-white/50 hover:text-white transition-all text-xl font-light focus:outline-none">✕ Fermer</button>
      
      <div class="flex-1 flex flex-col justify-center items-center gap-6 md:gap-8">
        <a v-for="link in [{h:'#home',l:'Accueil'},{h:'#features',l:'Le Hub'},{h:'#alumni',l:'Fierté'},{h:'#app-download',l:'Mobile'},{h:'#campus',l:'Campus'}]" 
           :key="link.h" :href="link.h" @click="isMenuOpen = false"
           class="text-xl md:text-2xl font-black text-white hover:text-sky-400 uppercase tracking-[0.2em] transition-all active:scale-95">
          {{ link.l }}
        </a>
        <div class="w-12 h-px bg-white/10 my-2"></div>
        <RouterLink @click="isMenuOpen = false" to="/login" class="text-lg font-black text-sky-400 uppercase tracking-widest active:scale-95">Connexion</RouterLink>
        <RouterLink @click="isMenuOpen = false" to="/register" class="w-full max-w-xs bg-sky-500 text-white px-8 py-4 rounded-full text-center font-black uppercase tracking-widest text-[10px] shadow-2xl shadow-sky-500/20 active:scale-95">Rejoindre ISI Alumni</RouterLink>
      </div>
    </div>


    <!-- Hero Section -->
    <section id="home" class="relative z-10 pt-32 md:pt-48 pb-20 md:pb-32 px-6 md:px-10 text-center flex flex-col items-center justify-center min-h-[80vh] bg-cover bg-center" :style="`background-image: url(${heroBg})`">

      <div class="absolute inset-0 bg-white/80 backdrop-blur-sm z-0"></div> <!-- Overlay for text readability -->
      
      <div class="relative z-10 inline-flex items-center space-x-2 bg-white border border-sky-100 shadow-xl shadow-sky-100 px-5 py-2.5 rounded-full mb-8 animate-fade-in-down">
        <SparklesIcon class="h-4 w-4 text-sky-500" />
        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-sky-600">Réseau d'Excellence Numérique</span>
      </div>
      
      <h1 class="relative z-10 text-4xl md:text-8xl font-black tracking-tighter leading-none mb-8 animate-fade-in-up text-slate-900 drop-shadow-sm uppercase">
        L'Écosystème des <br>
        <span class="text-sky-600 drop-shadow-lg font-black">Alumni ISI</span>
      </h1>

      
      <p class="relative z-10 text-lg md:text-xl text-slate-700 max-w-2xl font-bold mb-12 animate-fade-in-up delay-100">
        Bienvenue sur ISI Connect, la plateforme exclusive pour connecter les diplômés, propulser les carrières, partager des opportunités et faire rayonner l'excellence de notre institut.
      </p>
      
      <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6 animate-fade-in-up delay-200">
        <RouterLink to="/register" class="w-full sm:w-auto bg-sky-500 hover:bg-sky-600 text-white px-10 py-5 rounded-full text-sm font-black uppercase tracking-widest shadow-xl shadow-sky-500/30 hover:scale-105 transition-all outline-none">
          Créer un compte
        </RouterLink>
        <a href="#features" class="w-full sm:w-auto text-slate-800 hover:text-sky-600 px-10 py-5 rounded-full text-sm font-black uppercase tracking-widest transition-colors flex items-center justify-center group bg-white border border-slate-200 shadow-xl shadow-slate-100 hover:border-sky-200 hover:shadow-sky-100">
          Découvrir
          <svg class="w-4 h-4 ml-2 group-hover:translate-y-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </a>
      </div>
    </section>

    <!-- Platform Features -->
    <section id="features" class="relative z-10 py-20 md:py-32 px-6 md:px-10 bg-white border-y border-slate-100 shadow-[0_0_50px_rgba(0,0,0,0.02)]">

      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12 md:mb-20 animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000">
          <h2 class="text-3xl md:text-5xl font-black tracking-tighter mb-4 text-slate-900 drop-shadow-sm uppercase">Le <span class="text-sky-500">Hub</span> Digital</h2>

          <p class="text-slate-500 font-medium">Des outils taillés sur mesure pour votre évolution professionnelle.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
          <!-- Feature 1 -->
          <div class="bg-slate-50 border border-slate-100 p-10 rounded-[3rem] hover:shadow-2xl hover:shadow-sky-500/20 hover:-translate-y-2 hover:bg-white hover:border-sky-200 transition-all duration-500 group animate-on-scroll opacity-0 translate-y-10">
            <div class="h-16 w-16 bg-white shadow-[0_0_20px_rgba(14,165,233,0.1)] border border-slate-100 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:bg-sky-50 transition-all">
              <UserGroupIcon class="h-8 w-8 text-sky-500 group-hover:text-sky-600" />
            </div>
            <h3 class="text-2xl font-black mb-4 text-slate-800">Annuaire Global</h3>
            <p class="text-slate-500 text-sm leading-relaxed font-medium">
              Retrouvez facilement vos anciens camarades de promotion. Discutez en temps réel via la messagerie intégrée et développez votre réseau.
            </p>
          </div>
          
          <!-- Feature 2 -->
          <div class="bg-slate-50 border border-slate-100 p-10 rounded-[3rem] hover:shadow-2xl hover:shadow-blue-500/20 hover:-translate-y-2 hover:bg-white hover:border-blue-200 transition-all duration-500 group animate-on-scroll opacity-0 translate-y-10 delay-100">
            <div class="h-16 w-16 bg-white shadow-[0_0_20px_rgba(59,130,246,0.1)] border border-slate-100 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:bg-blue-50 transition-all">
              <BriefcaseIcon class="h-8 w-8 text-blue-500 group-hover:text-blue-600" />
            </div>
            <h3 class="text-2xl font-black mb-4 text-slate-800">Opportunités Pro</h3>
            <p class="text-slate-500 text-sm leading-relaxed font-medium">
              Accédez à un marché de l'emploi privilégié. Partagez des offres CDI, stages ou missions freelance issues de nos entreprises partenaires.
            </p>
          </div>
          
          <!-- Feature 3 -->
          <div class="bg-slate-50 border border-slate-100 p-10 rounded-[3rem] hover:shadow-2xl hover:shadow-indigo-500/20 hover:-translate-y-2 hover:bg-white hover:border-indigo-200 transition-all duration-500 group animate-on-scroll opacity-0 translate-y-10 delay-200">
            <div class="h-16 w-16 bg-white shadow-[0_0_20px_rgba(99,102,241,0.1)] border border-slate-100 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:bg-indigo-50 transition-all">
              <GlobeAltIcon class="h-8 w-8 text-indigo-500 group-hover:text-indigo-600" />
            </div>
            <h3 class="text-2xl font-black mb-4 text-slate-800">Forums & Groupes</h3>
            <p class="text-slate-500 text-sm leading-relaxed font-medium">
              Rejoignez des groupes de travail thématiques et participez aux discussions pour entraider les anciennes et futures générations.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Real Alumni Showcase -->
    <section id="alumni" class="relative z-10 py-32 px-10">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-end justify-between mb-20 animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000">
          <div>
            <h2 class="text-4xl md:text-5xl font-black tracking-tighter mb-4 text-slate-900 drop-shadow-sm">Notre Fierté : <span class="text-sky-500">Les Alumni</span></h2>
            <p class="text-slate-500 font-medium">Découvrez quelques membres brillants déjà inscrits sur la plateforme.</p>
          </div>
          <RouterLink to="/register" class="mt-8 md:mt-0 text-[11px] font-black uppercase tracking-[0.3em] text-sky-500 hover:text-sky-600 bg-white hover:bg-sky-50 border border-sky-100 px-6 py-3 rounded-full shadow-md hover:shadow-sky-500/30 transition-all flex items-center">
            Rejoignez-les <span class="ml-2">→</span>
          </RouterLink>
        </div>
        
        <div v-if="isLoadingAlumni" class="flex justify-center py-10">
           <div class="h-10 w-10 border-4 border-slate-200 border-t-sky-500 rounded-full animate-spin"></div>
        </div>
        
        <div v-if="alumniProfiles.length === 0" class="text-center py-20 px-4 max-w-lg mx-auto bg-slate-50 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 animate-cinematic">
          <UserGroupIcon class="h-16 w-16 mx-auto mb-6 text-sky-300" />
          <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 mb-2">Annuaire en cours</h3>
          <p class="text-slate-500 font-medium">La famille ISI grandit. Bientôt, les talents s'afficheront ici.</p>
          <RouterLink to="/register" class="mt-8 inline-block bg-white border border-sky-100 text-sky-600 px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg shadow-sky-100/50 hover:bg-sky-50 hover:text-sky-700 hover:border-sky-300 transition-all">
            Soyez le premier
          </RouterLink>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div 
            v-for="(alumnus, idx) in alumniProfiles" 
            :key="alumnus.id"
            class="tilt-card relative bg-white border border-slate-100 rounded-[2.5rem] p-8 hover:-translate-y-2 transition-all duration-300 shadow-xl shadow-slate-200/50 group overflow-hidden animate-cinematic"
            :style="{ transitionDelay: `${idx * 100}ms`, transformStyle: 'preserve-3d' }"
            @mouseleave="handleMouseLeave"
          >
            <!-- The glow effect attached to mouse -->
            <div class="pointer-events-none glow-effect absolute inset-0 z-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <div class="relative z-10">
              <!-- Image with nice crop -->
              <div class="h-24 w-24 mx-auto mb-6 rounded-[1.5rem] overflow-hidden bg-slate-100 border-4 border-white shadow-xl shadow-sky-500/10 pointer-events-none transform translate-z-10 group-hover:scale-105 transition-transform duration-500">
                <img v-if="getAvatarUrl(alumnus.profile?.profile_picture_url)" :src="getAvatarUrl(alumnus.profile?.profile_picture_url)" class="h-full w-full object-cover" />
                <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400 font-black text-2xl uppercase">
                  {{ alumnus.name.charAt(0) }}
                </div>
              </div>
              
              <h3 class="text-lg font-black text-slate-900 group-hover:text-sky-600 transition-colors mb-1 truncate translate-z-20 transform">{{ alumnus.name }}</h3>
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-6 translate-z-20 transform">Promotion {{ alumnus.promotion_year }}</p>
              
              <div class="space-y-3 pt-6 border-t border-slate-100 translate-z-30 transform">
                <div class="flex items-center text-slate-500 text-xs font-bold">
                  <BriefcaseIcon class="h-4 w-4 mr-3 text-sky-400" />
                  <span class="truncate">{{ alumnus.profile?.job_title || 'En transition' }}</span>
                </div>
                <div class="flex items-center text-slate-500 text-xs font-bold">
                  <BuildingOfficeIcon class="h-4 w-4 mr-3 text-sky-400" />
                  <span class="truncate">{{ alumnus.profile?.company_name || 'Écosystème Numérique' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- App / PWA Download Section WOW Effect -->
    <section id="app-download" class="relative z-10 py-32 px-10 bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-700 text-white overflow-hidden shadow-2xl">
      <!-- Glows -->
      <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/10 rounded-full blur-[100px] pointer-events-none"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-sky-300/20 rounded-full blur-[100px] pointer-events-none"></div>
      
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-16 relative z-10">
        <div class="md:w-1/2 animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000">
          <div class="inline-flex items-center space-x-2 bg-white/10 border border-white/20 px-4 py-2 rounded-full mb-8 backdrop-blur-md">
            <PhoneIcon class="h-4 w-4 text-sky-200" />
            <span class="text-[10px] font-black uppercase tracking-[0.3em] text-white">Application Mobile</span>
          </div>
          <h2 class="text-5xl lg:text-7xl font-black tracking-tighter mb-6 leading-none drop-shadow-xl">Le réseau,<br>dans votre poche.</h2>
          <p class="text-sky-100 text-lg mb-10 font-medium max-w-lg leading-relaxed">
            Installez l'application ISI Connect directement sur votre smartphone en un clic (PWA). Recevez les alertes emplois et discutez en temps réel, partout.
          </p>
          <div class="flex space-x-4">
            <button @click="installPWA" class="bg-white text-blue-600 px-8 py-4 rounded-full font-black uppercase tracking-widest text-xs shadow-xl shadow-black/20 hover:scale-105 transition-transform flex items-center">
              Installer l'App (PWA)
            </button>
          </div>
        </div>
        
        <div class="md:w-1/2 flex justify-center animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000 delay-200 perspective-1000">
          <!-- Real UI Phone mockup -->
          <div class="relative w-72 h-[550px] bg-slate-900 rounded-[3rem] border-[8px] border-slate-800 shadow-2xl shadow-black/40 overflow-hidden transform rotate-y-[-15deg] rotate-x-[10deg] hover:rotate-y-0 hover:rotate-x-0 transition-transform duration-1000 flex flex-col">
            <div class="absolute top-0 inset-x-0 h-6 bg-slate-800 flex justify-center rounded-t-3xl z-20">
              <div class="w-20 h-4 bg-slate-900 rounded-b-xl"></div> <!-- notch -->
            </div>
            
            <!-- Mock App Header -->
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 pt-10 pb-4 px-4 flex items-center justify-between shadow-md z-10">
               <img :src="isiLogo" class="h-6 w-auto bg-white p-1 rounded-md" />
               <div class="h-6 w-6 rounded-full bg-white/20"></div>
            </div>
            
            <!-- Mock App Body -->
            <div class="flex-1 bg-slate-50 p-4 space-y-4 overflow-hidden">
               <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Fil d'actualité</div>
               
               <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 flex items-start gap-3">
                 <div class="h-8 w-8 rounded-full bg-sky-100 shrink-0"></div>
                 <div class="space-y-2 w-full">
                   <div class="h-2 w-1/2 bg-slate-200 rounded"></div>
                   <div class="h-10 w-full bg-slate-100 rounded"></div>
                 </div>
               </div>
               
               <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 flex items-start gap-3">
                 <div class="h-8 w-8 rounded-full bg-blue-100 shrink-0"></div>
                 <div class="space-y-2 w-full">
                   <div class="h-2 w-1/3 bg-slate-200 rounded"></div>
                   <div class="h-8 w-full bg-slate-100 rounded"></div>
                 </div>
               </div>
            </div>
            
            <!-- Mock App Navbar -->
            <div class="bg-white p-4 border-t border-slate-100 flex justify-around">
               <div class="h-5 w-5 bg-sky-500 rounded-full"></div>
               <div class="h-5 w-5 bg-slate-200 rounded-full"></div>
               <div class="h-5 w-5 bg-slate-200 rounded-full"></div>
               <div class="h-5 w-5 bg-slate-200 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Location & Contact -->
    <section id="campus" class="relative z-10 py-32 px-10 bg-slate-950 text-white">
      <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
        
        <div class="animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000">
          <div class="inline-flex items-center space-x-2 bg-blue-500/10 border border-blue-400/20 px-4 py-2 rounded-full mb-8">
            <MapPinIcon class="h-4 w-4 text-blue-400" />
            <span class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-300">Notre Campus</span>
          </div>
          <h2 class="text-5xl lg:text-6xl font-black tracking-tighter mb-8 leading-none drop-shadow-md">Institut Supérieur <br>d'Informatique</h2>
          <p class="text-slate-400 text-lg mb-10 font-medium">
            Le point de départ de vos ambitions. ISI Suptech forme les leaders technologiques de demain, avec une pédagogie axée sur l'innovation et la pratique.
          </p>
          
          <div class="space-y-6">
            <div class="flex items-center space-x-5 group">
              <div class="h-14 w-14 rounded-2xl bg-slate-900 flex items-center justify-center shrink-0 border border-slate-800 shadow-xl group-hover:bg-slate-800 transition-colors">
                <MapPinIcon class="h-6 w-6 text-sky-400 group-hover:scale-110 transition-transform" />
              </div>
              <div>
                <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest">Localisation Centrale</p>
                <p class="text-white font-medium text-lg">Sicap Liberté 3 Nº1977, Dakar</p>
              </div>
            </div>
            
            <div class="flex items-center space-x-5 group">
              <div class="h-14 w-14 rounded-2xl bg-slate-900 flex items-center justify-center shrink-0 border border-slate-800 shadow-xl group-hover:bg-slate-800 transition-colors">
                <PhoneIcon class="h-6 w-6 text-sky-400 group-hover:scale-110 transition-transform" />
              </div>
              <div>
                <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest">Contact Téléphonique</p>
                <p class="text-white font-medium text-lg">00221 33 825 62 10</p>
              </div>
            </div>
            
            <div class="flex items-center space-x-5 group">
              <div class="h-14 w-14 rounded-2xl bg-slate-900 flex items-center justify-center shrink-0 border border-slate-800 shadow-xl group-hover:bg-slate-800 transition-colors">
                <EnvelopeIcon class="h-6 w-6 text-sky-400 group-hover:scale-110 transition-transform" />
              </div>
              <div>
                <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest">Support / Contact</p>
                <p class="text-white font-medium text-lg">suptech@suptech.com</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Real Google Maps Iframe embedded inside the mockup frame for WOW mapping -->
        <div class="relative h-[500px] lg:h-[600px] w-full rounded-[4rem] overflow-hidden group animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000 delay-200 shadow-2xl shadow-sky-500/10 border-4 border-slate-800 bg-slate-900">
          <div class="absolute inset-0 z-10 pointer-events-none rounded-[4rem] shadow-[inset_0_0_50px_rgba(0,0,0,0.8)]"></div>
          
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15433.874403666014!2d-17.464673!3d14.717621!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTTCsDQzJzAzLjQiTiAxN8KwMjcnNTIuOCJX!5e0!3m2!1sen!2ssn!4v1600000000000!5m2!1sen!2ssn" 
            width="100%" 
            height="100%" 
            style="border:0; filter: invert(90%) hue-rotate(180deg) brightness(80%) contrast(120%);" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade"
            class="transition-all duration-1000 group-hover:scale-105"
          >
          </iframe>

          <div class="absolute bottom-10 left-10 right-10 z-30 bg-slate-950/80 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl">
            <h3 class="text-2xl font-black mb-2 text-white flex items-center"><MapPinIcon class="h-6 w-6 mr-2 text-sky-400" /> Rejoignez-nous sur le Campus</h3>
            <p class="text-slate-400 text-sm mb-6 font-medium">L'épicentre technologique où les générations d'hier et de demain se rencontrent.</p>
            <RouterLink to="/register" class="inline-flex items-center justify-center w-full bg-sky-500 text-white font-black text-xs uppercase tracking-widest px-8 py-4 rounded-xl hover:bg-sky-400 hover:shadow-lg hover:shadow-sky-500/50 transition-all">
              S'inscrire maintenant
            </RouterLink>
          </div>
        </div>

      </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-800/50 py-10 text-center bg-slate-950">
      <div class="flex items-center justify-center space-x-2 mb-4 opacity-50">
        <img :src="isiLogo" alt="ISI Logo" class="h-6 w-auto grayscale" />
      </div>
      <p class="text-xs text-slate-500 font-medium uppercase tracking-[0.2em]">
        © {{ new Date().getFullYear() }} ISI Connect. Un écosystème propulsé par ISI SUPTECH.
      </p>
    </footer>

  </div>
</template>

<style scoped>
@keyframes fade-in-up {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}

@keyframes fade-in-down {
  0% { opacity: 0; transform: translateY(-20px); }
  100% { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-up {
  animation: fade-in-up 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-fade-in-down {
  animation: fade-in-down 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
</style>
