<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">🚚 Gestion des Achats</h1>
      <button
        @click="ongletActif = 'nouvelle'"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition"
      >
        + Nouvelle commande
      </button>
    </div>

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

    <!-- ===================== -->
    <!-- ONGLET : LISTE        -->
    <!-- ===================== -->
    <div v-if="ongletActif === 'liste'">

      <!-- Filtres -->
      <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3">
        <select
          v-model="filtreStatut"
          @change="chargerCommandes"
          class="input w-48"
        >
          <option value="">Tous les statuts</option>
          <option value="commande">En commande</option>
          <option value="livree">Livrées</option>
          <option value="annulee">Annulées</option>
        </select>

        <select
          v-model="filtreFournisseur"
          @change="chargerCommandes"
          class="input w-56"
        >
          <option value="">Tous les fournisseurs</option>
          <option
            v-for="f in fournisseurs"
            :key="f.id"
            :value="f.id"
          >
            {{ f.nom }}
          </option>
        </select>
      </div>

      <!-- Tableau des commandes -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="px-4 py-3 text-left">Référence</th>
              <th class="px-4 py-3 text-left">Fournisseur</th>
              <th class="px-4 py-3 text-center">Nb articles</th>
              <th class="px-4 py-3 text-right">Total</th>
              <th class="px-4 py-3 text-center">Statut</th>
              <th class="px-4 py-3 text-center">Date commande</th>
              <th class="px-4 py-3 text-center">Date livraison</th>
              <th class="px-4 py-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loadingListe">
              <td colspan="8" class="text-center py-10 text-gray-400">
                Chargement...
              </td>
            </tr>

            <tr
              v-for="achat in commandes"
              :key="achat.id"
              class="border-b hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3">
                <span class="font-mono font-semibold text-blue-700">
                  {{ achat.reference }}
                </span>
              </td>

              <td class="px-4 py-3 font-medium text-gray-800">
                {{ achat.fournisseur?.nom }}
              </td>

              <td class="px-4 py-3 text-center text-gray-600">
                {{ achat.details?.length || 0 }} article(s)
              </td>

              <td class="px-4 py-3 text-right font-bold text-blue-700">
                {{ Number(achat.total).toLocaleString('fr-FR') }} FCFA
              </td>

              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-1 rounded-full text-xs font-bold',
                               statutBadge(achat.statut).class]">
                  {{ statutBadge(achat.statut).label }}
                </span>
              </td>

              <td class="px-4 py-3 text-center text-gray-500 text-xs">
                {{ formatDate(achat.date_commande) }}
              </td>

              <td class="px-4 py-3 text-center text-gray-500 text-xs">
                {{ achat.date_livraison ? formatDate(achat.date_livraison) : '—' }}
              </td>

              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">

                  <!-- Voir détails -->
                  <button
                    @click="voirDetails(achat)"
                    class="text-gray-400 hover:text-blue-600 text-lg"
                    title="Voir détails"
                  >👁️</button>

                  <!-- Réceptionner livraison -->
                  <button
                    v-if="achat.statut === 'commande'"
                    @click="confirmerLivraison(achat)"
                    class="text-green-500 hover:text-green-700 text-lg"
                    title="Réceptionner la livraison"
                  >✅</button>

                  <!-- Annuler -->
                  <button
                    v-if="achat.statut === 'commande'"
                    @click="annulerCommande(achat)"
                    class="text-red-400 hover:text-red-600 text-lg"
                    title="Annuler"
                  >🗑️</button>

                </div>
              </td>
            </tr>

            <tr v-if="!loadingListe && !commandes.length">
              <td colspan="8" class="text-center py-10 text-gray-400">
                Aucune commande trouvée.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-4 py-3 border-t text-sm text-gray-500">
          <span>{{ pagination.total || 0 }} commande(s)</span>
          <div class="flex gap-2">
            <button
              :disabled="pagination.current_page <= 1"
              @click="changerPage(pagination.current_page - 1)"
              class="px-3 py-1 border rounded disabled:opacity-40 hover:bg-gray-100"
            >‹ Préc.</button>
            <span class="px-3 py-1">
              {{ pagination.current_page }} / {{ pagination.last_page || 1 }}
            </span>
            <button
              :disabled="pagination.current_page >= pagination.last_page"
              @click="changerPage(pagination.current_page + 1)"
              class="px-3 py-1 border rounded disabled:opacity-40 hover:bg-gray-100"
            >Suiv. ›</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== -->
    <!-- ONGLET : NOUVELLE     -->
    <!-- ===================== -->
    <div v-if="ongletActif === 'nouvelle'">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Colonne gauche : sélection produits -->
        <div class="bg-white rounded-xl shadow p-5 flex flex-col">
          <h2 class="font-bold text-gray-700 mb-4">🔍 Sélection des médicaments</h2>

          <input
            v-model="searchMed"
            @input="rechercherMedicament"
            class="input mb-3"
            placeholder="Rechercher un médicament..."
          />

          <div class="flex-1 overflow-y-auto space-y-2 max-h-96">
            <div
              v-for="med in resultats"
              :key="med.id"
              @click="ajouterLigne(med)"
              class="border rounded-lg p-3 cursor-pointer hover:bg-blue-50
                     hover:border-blue-300 transition"
            >
              <div class="flex justify-between">
                <span class="font-semibold text-gray-800">{{ med.nom }}</span>
                <span class="text-xs text-gray-400">
                  Stock : {{ med.stock_actuel }}
                </span>
              </div>
              <div class="text-xs text-gray-400 mt-1">
                Prix achat : {{ med.prix_achat.toLocaleString() }} FCFA
                | {{ med.categorie?.nom }}
              </div>
            </div>
            <p
              v-if="searchMed && !resultats.length"
              class="text-gray-400 text-sm text-center py-8"
            >
              Aucun résultat
            </p>
          </div>
        </div>

        <!-- Colonne droite : bon de commande -->
        <div class="bg-white rounded-xl shadow p-5 flex flex-col">
          <h2 class="font-bold text-gray-700 mb-4">📋 Bon de commande</h2>

          <!-- Fournisseur -->
          <div class="mb-4">
            <label class="label">Fournisseur *</label>
            <select v-model="nouvelleCommande.fournisseur_id" class="input">
              <option value="">— Choisir un fournisseur —</option>
              <option
                v-for="f in fournisseurs"
                :key="f.id"
                :value="f.id"
              >
                {{ f.nom }}
              </option>
            </select>
          </div>

          <!-- Lignes de commande -->
          <div class="flex-1 overflow-y-auto space-y-2 mb-4 max-h-64">
            <div
              v-for="(ligne, i) in nouvelleCommande.details"
              :key="i"
              class="flex items-center gap-2 border rounded-lg p-2 bg-gray-50"
            >
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-700 truncate">
                  {{ ligne.nom }}
                </p>
                <p class="text-xs text-gray-400">
                  {{ ligne.prix_unitaire.toLocaleString() }} FCFA / unité
                </p>
              </div>

              <!-- Quantité -->
              <div class="flex items-center gap-1">
                <button
                  @click="modifierQteLigne(i, -1)"
                  class="w-6 h-6 bg-gray-200 rounded text-sm hover:bg-red-100"
                >−</button>
                <span class="w-8 text-center text-sm font-bold">
                  {{ ligne.quantite }}
                </span>
                <button
                  @click="modifierQteLigne(i, 1)"
                  class="w-6 h-6 bg-gray-200 rounded text-sm hover:bg-green-100"
                >+</button>
              </div>

              <!-- Prix unitaire éditable -->
              <input
                v-model.number="ligne.prix_unitaire"
                type="number"
                min="0"
                class="w-24 border rounded px-2 py-1 text-xs text-right"
                title="Prix d'achat unitaire"
              />

              <!-- Sous-total -->
              <span class="text-sm font-bold text-blue-600 w-24 text-right">
                {{ (ligne.prix_unitaire * ligne.quantite).toLocaleString() }}
              </span>

              <button
                @click="retirerLigne(i)"
                class="text-red-400 hover:text-red-600"
              >✕</button>
            </div>

            <p
              v-if="!nouvelleCommande.details.length"
              class="text-gray-400 text-sm text-center py-8"
            >
              Aucun article ajouté
            </p>
          </div>

          <!-- Total -->
          <div class="border-t pt-3 mb-4">
            <div class="flex justify-between font-bold text-blue-700 text-lg">
              <span>Total commande</span>
              <span>{{ totalCommande.toLocaleString('fr-FR') }} FCFA</span>
            </div>
          </div>

          <!-- Erreur -->
          <div
            v-if="erreurCommande"
            class="mb-3 bg-red-50 border border-red-200 text-red-700
                   rounded-lg p-3 text-sm"
          >
            {{ erreurCommande }}
          </div>

          <!-- Actions -->
          <div class="flex gap-3">
            <button
              @click="reinitialiserCommande"
              class="flex-1 border border-gray-300 text-gray-600
                     hover:bg-gray-100 py-2 rounded-lg text-sm font-medium"
            >
              🗑️ Vider
            </button>
            <button
              @click="soumettreCommande"
              :disabled="!peutSoumettre || savingCommande"
              class="flex-1 bg-blue-600 hover:bg-blue-700 text-white
                     py-2 rounded-lg text-sm font-semibold
                     disabled:opacity-50 transition"
            >
              {{ savingCommande ? 'Envoi...' : '✅ Passer la commande' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== -->
    <!-- MODAL : DÉTAILS       -->
    <!-- ===================== -->
    <div
      v-if="detailModal.visible"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-screen overflow-y-auto">

        <div class="bg-blue-900 text-white px-6 py-4 rounded-t-2xl
                    flex justify-between items-center">
          <div>
            <h3 class="font-bold text-lg">
              Commande {{ detailModal.achat?.reference }}
            </h3>
            <p class="text-blue-300 text-sm">
              {{ detailModal.achat?.fournisseur?.nom }}
            </p>
          </div>
          <button
            @click="detailModal.visible = false"
            class="text-blue-200 hover:text-white text-xl"
          >✕</button>
        </div>

        <div class="p-6">

          <!-- Infos générales -->
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Statut</p>
              <span :class="['px-2 py-1 rounded-full text-xs font-bold mt-1 inline-block',
                             statutBadge(detailModal.achat?.statut).class]">
                {{ statutBadge(detailModal.achat?.statut).label }}
              </span>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Total</p>
              <p class="font-bold text-blue-700 text-lg">
                {{ Number(detailModal.achat?.total).toLocaleString('fr-FR') }} FCFA
              </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Date commande</p>
              <p class="font-medium">
                {{ formatDate(detailModal.achat?.date_commande) }}
              </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Date livraison</p>
              <p class="font-medium">
                {{
                  detailModal.achat?.date_livraison
                    ? formatDate(detailModal.achat.date_livraison)
                    : '—'
                }}
              </p>
            </div>
          </div>

          <!-- Tableau des articles -->
          <h4 class="font-semibold text-gray-700 mb-3">Articles commandés</h4>
          <table class="w-full text-sm border rounded-lg overflow-hidden">
            <thead class="bg-gray-100 text-gray-600">
              <tr>
                <th class="px-3 py-2 text-left">Médicament</th>
                <th class="px-3 py-2 text-center">Quantité</th>
                <th class="px-3 py-2 text-right">Prix unitaire</th>
                <th class="px-3 py-2 text-right">Sous-total</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="detail in detailModal.achat?.details"
                :key="detail.id"
                class="border-t"
              >
                <td class="px-3 py-2 font-medium">
                  {{ detail.medicament?.nom }}
                </td>
                <td class="px-3 py-2 text-center">{{ detail.quantite }}</td>
                <td class="px-3 py-2 text-right">
                  {{ Number(detail.prix_unitaire).toLocaleString() }} FCFA
                </td>
                <td class="px-3 py-2 text-right font-bold text-blue-700">
                  {{ Number(detail.sous_total).toLocaleString() }} FCFA
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-blue-900 text-white font-bold">
              <tr>
                <td colspan="3" class="px-3 py-2">TOTAL</td>
                <td class="px-3 py-2 text-right">
                  {{ Number(detailModal.achat?.total).toLocaleString('fr-FR') }} FCFA
                </td>
              </tr>
            </tfoot>
          </table>

          <!-- Action livraison depuis modal -->
          <div
            v-if="detailModal.achat?.statut === 'commande'"
            class="mt-4"
          >
            <button
              @click="confirmerLivraison(detailModal.achat); detailModal.visible = false"
              class="w-full bg-green-600 hover:bg-green-700 text-white
                     font-semibold py-3 rounded-lg transition"
            >
              ✅ Réceptionner cette livraison
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import Swal from 'sweetalert2'

// ─── State principal ──────────────────────────────────────
const ongletActif     = ref('liste')
const commandes       = ref([])
const fournisseurs    = ref([])
const resultats       = ref([])
const loadingListe    = ref(false)
const savingCommande  = ref(false)
const erreurCommande  = ref('')
const searchMed       = ref('')
const filtreStatut    = ref('')
const filtreFournisseur = ref('')

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
})

