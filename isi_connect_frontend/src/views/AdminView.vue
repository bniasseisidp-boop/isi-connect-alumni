<template>
  <div class="min-h-screen bg-[#0a0f1e] text-white font-sans flex flex-col overflow-x-hidden">

    <!-- Animated BG -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute top-0 left-1/4 w-[600px] h-[600px] bg-sky-500/10 rounded-full blur-[120px]"></div>
      <div class="absolute bottom-0 right-1/4 w-[400px] h-[400px] bg-blue-600/10 rounded-full blur-[100px]"></div>
    </div>

    <!-- TOP NAVBAR -->
    <nav class="relative z-50 flex items-center justify-between px-6 md:px-10 py-4 md:py-5 border-b border-white/5 bg-white/3 backdrop-blur-2xl">
      <div class="flex items-center gap-3 md:gap-5">
        <div class="bg-white p-2 rounded-xl shadow-xl shadow-sky-500/20 border border-white/10 shrink-0">
          <img src="../assets/images/isi.png" alt="ISI" class="h-7 md:h-9 w-auto" />
        </div>
        <div class="min-w-0">
          <h1 class="text-white font-black text-sm md:text-xl tracking-tight truncate">ISI Suptech <span class="text-sky-400">Alumni</span></h1>
          <p class="text-[7px] md:text-[9px] font-black uppercase tracking-[0.2em] md:tracking-[0.4em] text-sky-500/70 truncate">Panneau d'Administration</p>
        </div>
      </div>

      <div class="flex items-center gap-3 md:gap-6">
        <div class="hidden sm:flex flex-col items-end">
          <span class="text-white font-black tabular-nums text-xs md:text-sm">{{ currentTime }}</span>
          <span class="text-slate-500 text-[8px] md:text-[10px] font-bold uppercase tracking-widest">{{ currentDate }}</span>
        </div>
        <div class="hidden sm:block w-px h-8 bg-white/10"></div>
        <div class="flex items-center gap-2 md:gap-3">
          <div class="h-8 w-8 md:h-9 md:w-9 rounded-full bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center font-black text-xs md:text-sm shadow-lg shadow-sky-500/30 shrink-0">
            {{ auth.user.value?.name?.charAt(0)?.toUpperCase() }}
          </div>
          <div class="hidden lg:block text-left">
            <p class="text-white font-black text-sm leading-none">{{ auth.user.value?.name }}</p>
            <p class="text-sky-400 text-[9px] font-black uppercase tracking-widest mt-1">Admin Principal</p>
          </div>
        </div>
        <button @click="handleLogout" class="flex items-center gap-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 hover:border-red-400/40 text-red-400 px-3 md:px-5 py-2 md:py-2.5 rounded-full text-[9px] md:text-[11px] font-black uppercase tracking-widest transition-all">
          <span>Quitter</span>
        </button>
      </div>
    </nav>


    <!-- TABS NAVIGATION -->
    <div class="relative z-40 bg-[#0c1226]/50 border-b border-white/5 backdrop-blur-md px-6 md:px-10 overflow-x-auto custom-scrollbar">
      <div class="max-w-7xl mx-auto flex items-center gap-6 md:gap-10 whitespace-nowrap">
        <button 
          v-for="tab in tabs" :key="tab.id"
          @click="activeTab = tab.id"
          :class="activeTab === tab.id ? 'text-sky-400 border-sky-400' : 'text-slate-500 border-transparent hover:text-slate-300'"
          class="py-4 text-[9px] md:text-[11px] font-black uppercase tracking-[0.2em] border-b-2 transition-all"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>


    <!-- MAIN -->
    <main class="relative z-10 flex-1 max-w-7xl mx-auto w-full px-6 md:px-8 py-8 md:py-10">

      <!-- 1. DASHBOARD TAB -->
      <div v-if="activeTab === 'dashboard'" class="space-y-8 md:space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-700">

        <!-- Hero -->
        <div class="relative rounded-[2.5rem] overflow-hidden border border-white/5 bg-white/3 backdrop-blur-sm p-10">
          <div class="absolute inset-0 bg-gradient-to-br from-sky-500/10 via-transparent to-blue-600/10 pointer-events-none"></div>
          <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
            <div>
              <div class="inline-flex items-center gap-2 bg-sky-500/10 border border-sky-500/20 text-sky-400 text-[10px] font-black uppercase tracking-[0.3em] px-4 py-2 rounded-full mb-6">
                <span class="h-1.5 w-1.5 bg-sky-400 rounded-full animate-pulse"></span>
                Système Opérationnel
              </div>
              <h2 class="text-4xl md:text-5xl font-black text-white tracking-tight mb-3">
                Bonjour, <span class="text-sky-400">{{ auth.user.value?.name }}</span> 👋
              </h2>
              <p class="text-slate-400 font-medium max-w-lg">Gérez la plateforme ISI Connect. Voici les statistiques globales d'activité.</p>
            </div>
            <div class="flex flex-col items-end gap-3 text-right">
              <button @click="fetchStats" class="flex items-center gap-3 bg-white/5 hover:bg-white/10 text-white px-7 py-4 rounded-2xl font-black text-sm uppercase tracking-widest transition-all border border-white/10">
                Rafraîchir les stats
              </button>
              <p class="text-slate-500 text-xs font-bold">Dernière mise à jour : {{ currentTime }}</p>
            </div>
          </div>
        </div>

        <!-- Real Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
          <div 
            v-for="stat in dashboardStats" :key="stat.label"
            class="relative bg-white/3 border border-white/5 rounded-2xl md:rounded-3xl p-5 md:p-7 hover:border-white/10 transition-all group"
          >
            <div :class="stat.glow" class="absolute -top-6 -right-6 w-24 h-24 rounded-full blur-2xl opacity-10 group-hover:opacity-30 transition-opacity"></div>
            <p class="text-[7px] md:text-[9px] font-black uppercase tracking-[0.3em] md:tracking-[0.4em] text-slate-500 mb-2 md:mb-4">{{ stat.label }}</p>
            <p :class="stat.color" class="text-2xl md:text-4xl font-black mb-1">{{ stat.value }}</p>
            <p class="text-slate-500 text-[10px] md:text-xs font-bold">{{ stat.sub }}</p>
          </div>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
          <!-- Recent Users List -->
          <div class="lg:col-span-3 bg-white/3 border border-white/5 rounded-3xl p-8">
            <div class="flex items-center justify-between mb-8">
              <h3 class="text-white font-black text-lg">Inscriptions Récentes</h3>
              <button @click="activeTab = 'users'" class="text-[10px] font-black uppercase tracking-widest text-sky-400 hover:text-sky-300">Tout gérer →</button>
            </div>
            <div class="space-y-3">
              <div v-for="u in recentUsers" :key="u.id" class="flex items-center gap-4 p-4 rounded-2xl bg-white/2 border border-white/3">
                <div class="h-10 w-10 rounded-xl bg-sky-500/20 flex items-center justify-center font-black text-sky-400">{{ u.name?.[0] }}</div>
                <div class="flex-1">
                  <p class="text-sm font-black">{{ u.name }}</p>
                  <p class="text-[10px] text-slate-500">{{ u.email }}</p>
                </div>
                <span class="text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full border border-white/5 bg-white/5">{{ u.role }}</span>
              </div>
            </div>
          </div>
          <!-- Fast Actions -->
          <div class="lg:col-span-2 bg-white/3 border border-white/5 rounded-3xl p-8">
              <h3 class="text-white font-black text-lg mb-6">Actions Rapides</h3>
              <div class="space-y-3">
                <button @click="activeTab = 'users'" class="w-full flex items-center justify-between p-5 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 hover:bg-sky-500/20 transition-all font-black text-sm uppercase tracking-widest">
                  Gérer les membres <span>👥</span>
                </button>
                <button @click="activeTab = 'moderation'" class="w-full flex items-center justify-between p-5 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-400 hover:bg-purple-500/20 transition-all font-black text-sm uppercase tracking-widest">
                  Modérer le contenu <span>🛡️</span>
                </button>
              </div>
          </div>
        </div>
      </div>

      <!-- 2. USERS TAB -->
      <div v-if="activeTab === 'users'" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-4">
          <div>
            <h2 class="text-3xl font-black">Gestion des Utilisateurs</h2>
            <p class="text-slate-500 text-sm font-bold">Visualisez, modifiez les rôles ou supprimez des membres.</p>
          </div>
          <div class="relative w-full md:w-96">
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Rechercher par nom ou email..." 
              class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-sky-500/50 transition-all"
            />
          </div>
        </div>

        <div class="bg-white/3 border border-white/5 rounded-[2rem] overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead>
                <tr class="border-b border-white/5 bg-white/2">
                  <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-500">Utilisateur</th>
                  <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-500">Rôle</th>
                  <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-500">Année Promo</th>
                  <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5">
                <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-white/2 transition-colors group">
                  <td class="px-8 py-5">
                    <div class="flex items-center gap-4">
                      <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center font-black text-sm">
                        {{ user.name?.[0] }}
                      </div>
                      <div>
                        <p class="font-black text-sm">{{ user.name }}</p>
                        <p class="text-[11px] text-slate-500">{{ user.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-8 py-5">
                    <select 
                      @change="handleRoleUpdate(user, $event.target.value)"
                      :value="user.role"
                      class="bg-white/5 border border-white/10 rounded-xl px-3 py-1.5 text-[11px] font-black uppercase tracking-widest focus:outline-none focus:border-sky-500"
                    >
                      <option value="alumni">Alumni</option>
                      <option value="student">Étudiant</option>
                      <option value="admin">Admin</option>
                    </select>
                  </td>
                  <td class="px-8 py-5">
                    <span class="text-sm font-bold text-slate-400">{{ user.promotion_year || '—' }}</span>
                  </td>
                  <td class="px-8 py-5 text-right">
                    <button 
                      @click="confirmDeleteUser(user)"
                      class="opacity-0 group-hover:opacity-100 p-3 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500/20 transition-all"
                    >
                      🗑️
                    </button>
                  </td>
                </tr>
                <tr v-if="paginatedUsers.length === 0">
                  <td colspan="4" class="px-8 py-20 text-center text-slate-500 font-bold italic">
                    Aucun utilisateur trouvé pour cette recherche.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Pagination simple -->
          <div class="p-6 border-t border-white/5 flex items-center justify-between">
              <span class="text-xs text-slate-500 font-bold">Affichage de {{ paginatedUsers.length }} utilisateurs</span>
              <div class="flex gap-2">
                <button @click="userPage--" :disabled="userPage === 1" class="px-4 py-2 bg-white/5 rounded-xl text-xs font-black disabled:opacity-30">Précédent</button>
                <button @click="userPage++" :disabled="!hasMoreUsers" class="px-4 py-2 bg-white/5 rounded-xl text-xs font-black disabled:opacity-30">Suivant</button>
              </div>
          </div>
        </div>
      </div>

      <!-- 3. MODERATION TAB -->
      <div v-if="activeTab === 'moderation'" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div>
           <h2 class="text-3xl font-black">Modération de Contenu</h2>
           <p class="text-slate-500 text-sm font-bold mb-8">Nettoyez la plateforme en supprimant les contenus inappropriés.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          
          <!-- Jobs Moderation -->
          <div class="bg-white/3 border border-white/5 rounded-[2rem] p-8">
            <h3 class="font-black text-lg mb-6 flex items-center gap-3">💼 Offres d'emploi <span class="text-[10px] bg-sky-500/20 text-sky-400 px-2 py-0.5 rounded-full">{{ moderatedJobs.length }}</span></h3>
            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
               <div v-for="job in moderatedJobs" :key="job.id" class="p-4 rounded-2xl bg-white/2 border border-white/5 flex justify-between items-center group">
                  <div class="min-w-0">
                    <p class="font-black text-sm truncate">{{ job.title }}</p>
                    <p class="text-[10px] text-slate-500 truncate">{{ job.company_name }}</p>
                  </div>
                  <button @click="deleteItem('jobs', job.id)" class="p-2.5 rounded-xl bg-red-500/10 text-red-500 opacity-0 group-hover:opacity-100 transition-all">🗑️</button>
               </div>
               <p v-if="moderatedJobs.length === 0" class="text-center text-slate-600 text-xs py-10 italic">Aucune offre à modérer.</p>
            </div>
          </div>

           <!-- Forum Moderation -->
           <div class="bg-white/3 border border-white/5 rounded-[2rem] p-8">
            <h3 class="font-black text-lg mb-6 flex items-center gap-3">💬 Discussions Forum <span class="text-[10px] bg-purple-500/20 text-purple-400 px-2 py-0.5 rounded-full">{{ moderatedThreads.length }}</span></h3>
            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
               <div v-for="t in moderatedThreads" :key="t.id" class="p-4 rounded-2xl bg-white/2 border border-white/5 flex justify-between items-center group">
                  <div class="min-w-0">
                    <p class="font-black text-sm truncate">{{ t.title }}</p>
                    <p class="text-[10px] text-slate-500 truncate">Par {{ t.user?.name || 'Inconnu' }}</p>
                  </div>
                  <button @click="deleteItem('forum-threads', t.id)" class="p-2.5 rounded-xl bg-red-500/10 text-red-500 opacity-0 group-hover:opacity-100 transition-all">🗑️</button>
               </div>
               <p v-if="moderatedThreads.length === 0" class="text-center text-slate-600 text-xs py-10 italic">Aucune discussion à modérer.</p>
            </div>
          </div>

          <!-- Events Moderation -->
          <div class="bg-white/3 border border-white/5 rounded-[2rem] p-8">
            <h3 class="font-black text-lg mb-6 flex items-center gap-3">📅 Événements <span class="text-[10px] bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded-full">{{ moderatedEvents.length }}</span></h3>
            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
               <div v-for="e in moderatedEvents" :key="e.id" class="p-4 rounded-2xl bg-white/2 border border-white/5 flex justify-between items-center group">
                  <div class="min-w-0">
                    <p class="font-black text-sm truncate">{{ e.title }}</p>
                    <p class="text-[10px] text-slate-500 truncate">{{ e.event_date }}</p>
                  </div>
                  <button @click="deleteItem('events', e.id)" class="p-2.5 rounded-xl bg-red-500/10 text-red-500 opacity-0 group-hover:opacity-100 transition-all">🗑️</button>
               </div>
               <p v-if="moderatedEvents.length === 0" class="text-center text-slate-600 text-xs py-10 italic">Aucun événement à modérer.</p>
            </div>
          </div>

          <!-- Social Posts Moderation -->
          <div class="bg-white/3 border border-white/5 rounded-[2rem] p-8">
            <h3 class="font-black text-lg mb-6 flex items-center gap-3">📱 Posts Timeline <span class="text-[10px] bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded-full">{{ moderatedPosts.length }}</span></h3>
            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
               <div v-for="p in moderatedPosts" :key="p.id" class="p-4 rounded-2xl bg-white/2 border border-white/5 flex justify-between items-center group">
                  <div class="min-w-0 flex-1">
                    <p class="text-sm italic truncate">"{{ p.content }}"</p>
                    <p class="text-[10px] text-slate-500 truncate">Par {{ p.user?.name || 'Inconnu' }}</p>
                  </div>
                  <button @click="deleteItem('posts', p.id)" class="p-2.5 rounded-xl bg-red-500/10 text-red-500 opacity-0 group-hover:opacity-100 transition-all">🗑️</button>
               </div>
               <p v-if="moderatedPosts.length === 0" class="text-center text-slate-600 text-xs py-10 italic">Aucun post à modérer.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. INVITATIONS TAB -->
      <div v-if="activeTab === 'invitations'" class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="mb-2">
          <h2 class="text-3xl font-black">Invitations & Imports</h2>
          <p class="text-slate-500 text-sm font-bold">Ajoutez de nouveaux membres manuellement ou via un fichier CSV.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
          
          <!-- Single Invite -->
          <div class="bg-white/3 border border-white/5 rounded-[2.5rem] p-10 flex flex-col">
            <h3 class="text-white font-black text-xl mb-8 flex items-center gap-4">
              <span class="p-3 bg-sky-500/10 text-sky-400 rounded-2xl text-lg">👤</span>
              Individuel
            </h3>
            
            <form @submit.prevent="handleSingleInvite" class="space-y-5">
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Nom Complet</label>
                <input v-model="inviteForm.name" type="text" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-sky-500/50 transition-all" placeholder="Ex: Moussa Diop">
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Email</label>
                <input v-model="inviteForm.email" type="email" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-sky-500/50 transition-all" placeholder="moussa@isi.sn">
              </div>
              <div class="grid grid-cols-2 gap-5">
                <div class="space-y-2">
                  <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Promotion</label>
                  <input v-model="inviteForm.promotion_year" type="number" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-sky-500/50 transition-all">
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Groupe (Optionnel)</label>
                  <select v-model="inviteForm.work_group_id" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-sky-500/50 transition-all">
                    <option :value="null">Aucun</option>
                    <option v-for="g in workGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
                  </select>
                </div>
              </div>
              <button type="submit" :disabled="isInviting" class="w-full mt-4 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white p-5 rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-lg shadow-sky-500/20 transition-all disabled:opacity-50">
                {{ isInviting ? 'Envoi...' : 'Envoyer l\'invitation' }}
              </button>
            </form>
          </div>

          <!-- Bulk Import -->
          <div class="bg-white/3 border border-white/5 rounded-[2.5rem] p-10 flex flex-col">
            <h3 class="text-white font-black text-xl mb-8 flex items-center gap-4">
              <span class="p-3 bg-emerald-500/10 text-emerald-400 rounded-2xl text-lg">📄</span>
              Importation CSV
            </h3>

            <div class="flex-1 flex flex-col justify-between">
              <div class="space-y-6">
                <p class="text-slate-400 text-sm leading-relaxed">
                  Importez des centaines de membres d'un coup. Le fichier doit contenir les colonnes : <code class="text-sky-400 font-bold bg-white/5 px-2 py-1 rounded">email, name, promotion_year</code>.
                </p>

                <div class="space-y-2">
                  <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Groupe par défaut</label>
                  <select v-model="importGroupId" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-sky-500/50 transition-all">
                    <option :value="null">Aucun</option>
                    <option v-for="g in workGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
                  </select>
                </div>

                <div @click="$refs.csvInput.click()" class="border-2 border-dashed border-white/10 rounded-3xl p-10 text-center hover:border-emerald-500/50 transition-all cursor-pointer group">
                  <input type="file" ref="csvInput" @change="handleFileSelect" class="hidden" accept=".csv">
                  <div class="h-16 w-16 bg-emerald-500/10 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <span class="text-2xl">☁️</span>
                  </div>
                  <p class="text-white font-black text-sm mb-1">{{ selectedFile ? selectedFile.name : 'Choisir un fichier CSV' }}</p>
                  <p class="text-slate-500 text-xs font-bold">ou glisser-déposer ici</p>
                </div>
              </div>

              <div class="mt-8 space-y-4">
                <button @click="downloadTemplate" class="w-full bg-white/5 border border-white/10 hover:border-emerald-500/50 text-emerald-400 p-4 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                  📥 Télécharger le modèle (Excel / CSV)
                </button>
                <button @click="handleBulkImport" :disabled="isImporting || !selectedFile" class="w-full bg-emerald-500 hover:bg-emerald-400 text-white p-5 rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20 transition-all disabled:opacity-50">
                  {{ isImporting ? 'Importation...' : "Lancer l'importation" }}
                </button>
              </div>

            </div>
          </div>

        </div>
      </div>

    </main>

    <!-- NOTIFICATION POPUP (Simple toast replacement) -->
    <div v-if="toast" class="fixed bottom-10 right-10 z-[100] animate-in slide-in-from-right-10 duration-300">
      <div :class="toast.type === 'error' ? 'bg-red-500 border-red-400' : 'bg-emerald-500 border-emerald-400'" 
           class="px-8 py-5 rounded-2xl border-2 text-white shadow-2xl flex items-center gap-4">
        <span class="font-black text-sm uppercase tracking-widest">{{ toast.message }}</span>
        <button @click="toast = null" class="opacity-50 hover:opacity-100">✕</button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../auth'
import apiClient from '../api'

const auth = useAuth()
const router = useRouter()
const activeTab = ref('dashboard')
const isLoading = ref(true)
const currentTime = ref('')
const currentDate = ref('')
const toast = ref(null)

const tabs = [
  { id: 'dashboard', label: 'Tableau de bord' },
  { id: 'users', label: 'Utilisateurs' },
  { id: 'moderation', label: 'Modération' },
  { id: 'invitations', label: 'Invitations' },
]


// Stats & Dashboard
const dashboardStats = ref([
  { label: 'Utilisateurs', value: '—', sub: 'Total membres', color: 'text-sky-400', glow: 'bg-sky-500' },
  { label: 'Offres', value: '—', sub: 'Jobs & Stages', color: 'text-blue-400', glow: 'bg-blue-500' },
  { label: 'Événements', value: '—', sub: 'Programmés', color: 'text-indigo-400', glow: 'bg-indigo-500' },
  { label: 'Timeline', value: '—', sub: 'Posts partagés', color: 'text-emerald-400', glow: 'bg-emerald-500' },
])
const recentUsers = ref([])

// Users Management
const allUsers = ref([])
const userPage = ref(1)
const searchQuery = ref('')
const hasMoreUsers = ref(true)

// Moderation
const moderatedJobs = ref([])
const moderatedEvents = ref([])
const moderatedThreads = ref([])
const moderatedPosts = ref([])
const workGroups = ref([])


// Formulaires Invitations
const inviteForm = ref({
  name: '',
  email: '',
  promotion_year: new Date().getFullYear(),
  work_group_id: null
})
const selectedFile = ref(null)
const importGroupId = ref(null)
const isInviting = ref(false)
const isImporting = ref(false)


const showToast = (message, type = 'success') => {
  toast.value = { message, type }
  setTimeout(() => { toast.value = null }, 3000)
}

const updateClock = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
  currentDate.value = now.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })
}

