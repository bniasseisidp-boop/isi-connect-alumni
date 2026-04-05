// src/auth.js
import { ref, computed, readonly } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from './api';

const user = ref(null);
const token = ref(localStorage.getItem('token') || null);
const mustChangePassword = ref(localStorage.getItem('must_change_password') === 'true');


// L'état est-il connecté ?
const isAuthenticated = computed(() => !!token.value && !!user.value);

function setAuthData(userData, userToken, mcp = false) {
  user.value = userData;
  token.value = userToken;
  mustChangePassword.value = mcp;
  localStorage.setItem('token', userToken);
  localStorage.setItem('must_change_password', mcp ? 'true' : 'false');
}


function clearAuthData() {
  user.value = null;
  token.value = null;
  mustChangePassword.value = false;
  localStorage.removeItem('token');
  localStorage.removeItem('must_change_password');
}


// Fonction pour se connecter : Retourne true si succès
async function login(email, password) {
  try {
    const response = await apiClient.post('/login', { email, password });
    const userData = response.data.user;
    const userToken = response.data.access_token;
    const mcp = response.data.must_change_password || false;
    setAuthData(userData, userToken, mcp);
    return true;

  } catch (error) {
    console.error("Erreur login:", error);
    throw error; // On laisse la vue gérer l'erreur (Hors ligne / Incorrect)
  }
}

async function register(name, email, promotion_year, password, password_confirmation) {
  await apiClient.post('/register', {
    name,
    email,
    promotion_year,
    password,
    password_confirmation
  });
  return await login(email, password);
}

async function forgotPassword(email) {
  return await apiClient.post('/forgot-password', { email });
}

async function logout() {
  try {
    await apiClient.post('/logout');
  } catch (error) {
    console.error("Erreur logout:", error);
  } finally {
    clearAuthData();
  }
}

async function fetchUser() {
  if (token.value && !user.value) {
    try {
      const response = await apiClient.get('/user');
      user.value = response.data;
      mustChangePassword.value = !!response.data.must_change_password;
      localStorage.setItem('must_change_password', mustChangePassword.value ? 'true' : 'false');
    } catch (error) {

      clearAuthData();
    }
  }
}

function setUser(userData) {
  user.value = userData;
}

function updateMustChangePassword(val) {
  mustChangePassword.value = val;
  localStorage.setItem('must_change_password', val ? 'true' : 'false');
}


export function useAuth() {
  return {
    user: readonly(user),
    token: readonly(token),
    isAuthenticated,
    mustChangePassword: readonly(mustChangePassword),
    login,
    register,
    logout,
    fetchUser,
    setUser,
    updateMustChangePassword,
    forgotPassword
  };
}