// src/api.js
import axios from 'axios';

// 1. On crée une "instance" d'axios
const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8001';
const apiClient = axios.create({
  baseURL: `${baseURL}/api`, 
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },
  timeout: 15000
});

// Base URL pour les images stockées localement
export const STORAGE_URL = `${baseURL}/storage/`;

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