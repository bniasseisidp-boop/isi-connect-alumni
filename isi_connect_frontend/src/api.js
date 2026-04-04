// src/api.js
import axios from 'axios';

// 1. On crée une "instance" d'axios
const apiClient = axios.create({
  baseURL: 'http://localhost:8001/api', 
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

// Base URL pour les images stockées localement
export const STORAGE_URL = 'http://localhost:8001/storage/';

// 2. Intercepteur pour le Token
// On récupère le token DIRECTEMENT depuis le localStorage 
// Cela évite l'import circulaire de auth.js qui causait l'erreur "HORS LIGNE"
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default apiClient;