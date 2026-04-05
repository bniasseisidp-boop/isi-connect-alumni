// src/api.js
import axios from 'axios';

// 1. On crée une "instance" d'axios
const baseURL = 'https://isi-connect-api.onrender.com';
const apiClient = axios.create({
  baseURL: `${baseURL}/api`, 
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },
  timeout: 120000
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