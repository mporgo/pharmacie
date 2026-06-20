<template>
  <div class="flex gap-4 h-full">

    <!-- Panneau gauche : recherche + catalogue -->
    <div class="flex-1 bg-white rounded-xl shadow p-4 flex flex-col">
      <h2 class="font-bold text-gray-800 text-lg mb-3">🔍 Recherche médicament</h2>

      <input
        v-model="searchQuery"
        @input="rechercherMedicament"
        class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400"
        placeholder="Nom ou code-barres..."
        autofocus
      />

      <!-- Résultats -->
      <div class="flex-1 overflow-y-auto space-y-2">
        <div
          v-for="med in resultats"
          :key="med.id"
          @click="ajouterAuPanier(med)"
          class="border rounded-lg p-3 cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition"
        >
          <div class="flex justify-between">
            <span class="font-semibold text-gray-800">{{ med.nom }}</span>
            <span class="text-blue-600 font-bold">{{ med.prix_vente.toLocaleString() }} FCFA</span>
          </div>
          <div class="text-xs text-gray-400 mt-1">
            Stock : {{ med.stock_actuel }} | {{ med.categorie?.nom }}
          </div>
        </div>
        <p v-if="searchQuery && !resultats.length" class="text-gray-400 text-sm text-center py-8">
          Aucun médicament trouvé
        </p>
      </div>
    </div>

    <!-- Panneau droit : Panier + Validation -->
    <div class="w-96 bg-white rounded-xl shadow p-4 flex flex-col">
      <h2 class="font-bold text-gray-800 text-lg mb-3">🛒 Panier</h2>

      <!-- Ligne panier -->
      <div class="flex-1 overflow-y-auto space-y-2 mb-4">
        <div
          v-for="(item, i) in panier"
          :key="i"
          class="flex items-center gap-2 border rounded-lg p-2 bg-gray-50"
        >
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-700 truncate">{{ item.nom }}</p>
            <p class="text-xs text-gray-400">{{ item.prix_vente.toLocaleString() }} FCFA</p>
          </div>
          <div class="flex items-center gap-1">
            <button @click="modifierQte(i, -1)" class="w-6 h-6 bg-gray-200 rounded text-sm hover:bg-red-100">−</button>
            <span class="w-8 text-center text-sm font-bold">{{ item.quantite }}</span>
            <button @click="modifierQte(i, 1)" class="w-6 h-6 bg-gray-200 rounded text-sm hover:bg-green-100">+</button>
          </div>
          <span class="text-sm font-bold text-blue-600 w-20 text-right">
            {{ (item.prix_vente * item.quantite).toLocaleString() }}
          </span>
          <button @click="retirerDuPanier(i)" class="text-red-400 hover:text-red-600">✕</button>
        </div>

        <p v-if="!panier.length" class="text-gray-400 text-sm text-center py-8">
          Panier vide
        </p>
      </div>

      <!-- Totaux -->
      <div class="border-t pt-3 space-y-2 mb-4">
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">Sous-total</span>
          <span>{{ sousTotal.toLocaleString() }} FCFA</span>
        </div>
        <div class="flex justify-between text-sm items-center">
          <span class="text-gray-500">Remise</span>
          <input v-model.number="remise" type="number" min="0"
                 class="w-24 border rounded px-2 py-1 text-right text-sm" />
        </div>
        <div class="flex justify-between font-bold text-blue-700 text-lg">
          <span>Total</span>
          <span>{{ total.toLocaleString() }} FCFA</span>
        </div>
        <div class="flex justify-between text-sm items-center">
          <span class="text-gray-500">Montant reçu</span>
          <input v-model.number="montantPaye" type="number" min="0"
                 class="w-32 border rounded px-2 py-1 text-right text-sm font-bold" />
        </div>
        <div v-if="monnaie >= 0" class="flex justify-between text-green-600 font-semibold">
          <span>Monnaie</span>
          <span>{{ monnaie.toLocaleString() }} FCFA</span>
        </div>
        <div v-else class="text-red-500 text-sm text-right">Montant insuffisant</div>
      </div>

      <!-- Bouton validation -->
      <button
        @click="validerVente"
        :disabled="!peutValider || loading"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition disabled:opacity-50"
      >
        {{ loading ? 'Traitement...' : '✅ Valider la vente' }}
      </button>

      <button @click="viderPanier" v-if="panier.length"
              class="mt-2 w-full text-sm text-red-500 hover:underline">
        Vider le panier
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import api from '@/services/api'
import Swal from 'sweetalert2'

const searchQuery = ref('')
const resultats   = ref([])
const panier      = ref([])
const remise      = ref(0)
const montantPaye = ref(0)
const loading     = ref(false)

// Recherche avec debounce simple
let timer = null
async function rechercherMedicament() {
  clearTimeout(timer)
  if (!searchQuery.value.trim()) { resultats.value = []; return }
  timer = setTimeout(async () => {
    try {
      const { data } = await api.get('/medicaments', {
        params: { search: searchQuery.value }
      })
      // Backend pagine → data.data, sinon tableau direct
      resultats.value = Array.isArray(data) ? data : (data.data ?? [])
    } catch {
      resultats.value = []
    }
  }, 300)
}

function ajouterAuPanier(med) {
  if (med.stock_actuel <= 0) {
    Swal.fire('Stock épuisé', `${med.nom} n'est plus disponible.`, 'warning')
    return
  }
  const existant = panier.value.find(p => p.id === med.id)
  if (existant) {
    if (existant.quantite < med.stock_actuel) existant.quantite++
  } else {
    panier.value.push({ ...med, quantite: 1 })
  }
  searchQuery.value = ''
  resultats.value   = []
}

function modifierQte(index, delta) {
  const item = panier.value[index]
  const newQte = item.quantite + delta
  if (newQte <= 0) { panier.value.splice(index, 1); return }
  if (newQte > item.stock_actuel) return
  item.quantite = newQte
}

function retirerDuPanier(index) { panier.value.splice(index, 1) }
function viderPanier() { panier.value = [] }

const sousTotal   = computed(() => panier.value.reduce((s, i) => s + i.prix_vente * i.quantite, 0))
const total       = computed(() => Math.max(0, sousTotal.value - remise.value))
const monnaie     = computed(() => montantPaye.value - total.value)
const peutValider = computed(() => panier.value.length > 0 && montantPaye.value >= total.value)

async function validerVente() {
  loading.value = true
  try {
    const payload = {
      total:        total.value,
      remise:       remise.value,
      montant_paye: montantPaye.value,
      details: panier.value.map(i => ({
        medicament_id: i.id,
        quantite:      i.quantite,
      }))
    }
    const { data } = await api.post('/ventes', payload)

    await Swal.fire({
      title: '✅ Vente validée',
      html: `
        <p>Référence : <strong>${data.reference}</strong></p>
        <p>Monnaie à rendre : <strong>${monnaie.value.toLocaleString()} FCFA</strong></p>
      `,
      icon: 'success',
    })

    // ✅ Reset complet
    viderPanier()
    remise.value      = 0
    montantPaye.value = 0
    resultats.value   = []   // ← vider aussi les résultats de recherche
    searchQuery.value = ''

  } catch (e) {
    Swal.fire('Erreur', e.response?.data?.message || 'Erreur lors de la vente.', 'error')
  } finally {
    loading.value = false
  }
}
</script>