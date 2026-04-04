<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <h2 class="text-3xl font-extrabold text-slate-900">
          Changement de mot de passe
        </h2>
        <p class="mt-2 text-sm text-slate-600">
          Par mesure de sécurité, vous devez définir un nouveau mot de passe pour continuer.
        </p>
      </div>

      <div class="mt-8 mx-auto w-full max-w-md px-4 sm:px-0">
        <div class="bg-white/80 backdrop-blur-xl py-8 px-6 shadow-2xl rounded-[2.5rem] border border-slate-200/50">
          <form class="space-y-6" @submit.prevent="handleSubmit">

            <div>
              <label for="password" class="block text-sm font-medium text-slate-700">
                Nouveau mot de passe
              </label>
              <div class="mt-1">
                <input
                  id="password"
                  v-model="form.password"
                  name="password"
                  type="password"
                  required
                  class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                />
              </div>
            </div>

            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-slate-700">
                Confirmer le mot de passe
              </label>
              <div class="mt-1">
                <input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  name="password_confirmation"
                  type="password"
                  required
                  class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                />
              </div>
            </div>

            <div v-if="error" class="text-red-500 text-sm bg-red-50 p-3 rounded-lg border border-red-100">
              {{ error }}
            </div>

            <div>
              <button
                type="submit"
                :disabled="loading"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 disabled:opacity-50 transition-all duration-200"
              >
                <span v-if="loading">Mise à jour...</span>
                <span v-else>Mettre à jour mon mot de passe</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../auth'
import apiClient from '../api'

const router = useRouter()
const auth = useAuth()

const form = reactive({
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const error = ref(null)

const handleSubmit = async () => {
  if (form.password !== form.password_confirmation) {
    error.value = "Les mots de passe ne correspondent pas."
    return
  }

  loading.value = true
  error.value = null

  try {
    const response = await apiClient.post('/user/update-password', form)
    
    // Mettre à jour l'état local via useAuth
    auth.updateMustChangePassword(false)
    
    // On redirige vers le dashboard
    router.push({ name: 'dashboard' })
  } catch (err) {
    error.value = err.response?.data?.message || "Une erreur est survenue lors du changement de mot de passe."
  } finally {
    loading.value = false
  }
}
</script>

