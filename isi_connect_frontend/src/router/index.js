// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../auth'
import ChangePasswordView from '../views/ChangePasswordView.vue'


// On importe nos composants
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import AppLayout from '../layouts/AppLayout.vue' // <-- NOTRE COQUILLE

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // --- Routes pour "Invités" (non connectés) ---
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresAuth: false } 
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { requiresAuth: false }
    },
    // --- Route d'Accueil (Vitrine) ---
    {
      path: '/landing',
      name: 'landing',
      component: () => import('../views/LandingView.vue'),
      meta: { requiresAuth: false }
    },
    
    // --- Routes pour "Authentifiés" (connectés) ---
    {
      path: '/',
      component: AppLayout, 
      meta: { requiresAuth: true },
      children: [
        // La redirection de la racine est gérée par le Navigation Guard
        
        {
          path: '/dashboard',
          name: 'dashboard',
          component: DashboardView
        },
        {
          path: '/actualites',
          name: 'actualites',
          component: () => import('../views/TimelineView.vue')
        },
        {
          path: '/annuaire',
          name: 'annuaire',
          component: () => import('../views/AnnuaireView.vue')
        },
        {
          path: '/offres',
          name: 'offres',
          component: () => import('../views/OffresView.vue')
        },
        {
          path: '/evenements',
          name: 'evenements',
          component: () => import('../views/EventsView.vue')
        },
        {
          path: '/forum',
          name: 'forum',
          component: () => import('../views/ForumView.vue')
        },
        {
          path: '/groupes',
          name: 'groupes',
          component: () => import('../views/WorkGroupsView.vue')
        },
        {
          path: '/forum/:id',
          name: 'forum-thread',
          component: () => import('../views/ForumThreadView.vue') 
        },
        {
          path: '/profil',
          name: 'profil',
          component: () => import('../views/ProfilView.vue')
        },
        {
          path: '/profil/:id', 
          name: 'profil-public',
          component: () => import('../views/ProfilPublicView.vue') 
        },
      ]
    },
    // --- Route Admin ---
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/AdminView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/change-password',
      name: 'change-password',
      component: ChangePasswordView,
      meta: { requiresAuth: true }
    }
  ]
})


// LE GARDE DE SÉCURITÉ
router.beforeEach(async (to, from, next) => {
  const auth = useAuth();

  if (auth.token.value && !auth.isAuthenticated.value) {
    try {
      await auth.fetchUser();
    } catch (e) {
      // Le token est invalide
    }
  }
  
  const requiresAuth = to.meta.requiresAuth;
  const isAuthenticated = auth.isAuthenticated.value;
  const isMustChangePassword = auth.mustChangePassword.value;

  // Force redirection to change-password if flag is set
  if (isAuthenticated && isMustChangePassword && to.name !== 'change-password' && to.name !== 'logout') {
    return next({ name: 'change-password' });
  }

  // Prevent access to change-password if not required
  if (isAuthenticated && !isMustChangePassword && to.name === 'change-password') {
    return next({ name: 'dashboard' });
  }

  // Gestion pointue de la racine '/'

  if (to.path === '/') {
    if (isAuthenticated) {
      const userRole = auth.user.value?.role;
      if (userRole === 'admin') {
        return next({ name: 'admin' });
      }
      return next({ name: 'dashboard' });
    } else {
      return next({ name: 'landing' });
    }
  }

  if (requiresAuth && !isAuthenticated) {
    next({ name: 'login' });
  } else if (to.meta.requiresAdmin && auth.user.value?.role !== 'admin') {
    next({ name: 'dashboard' }); // Non-admins ne peuvent pas aller sur /admin
  } else if (!requiresAuth && isAuthenticated && (to.name === 'login' || to.name === 'register' || to.name === 'landing')) {
    const userRole = auth.user.value?.role;
    next({ name: userRole === 'admin' ? 'admin' : 'dashboard' });
  } else {
    next();
  }
});

export default router