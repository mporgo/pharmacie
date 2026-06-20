<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">💊 Médicaments</h1>
      <button
        v-if="auth.hasPermission('medicaments.create')"
        @click="ouvrirModal()"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition"
      >
        + Ajouter
      </button>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3">
      <input
        v-model="filtres.search"
        @input="charger"
        placeholder="Rechercher..."
        class="border rounded-lg px-3 py-2 text-sm flex-1 min-w-48 focus:outline-none focus:ring-2 focus:ring-blue-400"
      />
      <select
        v-model="filtres.categorie_id"
        @change="charger"
        class="border rounded-lg px-3 py-2 text-sm focus:outline-none"
      >
        <option value="">Toutes les catégories</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
      </select>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-blue-900 text-white">
          <tr>
            <th class="px-4 py-3 text-left">Médicament</th>
            <th class="px-4 py-3 text-left">Catégorie</th>
            <th class="px-4 py-3 text-left">Forme</th>
            <th class="px-4 py-3 text-right">Prix vente</th>
            <th class="px-4 py-3 text-center">Stock</th>
            <th class="px-4 py-3 text-center">Expiration</th>
            <th class="px-4 py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="text-center py-10 text-gray-400">Chargement...</td>
          </tr>
          <tr
            v-for="med in medicaments"
            :key="med.id"
            :class="['border-b hover:bg-gray-50 transition', rowClass(med)]"
          >
            <td class="px-4 py-3">
              <p class="font-semibold text-gray-800">{{ med.nom }}</p>
              <p class="text-xs text-gray-400">{{ med.dosage }}</p>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ med.categorie?.nom }}</td>
            <td class="px-4 py-3 text-gray-600">{{ med.forme || '—' }}</td>
            <td class="px-4 py-3 text-right font-bold text-blue-700">
              {{ med.prix_vente.toLocaleString() }} FCFA
            </td>
            <td class="px-4 py-3 text-center">
              <span :class="['px-2 py-1 rounded-full text-xs font-semibold', stockBadge(med)]">
                {{ med.stock_actuel }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <span :class="['text-xs', expirationClass(med.date_expiration)]">
                {{ med.date_expiration ? formatDate(med.date_expiration) : '—' }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex justify-center gap-2">
                <button
                  v-if="auth.hasPermission('medicaments.edit')"
                  @click="ouvrirModal(med)"
                  class="text-blue-500 hover:text-blue-700 text-lg"
                  title="Modifier"
                >✏️</button>
                <button
                  v-if="auth.hasPermission('medicaments.delete')"
                  @click="supprimer(med)"
                  class="text-red-400 hover:text-red-600 text-lg"
                  title="Désactiver"
                >🗑️</button>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && !medicaments.length">
            <td colspan="7" class="text-center py-10 text-gray-400">Aucun médicament trouvé.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-4 py-3 border-t text-sm text-gray-500">
        <span>{{ pagination.total || 0 }} résultats</span>
        <div class="flex gap-2">
          <button
            :disabled="pagination.current_page <= 1"
            @click="changerPage(pagination.current_page - 1)"
            class="px-3 py-1 border rounded disabled:opacity-40 hover:bg-gray-100"
          >‹ Préc.</button>
          <span class="px-3 py-1">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
          <button
            :disabled="pagination.current_page >= pagination.last_page"
            @click="changerPage(pagination.current_page + 1)"
            class="px-3 py-1 border rounded disabled:opacity-40 hover:bg-gray-100"
          >Suiv. ›</button>
        </div>
      </div>
    </div>

    <!-- Modal Ajout/Édition -->
    <div v-if="modal.visible"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-screen overflow-y-auto">

        <div class="bg-blue-900 text-white px-6 py-4 rounded-t-2xl flex justify-between items-center">
          <h3 class="font-bold text-lg">
            {{ modal.medicament ? 'Modifier le médicament' : 'Nouveau médicament' }}
          </h3>
          <button @click="fermerModal" class="text-blue-200 hover:text-white text-xl">✕</button>
        </div>

        <form @submit.prevent="sauvegarder" class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">

          <div class="sm:col-span-2">
            <label class="label">Nom du médicament *</label>
            <input v-model="form.nom" required class="input" placeholder="Ex : Paracétamol" />
          </div>

          <div>
            <label class="label">Dosage</label>
            <input v-model="form.dosage" class="input" placeholder="Ex : 500mg" />
          </div>

          <div>
            <label class="label">Forme pharmaceutique</label>
            <select v-model="form.forme" class="input">
              <option value="">— Choisir —</option>
              <option>Comprimé</option>
              <option>Gélule</option>
              <option>Sirop</option>
              <option>Injectable</option>
              <option>Pommade</option>
              <option>Gouttes</option>
              <option>Suppositoire</option>
              <option>Sachet</option>
            </select>
          </div>

          <div>
            <label class="label">Catégorie *</label>
            <select v-model="form.categorie_id" required class="input">
              <option value="">— Choisir —</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
            </select>
          </div>

          <div>
            <label class="label">Fournisseur</label>
            <select v-model="form.fournisseur_id" class="input">
              <option value="">— Aucun —</option>
              <option v-for="f in fournisseurs" :key="f.id" :value="f.id">{{ f.nom }}</option>
            </select>
          </div>

          <div>
            <label class="label">Prix d'achat (FCFA) *</label>
            <input v-model.number="form.prix_achat" type="number" min="0" required class="input" />
          </div>

          <div>
            <label class="label">Prix de vente (FCFA) *</label>
            <input v-model.number="form.prix_vente" type="number" min="0" required class="input" />
          </div>

          <div>
            <label class="label">Stock actuel *</label>
            <input v-model.number="form.stock_actuel" type="number" min="0" required class="input" />
          </div>

          <div>
            <label class="label">Stock minimum (alerte)</label>
            <input v-model.number="form.stock_minimum" type="number" min="0" class="input" />
          </div>

          <div>
            <label class="label">Code-barres</label>
            <input v-model="form.code_barre" class="input" placeholder="Ex : 3400935055875" />
          </div>

          <div>
            <label class="label">Date d'expiration</label>
            <input v-model="form.date_expiration" type="date" class="input" />
          </div>

          <div v-if="erreurForm" class="sm:col-span-2 bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm">
            {{ erreurForm }}
          </div>

          <div class="sm:col-span-2 flex justify-end gap-3 mt-2">
            <button type="button" @click="fermerModal"
                    class="px-5 py-2 border rounded-lg text-gray-600 hover:bg-gray-100">
              Annuler
            </button>
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
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'
import Swal from 'sweetalert2'

const auth = useAuthStore()

// State
const medicaments  = ref([])
const categories   = ref([])
const fournisseurs = ref([])
const loading      = ref(false)
const saving       = ref(false)
const erreurForm   = ref('')
const pagination   = ref({ current_page: 1, last_page: 1, total: 0 })
const filtres      = reactive({ search: '', categorie_id: '' })

const modal = reactive({ visible: false, medicament: null })
const form  = reactive({
  nom: '', dosage: '', forme: '', categorie_id: '', fournisseur_id: '',
  prix_achat: 0, prix_vente: 0, stock_actuel: 0, stock_minimum: 5,
  code_barre: '', date_expiration: ''
})

// Chargement données
async function charger(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/medicaments', {
      params: { ...filtres, page }
    })
    medicaments.value = data.data
    pagination.value  = {
      current_page: data.current_page,
      last_page:    data.last_page,
      total:        data.total,
    }
  } finally {
    loading.value = false
  }
}

