<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">📦 Gestion du Stock</h1>

    <!-- Onglets -->
    <div class="flex gap-2 mb-6">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="ongletActif = tab.key"
        :class="['px-4 py-2 rounded-lg text-sm font-medium transition',
                 ongletActif === tab.key
                   ? 'bg-blue-600 text-white shadow'
                   : 'bg-white text-gray-600 hover:bg-gray-100 border']"
      >
        {{ tab.icon }} {{ tab.label }}
      </button>
    </div>

    <!-- Onglet : État du stock -->
    <div v-if="ongletActif === 'stock'">
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-4 py-3 border-b flex gap-3">
          <input
            v-model="searchStock"
            @input="chargerStock"
            placeholder="Rechercher un médicament..."
            class="border rounded-lg px-3 py-2 text-sm flex-1 focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>

        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600">
            <tr>
              <th class="px-4 py-3 text-left">Médicament</th>
              <th class="px-4 py-3 text-center">Stock actuel</th>
              <th class="px-4 py-3 text-center">Stock min.</th>
              <th class="px-4 py-3 text-center">Statut</th>
              <th class="px-4 py-3 text-center">Expiration</th>
              <th class="px-4 py-3 text-center">Ajustement</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="med in stocks" :key="med.id"
                :class="['border-b hover:bg-gray-50', rowClass(med)]">
              <td class="px-4 py-3">
                <p class="font-semibold text-gray-800">{{ med.nom }}</p>
                <p class="text-xs text-gray-400">{{ med.categorie?.nom }}</p>
              </td>
              <td class="px-4 py-3 text-center font-bold text-lg">{{ med.stock_actuel }}</td>
              <td class="px-4 py-3 text-center text-gray-400">{{ med.stock_minimum }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-1 rounded-full text-xs font-semibold', statutStock(med).class]">
                  {{ statutStock(med).label }}
                </span>
              </td>
              <td class="px-4 py-3 text-center text-xs" :class="expirationClass(med.date_expiration)">
                {{ med.date_expiration ? formatDate(med.date_expiration) : '—' }}
              </td>
              <td class="px-4 py-3 text-center">
                <button
                  @click="ouvrirMouvement(med)"
                  class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-lg text-xs font-medium transition"
                >
                  ± Ajuster
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Onglet : Mouvements -->
    <div v-if="ongletActif === 'mouvements'">
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600">
            <tr>
              <th class="px-4 py-3 text-left">Médicament</th>
              <th class="px-4 py-3 text-center">Type</th>
              <th class="px-4 py-3 text-center">Quantité</th>
              <th class="px-4 py-3 text-center">Avant</th>
              <th class="px-4 py-3 text-center">Après</th>
              <th class="px-4 py-3 text-left">Motif</th>
              <th class="px-4 py-3 text-left">Opérateur</th>
              <th class="px-4 py-3 text-left">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in mouvements" :key="m.id" class="border-b hover:bg-gray-50">
              <td class="px-4 py-3 font-semibold">{{ m.medicament?.nom }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-1 rounded-full text-xs font-bold', typeBadge(m.type)]">
                  {{ m.type }}
                </span>
              </td>
              <td class="px-4 py-3 text-center font-bold">{{ m.quantite }}</td>
              <td class="px-4 py-3 text-center text-gray-400">{{ m.stock_avant }}</td>
              <td class="px-4 py-3 text-center text-gray-700 font-semibold">{{ m.stock_apres }}</td>
              <td class="px-4 py-3 text-gray-500">{{ m.motif || '—' }}</td>
              <td class="px-4 py-3 text-gray-600">{{ m.user?.name }}</td>
              <td class="px-4 py-3 text-gray-400 text-xs">{{ formatDateHeure(m.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Mouvement de stock -->
    <div v-if="mouvementModal.visible"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

        <div class="bg-blue-900 text-white px-6 py-4 rounded-t-2xl flex justify-between">
          <h3 class="font-bold">Ajustement de stock — {{ mouvementModal.medicament?.nom }}</h3>
          <button @click="mouvementModal.visible = false" class="text-blue-200 hover:text-white">✕</button>
        </div>

        <form @submit.prevent="enregistrerMouvement" class="p-6 space-y-4">

          <div class="bg-gray-50 rounded-lg p-3 text-center">
            <p class="text-sm text-gray-500">Stock actuel</p>
            <p class="text-3xl font-bold text-blue-700">{{ mouvementModal.medicament?.stock_actuel }}</p>
          </div>

          <div>
            <label class="label">Type de mouvement *</label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="t in ['entree', 'sortie', 'inventaire']"
                :key="t"
                type="button"
                @click="mouvementForm.type = t"
                :class="['py-2 rounded-lg border text-sm font-medium capitalize transition',
                         mouvementForm.type === t
                           ? 'bg-blue-600 text-white border-blue-600'
                           : 'bg-white text-gray-600 hover:bg-gray-50']"
              >
                {{ t === 'entree' ? '📥 Entrée' : t === 'sortie' ? '📤 Sortie' : '🔄 Inventaire' }}
              </button>
            </div>
          </div>

          <div>
            <label class="label">
              {{ mouvementForm.type === 'inventaire' ? 'Nouveau stock total *' : 'Quantité *' }}
            </label>
            <input
              v-model.number="mouvementForm.quantite"
              type="number" min="1" required
              class="input"
              :placeholder="mouvementForm.type === 'inventaire' ? 'Ex : 50' : 'Ex : 10'"
            />
          </div>

          <div>
            <label class="label">Motif (optionnel)</label>
            <input v-model="mouvementForm.motif" class="input" placeholder="Ex : Livraison fournisseur" />
          </div>

          <!-- Aperçu -->
          <div v-if="mouvementForm.quantite" class="bg-blue-50 rounded-lg p-3 text-sm text-center">
            <span class="text-gray-600">Nouveau stock estimé : </span>
            <span class="font-bold text-blue-700 text-lg">{{ stockPreview }}</span>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="mouvementModal.visible = false"
                    class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100">
              Annuler
            </button>
            <button type="submit" :disabled="savingMvt"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold disabled:opacity-50">
              {{ savingMvt ? 'Enregistrement...' : 'Valider' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import Swal from 'sweetalert2'

const ongletActif = ref('stock')
const searchStock = ref('')
const stocks      = ref([])
const mouvements  = ref([])
const savingMvt   = ref(false)

const tabs = [
  { key: 'stock',      icon: '📦', label: 'État du stock' },
  { key: 'mouvements', icon: '🔄', label: 'Historique mouvements' },
]

const mouvementModal = reactive({ visible: false, medicament: null })
const mouvementForm  = reactive({ type: 'entree', quantite: null, motif: '' })

const stockPreview = computed(() => {
  const med = mouvementModal.medicament
  if (!med || !mouvementForm.quantite) return '—'
  if (mouvementForm.type === 'entree')    return med.stock_actuel + mouvementForm.quantite
  if (mouvementForm.type === 'sortie')    return Math.max(0, med.stock_actuel - mouvementForm.quantite)
  if (mouvementForm.type === 'inventaire') return mouvementForm.quantite
  return '—'
})

async function chargerStock() {
  const { data } = await api.get('/stock', {
    params: { search: searchStock.value || undefined }
  })
  // Toujours paginé
  stocks.value = data.data ?? []
}

async function chargerMouvements() {
  const { data } = await api.get('/stock/mouvements')
  mouvements.value = data.data || data
}

function ouvrirMouvement(med) {
  mouvementModal.medicament = med
  mouvementModal.visible    = true
  mouvementForm.type        = 'entree'
  mouvementForm.quantite    = null
  mouvementForm.motif       = ''
}

async function enregistrerMouvement() {
  savingMvt.value = true
  try {
    await api.post('/stock/mouvement', {
      medicament_id: mouvementModal.medicament.id,
      type:          mouvementForm.type,
      quantite:      mouvementForm.quantite,
      motif:         mouvementForm.motif,
    })
    mouvementModal.visible = false
    Swal.fire('✅ Succès', 'Mouvement enregistré.', 'success')
    chargerStock()
    chargerMouvements()
  } catch (e) {
    Swal.fire('Erreur', e.response?.data?.message || 'Erreur.', 'error')
  } finally {
    savingMvt.value = false
  }
}

// Helpers
function rowClass(med) {
  if (med.stock_actuel === 0) return 'bg-red-50'
  if (med.stock_actuel <= med.stock_minimum) return 'bg-orange-50'
  return ''
}

function statutStock(med) {
  if (med.stock_actuel === 0)                    return { label: 'Rupture',    class: 'bg-red-100 text-red-700' }
  if (med.stock_actuel <= med.stock_minimum)     return { label: 'Faible',     class: 'bg-orange-100 text-orange-700' }
  return { label: 'Normal', class: 'bg-green-100 text-green-700' }
}

function expirationClass(date) {
  if (!date) return 'text-gray-400'
  const diff = (new Date(date) - new Date()) / (1000 * 60 * 60 * 24)
  if (diff < 0)  return 'text-red-600 font-bold'
  if (diff < 30) return 'text-orange-500 font-semibold'
  return 'text-gray-600'
}

function typeBadge(type) {
  return {
    entree:     'bg-green-100 text-green-700',
    sortie:     'bg-red-100 text-red-700',
    inventaire: 'bg-blue-100 text-blue-700',
  }[type] || ''
}

function formatDate(d)       { return new Date(d).toLocaleDateString('fr-FR') }
function formatDateHeure(d)  {
  return new Date(d).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })
}

onMounted(() => { chargerStock(); chargerMouvements() })
</script>

<style scoped>
@reference "tailwindcss";
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400; }
</style>