const tabs = [
  { key: 'liste',    icon: '📋', label: 'Commandes' },
  { key: 'nouvelle', icon: '➕', label: 'Nouvelle commande' },
]

// ─── Nouvelle commande state ──────────────────────────────
const nouvelleCommande = reactive({
  fournisseur_id: '',
  details: [],
})

const detailModal = reactive({
  visible: false,
  achat: null,
})

// ─── Computed ─────────────────────────────────────────────
const totalCommande = computed(() =>
  nouvelleCommande.details.reduce(
    (sum, l) => sum + l.prix_unitaire * l.quantite, 0
  )
)

const peutSoumettre = computed(() =>
  nouvelleCommande.fournisseur_id &&
  nouvelleCommande.details.length > 0
)

// ─── Chargement des commandes ─────────────────────────────
async function chargerCommandes(page = 1) {
  loadingListe.value = true
  try {
    const { data } = await api.get('/achats', {
      params: {
        statut:        filtreStatut.value || undefined,
        fournisseur_id: filtreFournisseur.value || undefined,
        page,
      }
    })
    commandes.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page:    data.last_page,
      total:        data.total,
    }
  } finally {
    loadingListe.value = false
  }
}

async function chargerFournisseurs() {
  const { data } = await api.get('/fournisseurs')
  fournisseurs.value = data.data || data
}

