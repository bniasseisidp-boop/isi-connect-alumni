// src/main.js

import './assets/main.css'
import "tailwindcss";

import { createApp } from 'vue'
import App from './App.vue'
// 1. On importe notre routeur
import router from './router' 

const app = createApp(App)

// 2. On "branche" le routeur à l'application
app.use(router) 

// 3. On lance l'application
app.mount('#app')