<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">👥 Utilisateurs</h1>
      <button
        @click="ouvrirModal()"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium"
      >
        + Nouvel utilisateur
      </button>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-blue-900 text-white">
          <tr>
            <th class="px-4 py-3 text-left">Nom</th>
            <th class="px-4 py-3 text-left">Email</th>
            <th class="px-4 py-3 text-center">Rôle</th>
            <th class="px-4 py-3 text-center">Statut</th>
            <th class="px-4 py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id"
              class="border-b hover:bg-gray-50 transition">
            <td class="px-4 py-3 font-semibold text-gray-800">{{ user.name }}</td>
            <td class="px-4 py-3 text-gray-500">{{ user.email }}</td>
            <td class="px-4 py-3 text-center">
              <span
                v-for="role in user.roles"
                :key="role"
                :class="['px-2 py-1 rounded-full text-xs font-bold mr-1', roleBadge(role)]"
              >{{ role }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <span :class="['px-2 py-1 rounded-full text-xs font-semibold',
                             user.actif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                {{ user.actif ? 'Actif' : 'Désactivé' }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex justify-center gap-2">
                <button @click="ouvrirModal(user)" class="text-blue-500 hover:text-blue-700 text-lg">✏️</button>
                <button @click="toggleStatut(user)"
                        :class="user.actif ? 'text-orange-400 hover:text-orange-600' : 'text-green-400 hover:text-green-600'"
                        class="text-lg">
                  {{ user.actif ? '🔒' : '🔓' }}
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!users.length">
            <td colspan="5" class="text-center py-10 text-gray-400">Aucun utilisateur</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="modal.visible"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

        <div class="bg-blue-900 text-white px-6 py-4 rounded-t-2xl flex justify-between">
          <h3 class="font-bold">{{ modal.user ? 'Modifier utilisateur' : 'Nouvel utilisateur' }}</h3>
          <button @click="modal.visible = false" class="text-blue-200 hover:text-white">✕</button>
        </div>

        <form @submit.prevent="sauvegarder" class="p-6 space-y-4">
          <div>
            <label class="label">Nom complet *</label>
            <input v-model="form.name" required class="input" placeholder="Ex : Jean Dupont" />
          </div>
          <div>
            <label class="label">Email *</label>
            <input v-model="form.email" type="email" required class="input" />
          </div>
          <div>
            <label class="label">{{ modal.user ? 'Nouveau mot de passe (laisser vide = inchangé)' : 'Mot de passe *' }}</label>
            <input
              v-model="form.password"
              type="password"
              :required="!modal.user"
              class="input"
              placeholder="Min. 8 caractères"
            />
          </div>
          <div>
            <label class="label">Rôle *</label>
            <select v-model="form.role" required class="input">
              <option value="">— Choisir —</option>
              <option value="admin">Administrateur</option>
              <option value="pharmacien">Pharmacien</option>
              <option value="caissier">Caissier</option>
            </select>
          </div>

          <div v-if="erreur" class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm">
            {{ erreur }}
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="modal.visible = false"
                    class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100">Annuler</button>
            <button type="submit" :disabled="saving"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold disabled:opacity-50">
              {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import Swal from 'sweetalert2'

const users  = ref([])
const saving = ref(false)
const erreur = ref('')

const modal = reactive({ visible: false, user: null })
const form  = reactive({ name: '', email: '', password: '', role: '' })

async function charger() {
  const { data } = await api.get('/users')
  users.value = data.data || data
}

function ouvrirModal(user = null) {
  modal.user    = user
  modal.visible = true
  erreur.value  = ''
  Object.assign(form, {
    name:     user?.name || '',
    email:    user?.email || '',
    password: '',
    role:     user?.roles?.[0] || '',
  })
}

async function sauvegarder() {
  saving.value = true
  erreur.value = ''
  try {
    const payload = { ...form }
    if (!payload.password) delete payload.password

    if (modal.user) {
      await api.put(`/users/${modal.user.id}`, payload)
    } else {
      await api.post('/users', payload)
    }

    modal.visible = false
    charger()
    Swal.fire('✅ Succès', 'Utilisateur enregistré.', 'success')
  } catch (e) {
    erreur.value = e.response?.data?.message ||
      Object.values(e.response?.data?.errors || {}).flat().join(' ') ||
      'Erreur.'
  } finally {
    saving.value = false
  }
}

async function toggleStatut(user) {
  const action = user.actif ? 'désactiver' : 'réactiver'
  const result = await Swal.fire({
    title: `${action.charAt(0).toUpperCase() + action.slice(1)} ${user.name} ?`,
    icon:  'question',
    showCancelButton: true,
    confirmButtonText: 'Confirmer',
    cancelButtonText:  'Annuler',
  })
  if (result.isConfirmed) {
    await api.put(`/users/${user.id}`, { actif: !user.actif })
    charger()
  }
}

function roleBadge(role) {
  return {
    admin:       'bg-purple-100 text-purple-700',
    pharmacien:  'bg-blue-100 text-blue-700',
    caissier:    'bg-green-100 text-green-700',
  }[role] || 'bg-gray-100 text-gray-700'
}

onMounted(charger)
</script>

<style scoped>
@reference "tailwindcss";
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400; }
</style>