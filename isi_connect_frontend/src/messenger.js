// src/messenger.js
import { ref, reactive } from 'vue'

// État réactif global pour la messagerie
export const messengerState = reactive({
  isOpen: false,            // Si le volet de messagerie est ouvert
  activeChat: null,         // L'utilisateur avec qui on discute actuellement { id, name, photo }
  conversations: [],        // Liste des conversations récentes
  unreadCount: 0            // Nombre de messages non lus
})

// Fonction pour ouvrir un chat avec quelqu'un
export const openChatWith = (user) => {
  messengerState.activeChat = user
  messengerState.isOpen = true
}

// Fonction pour fermer ou basculer le volet
export const toggleMessenger = () => {
  messengerState.isOpen = !messengerState.isOpen
}