function changerPage(page) {
  chargerCommandes(page)
}

// ─── Recherche médicaments ────────────────────────────────
let timer = null
function rechercherMedicament() {
  clearTimeout(timer)
  if (!searchMed.value.trim()) { resultats.value = []; return }
  timer = setTimeout(async () => {
    try {
      const { data } = await api.get('/medicaments', {
        params: { search: searchMed.value }
      })
      resultats.value = Array.isArray(data) ? data : (data.data ?? [])
    } catch {
      resultats.value = []
    }
  }, 300)
}
// ─── Gestion des lignes commande ──────────────────────────
function ajouterLigne(med) {
  const existant = nouvelleCommande.details.find(
    l => l.medicament_id === med.id
  )
  if (existant) {
    existant.quantite++
  } else {
    nouvelleCommande.details.push({
      medicament_id: med.id,
      nom:           med.nom,
      quantite:      1,
      prix_unitaire: med.prix_achat,
    })
  }
  searchMed.value = ''
  resultats.value = []
}

function modifierQteLigne(index, delta) {
  const ligne = nouvelleCommande.details[index]
  const newQte = ligne.quantite + delta
  if (newQte <= 0) {
    nouvelleCommande.details.splice(index, 1)
    return
  }
  ligne.quantite = newQte
}