const fetchStats = async () => {
  try {
    const { data } = await apiClient.get('/admin/stats')
    dashboardStats.value[0].value = data.users_total
    dashboardStats.value[1].value = data.jobs_total
    dashboardStats.value[2].value = data.events_total
    dashboardStats.value[3].value = data.posts_total
  } catch (e) {
    console.error('Stats fetch error:', e)
  }
}

const fetchUsers = async () => {
  try {
    const { data } = await apiClient.get(`/admin/users?page=${userPage.value}&search=${searchQuery.value}`)
    allUsers.value = data.data
    hasMoreUsers.value = data.next_page_url !== null
    if (userPage.value === 1) recentUsers.value = data.data.slice(0, 5)
  } catch (e) {
    showToast('Erreur lors du chargement des utilisateurs', 'error')
  }
}

const fetchModerationContent = async () => {
  try {
    const [j, e, t, p] = await Promise.all([
      apiClient.get('/job-postings'),
      apiClient.get('/events'),
      apiClient.get('/forum-threads'),
      apiClient.get('/posts')
    ])
    moderatedJobs.value = j.data.data || j.data
    moderatedEvents.value = e.data.data || e.data
    moderatedThreads.value = t.data.data || t.data
    moderatedPosts.value = p.data.data || p.data
  } catch (err) {
    console.error('Moderation load error:', err)
  }
}