async function chargerSelecteurs() {
  const [cats, fours] = await Promise.all([
    api.get('/categories'),
    api.get('/fournisseurs'),
  ])
  categories.value   = cats.data.data || cats.data
  fournisseurs.value = fours.data.data || fours.data
}

function changerPage(page) { charger(page) }

// Modal
function ouvrirModal(med = null) {
  modal.medicament = med
  modal.visible    = true
  erreurForm.value = ''

  if (med) {
    Object.assign(form, {
      nom:             med.nom,
      dosage:          med.dosage || '',
      forme:           med.forme || '',
      categorie_id:    med.categorie_id,
      fournisseur_id:  med.fournisseur_id || '',
      prix_achat:      med.prix_achat,
      prix_vente:      med.prix_vente,
      stock_actuel:    med.stock_actuel,
      stock_minimum:   med.stock_minimum,
      code_barre:      med.code_barre || '',
      date_expiration: med.date_expiration?.substring(0, 10) || '',
    })
  } else {
    Object.assign(form, {
      nom: '', dosage: '', forme: '', categorie_id: '', fournisseur_id: '',
      prix_achat: 0, prix_vente: 0, stock_actuel: 0, stock_minimum: 5,
      code_barre: '', date_expiration: ''
    })
  }
}

function fermerModal() { modal.visible = false }

async function sauvegarder() {
  saving.value     = true
  erreurForm.value = ''
  try {
    const payload = { ...form }

    // ✅ Supprimer les champs vides pour éviter erreurs validation Laravel
    if (!payload.fournisseur_id)  delete payload.fournisseur_id
    if (!payload.date_expiration) delete payload.date_expiration
    if (!payload.code_barre)      delete payload.code_barre
    if (!payload.dosage)          delete payload.dosage
    if (!payload.forme)           delete payload.forme

    if (modal.medicament) {
      await api.put(`/medicaments/${modal.medicament.id}`, payload)
    } else {
      await api.post('/medicaments', payload)
    }

    fermerModal()
    charger()
    Swal.fire('✅ Succès', 'Médicament enregistré.', 'success')
  } catch (e) {
    erreurForm.value =
      e.response?.data?.message ||
      Object.values(e.response?.data?.errors || {}).flat().join(' ') ||
      'Erreur lors de la sauvegarde.'
  } finally {
    saving.value = false
  }
}

async function supprimer(med) {
  const result = await Swal.fire({
    title: 'Désactiver ce médicament ?',
    text:  med.nom,
    icon:  'warning',
    showCancelButton: true,
    confirmButtonText: 'Oui, désactiver',
    cancelButtonText:  'Annuler',
    confirmButtonColor: '#dc2626',
  })
  if (result.isConfirmed) {
    await api.delete(`/medicaments/${med.id}`)
    charger()
  }
}

// Helpers UI
function rowClass(med) {
  if (med.date_expiration && new Date(med.date_expiration) < new Date()) return 'bg-red-50'
  if (med.stock_actuel <= med.stock_minimum) return 'bg-orange-50'
  return ''
}

function stockBadge(med) {
  if (med.stock_actuel === 0)                    return 'bg-red-100 text-red-700'
  if (med.stock_actuel <= med.stock_minimum)     return 'bg-orange-100 text-orange-700'
  return 'bg-green-100 text-green-700'
}

function expirationClass(date) {
  if (!date) return 'text-gray-400'
  const diff = (new Date(date) - new Date()) / (1000 * 60 * 60 * 24)
  if (diff < 0)  return 'text-red-600 font-bold'
  if (diff < 30) return 'text-orange-500 font-semibold'
  return 'text-gray-600'
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR')
}

onMounted(() => { charger(); chargerSelecteurs() })
</script>

<style scoped>
@reference "tailwindcss";
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400; }
</style>