function retirerLigne(index) {
  nouvelleCommande.details.splice(index, 1)
}

function reinitialiserCommande() {
  nouvelleCommande.fournisseur_id = ''
  nouvelleCommande.details = []
  erreurCommande.value = ''
}

// ─── Soumettre la commande ────────────────────────────────
async function soumettreCommande() {
  erreurCommande.value = ''

  // Validation prix unitaires
  const ligneInvalide = nouvelleCommande.details.find(l => l.prix_unitaire <= 0)
  if (ligneInvalide) {
    erreurCommande.value = `Prix invalide pour : ${ligneInvalide.nom}`
    return
  }

  const result = await Swal.fire({
    title:             'Passer la commande ?',
    html: `
      <p class="text-gray-600">Fournisseur : <strong>
        ${fournisseurs.value.find(f => f.id == nouvelleCommande.fournisseur_id)?.nom}
      </strong></p>
      <p class="text-gray-600">Total : <strong>
        ${totalCommande.value.toLocaleString('fr-FR')} FCFA
      </strong></p>
      <p class="text-gray-600">${nouvelleCommande.details.length} article(s)</p>
    `,
    icon:              'question',
    showCancelButton:  true,
    confirmButtonText: 'Confirmer',
    cancelButtonText:  'Annuler',
    confirmButtonColor: '#2563EB',
  })

  if (!result.isConfirmed) return

  savingCommande.value = true
  try {
    const payload = {
      fournisseur_id: nouvelleCommande.fournisseur_id,
      total:          totalCommande.value,
      details: nouvelleCommande.details.map(l => ({
        medicament_id: l.medicament_id,
        quantite:      l.quantite,
        prix_unitaire: l.prix_unitaire,
      })),
    }

    await api.post('/achats', payload)

    await Swal.fire(
      '✅ Commande créée',
      'La commande a été enregistrée avec succès.',
      'success'
    )

    reinitialiserCommande()
    ongletActif.value = 'liste'
    chargerCommandes()

  } catch (e) {
    erreurCommande.value =
      e.response?.data?.message ||
      Object.values(e.response?.data?.errors || {}).flat().join(' ') ||
      'Erreur lors de l\'envoi.'
  } finally {
    savingCommande.value = false
  }
}