const handleRoleUpdate = async (user, newRole) => {
  try {
    await apiClient.put(`/admin/users/${user.id}/role`, { role: newRole })
    user.role = newRole
    showToast(`Rôle de ${user.name} mis à jour : ${newRole}`)
  } catch (e) {
    showToast('Erreur lors de la mise à jour du rôle', 'error')
  }
}

const confirmDeleteUser = async (user) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer COMPLÈTEMENT ${user.name} ? Cette action est irréversible.`)) {
    try {
      await apiClient.delete(`/admin/users/${user.id}`)
      allUsers.value = allUsers.value.filter(u => u.id !== user.id)
      showToast(`${user.name} a été supprimé.`)
      fetchStats()
    } catch (e) {
      showToast(e.response?.data?.message || 'Erreur lors de la suppression', 'error')
    }
  }
}

const deleteItem = async (type, id) => {
  if (!confirm('Supprimer ce contenu définitivement ?')) return
  try {
    // We use the admin moderation endpoints
    let endpoint = `/admin/${type}/${id}`
    // Special case for forum threads since the route name in controllers might vary, 
    // but we defined /admin/forum-threads/{id} in api.php
    await apiClient.delete(endpoint)
    
    if (type === 'jobs') moderatedJobs.value = moderatedJobs.value.filter(i => i.id !== id)
    if (type === 'events') moderatedEvents.value = moderatedEvents.value.filter(i => i.id !== id)
    if (type === 'forum-threads') moderatedThreads.value = moderatedThreads.value.filter(i => i.id !== id)
    if (type === 'posts') moderatedPosts.value = moderatedPosts.value.filter(i => i.id !== id)
    
    showToast('Contenu supprimé par modération.')
    fetchStats()
  } catch (e) {
    showToast('Erreur lors de la modération.', 'error')
  }
}

const paginatedUsers = computed(() => allUsers.value)

watch([userPage, searchQuery], () => fetchUsers())

let clockInterval = null

const handleLogout = async () => {
  await auth.logout()
  router.push({ name: 'landing' })
}

const fetchWorkGroups = async () => {
  try {
    const { data } = await apiClient.get('/work-groups')
    workGroups.value = data
  } catch (error) {
    console.error('WorkGroups fetch error:', error)
  }
}

const handleSingleInvite = async () => {
  isInviting.value = true
  try {
    const response = await apiClient.post('/admin/invite', inviteForm.value)
    showToast(response.data.message)
    inviteForm.value.name = ''
    inviteForm.value.email = ''
    inviteForm.value.work_group_id = null
  } catch (error) {
    showToast(error.response?.data?.message || "Erreur d'invitation", 'error')
  } finally {
    isInviting.value = false
  }
}

const handleFileSelect = (e) => {
  selectedFile.value = e.target.files[0]
}

const handleBulkImport = async () => {
  if (!selectedFile.value) return
  isImporting.value = true
  
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  if (importGroupId.value) formData.append('work_group_id', importGroupId.value)

  try {
    const response = await apiClient.post('/admin/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    showToast(response.data.message)
    selectedFile.value = null
  } catch (error) {
    showToast("Erreur lors de l'importation", 'error')
  } finally {
    isImporting.value = false
  }
}

const downloadTemplate = () => {
  // UTF-8 BOM pour qu'Excel reconnaisse les accents immédiatement
  const BOM = "\uFEFF";
  const header = "email;name;promotion_year\n";
  const rows = [
    "azo@gmail.com;Azo Senghor;2024",
    "marie.sene@yahoo.fr;Marie Sene;2022"
  ].join("\n");


  
  const content = BOM + header + rows;
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'modele_import_isi_alumni.csv'
  a.click()
  URL.revokeObjectURL(url)
}



onMounted(async () => {
  updateClock()
  clockInterval = setInterval(updateClock, 1000)
  isLoading.value = true
  await Promise.all([fetchStats(), fetchUsers(), fetchModerationContent(), fetchWorkGroups()])
  isLoading.value = false
})


onUnmounted(() => clearInterval(clockInterval))
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(56, 189, 248, 0.5);
}
</style>