// ─── Voir détails ─────────────────────────────────────────
async function voirDetails(achat) {
  try {
    const { data } = await api.get(`/achats/${achat.id}`)
    detailModal.achat   = data
    detailModal.visible = true
  } catch {
    Swal.fire('Erreur', 'Impossible de charger les détails.', 'error')
  }
}

// ─── Confirmer livraison ──────────────────────────────────
async function confirmerLivraison(achat) {
  const result = await Swal.fire({
    title:             '📦 Confirmer la réception ?',
    html: `
      <p class="text-gray-600">Commande : <strong>${achat.reference}</strong></p>
      <p class="text-gray-600">Le stock sera mis à jour automatiquement.</p>
    `,
    icon:              'question',
    showCancelButton:  true,
    confirmButtonText: '✅ Réceptionner',
    cancelButtonText:  'Annuler',
    confirmButtonColor: '#16a34a',
  })

  if (!result.isConfirmed) return

  try {
    await api.post(`/achats/${achat.id}/livraison`)

    await Swal.fire(
      '✅ Livraison réceptionnée',
      'Le stock a été mis à jour automatiquement.',
      'success'
    )
    chargerCommandes()
  } catch (e) {
    Swal.fire(
      'Erreur',
      e.response?.data?.message || 'Erreur lors de la réception.',
      'error'
    )
  }
}

// ─── Annuler commande ─────────────────────────────────────
async function annulerCommande(achat) {
  const result = await Swal.fire({
    title:             'Annuler la commande ?',
    text:              achat.reference,
    icon:              'warning',
    showCancelButton:  true,
    confirmButtonText: 'Oui, annuler',
    cancelButtonText:  'Non',
    confirmButtonColor: '#dc2626',
  })

  if (!result.isConfirmed) return

  try {
    await api.delete(`/achats/${achat.id}`)
    chargerCommandes()
  } catch (e) {
    Swal.fire(
      'Erreur',
      e.response?.data?.message || 'Erreur.',
      'error'
    )
  }
}

// ─── Helpers ──────────────────────────────────────────────
function statutBadge(statut) {
  return {
    commande: { label: '🕐 En attente',  class: 'bg-yellow-100 text-yellow-700' },
    livree:   { label: '✅ Livrée',      class: 'bg-green-100 text-green-700'  },
    annulee:  { label: '❌ Annulée',     class: 'bg-red-100 text-red-700'      },
  }[statut] || { label: statut, class: 'bg-gray-100 text-gray-600' }
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', {
    day:   '2-digit',
    month: 'short',
    year:  'numeric',
  })
}

// ─── Init ─────────────────────────────────────────────────
onMounted(() => {
  chargerCommandes()
  chargerFournisseurs()
})
</script>

<style scoped>
@reference "tailwindcss";

.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input {
  @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
         focus:outline-none focus:ring-2 focus:ring-blue-400;
}
</style>