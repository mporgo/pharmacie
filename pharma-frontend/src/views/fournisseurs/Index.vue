<template>
  <div>

    <!-- En-tête -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">🏭 Fournisseurs</h1>
      <button
        @click="ouvrirModal()"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2
               rounded-lg font-medium transition"
      >
        + Nouveau fournisseur
      </button>
    </div>

    <!-- Barre de recherche -->
    <div class="bg-white rounded-xl shadow p-4 mb-4">
      <input
        v-model="search"
        @input="filtrerLocalement"
        placeholder="Rechercher un fournisseur..."
        class="input max-w-md"
      />
    </div>

    <!-- Grille des fournisseurs -->
    <div
      v-if="!loading && fournisseursFiltres.length"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6"
    >
      <div
        v-for="f in fournisseursFiltres"
        :key="f.id"
        class="bg-white rounded-xl shadow hover:shadow-md transition p-5
               border border-transparent hover:border-blue-200 cursor-pointer"
        @click="voirHistorique(f)"
      >
        <!-- Avatar + Nom -->
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center
                      justify-center text-blue-700 font-bold text-lg flex-shrink-0">
            {{ initiales(f.nom) }}
          </div>
          <div class="min-w-0">
            <h3 class="font-bold text-gray-800 truncate">{{ f.nom }}</h3>
            <p class="text-xs text-gray-400">{{ f.achats_count || 0 }} commande(s)</p>
          </div>
        </div>

        <!-- Infos contact -->
        <div class="space-y-1 text-sm text-gray-500 mb-4">
          <div v-if="f.telephone" class="flex items-center gap-2">
            <span>📞</span>
            <span>{{ f.telephone }}</span>
          </div>
          <div v-if="f.email" class="flex items-center gap-2">
            <span>📧</span>
            <span class="truncate">{{ f.email }}</span>
          </div>
          <div v-if="f.adresse" class="flex items-center gap-2">
            <span>📍</span>
            <span class="truncate">{{ f.adresse }}</span>
          </div>
          <div
            v-if="!f.telephone && !f.email && !f.adresse"
            class="text-gray-300 italic text-xs"
          >
            Aucune information de contact
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 pt-3 border-t" @click.stop>
          <button
            @click="voirHistorique(f)"
            class="flex-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700
                   py-1.5 rounded-lg font-medium transition"
          >
            📦 Historique
          </button>
          <button
            @click="ouvrirModal(f)"
            class="flex-1 text-xs bg-gray-50 hover:bg-gray-100 text-gray-700
                   py-1.5 rounded-lg font-medium transition"
          >
            ✏️ Modifier
          </button>
          <button
            @click="supprimer(f)"
            class="text-xs bg-red-50 hover:bg-red-100 text-red-600
                   px-3 py-1.5 rounded-lg font-medium transition"
          >
            🗑️
          </button>
        </div>
      </div>
    </div>

    <!-- État vide -->
    <div
      v-if="!loading && !fournisseursFiltres.length"
      class="bg-white rounded-xl shadow p-16 text-center"
    >
      <p class="text-5xl mb-4">🏭</p>
      <p class="text-gray-500 font-medium">Aucun fournisseur trouvé.</p>
      <button
        @click="ouvrirModal()"
        class="mt-4 bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-medium"
      >
        Ajouter le premier fournisseur
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="n in 6"
        :key="n"
        class="bg-white rounded-xl shadow p-5 animate-pulse"
      >
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-full bg-gray-200"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
          </div>
        </div>
        <div class="space-y-2">
          <div class="h-3 bg-gray-200 rounded"></div>
          <div class="h-3 bg-gray-200 rounded w-4/5"></div>
        </div>
      </div>
    </div>

    <!-- ========================= -->
    <!-- MODAL : AJOUT / ÉDITION   -->
    <!-- ========================= -->
    <div
      v-if="modal.visible"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">

        <div class="bg-blue-900 text-white px-6 py-4 rounded-t-2xl
                    flex justify-between items-center">
          <h3 class="font-bold text-lg">
            {{ modal.fournisseur ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}
          </h3>
          <button
            @click="fermerModal"
            class="text-blue-200 hover:text-white text-xl"
          >✕</button>
        </div>

        <form @submit.prevent="sauvegarder" class="p-6 space-y-4">

          <div>
            <label class="label">Nom du fournisseur *</label>
            <input
              v-model="form.nom"
              required
              class="input"
              placeholder="Ex : Pharma Distribution SARL"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Téléphone</label>
              <input
                v-model="form.telephone"
                class="input"
                placeholder="Ex : +226 70 00 00 00"
              />
            </div>
            <div>
              <label class="label">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="input"
                placeholder="contact@pharma.com"
              />
            </div>
          </div>

          <div>
            <label class="label">Adresse</label>
            <textarea
              v-model="form.adresse"
              class="input resize-none"
              rows="2"
              placeholder="Adresse complète..."
            ></textarea>
          </div>

          <!-- Erreur -->
          <div
            v-if="erreur"
            class="bg-red-50 border border-red-200 text-red-700
                   rounded-lg p-3 text-sm"
          >
            {{ erreur }}
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              @click="fermerModal"
              class="px-5 py-2 border rounded-lg text-gray-600 hover:bg-gray-100"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white
                     rounded-lg font-semibold disabled:opacity-50"
            >
              {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ========================= -->
    <!-- MODAL : HISTORIQUE ACHATS -->
    <!-- ========================= -->
    <div
      v-if="historiqueModal.visible"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl
               max-h-screen overflow-y-auto"
      >

        <div class="bg-blue-900 text-white px-6 py-4 rounded-t-2xl
                    flex justify-between items-center sticky top-0">
          <div>
            <h3 class="font-bold text-lg">
              {{ historiqueModal.fournisseur?.nom }}
            </h3>
            <p class="text-blue-300 text-sm">Historique des commandes</p>
          </div>
          <button
            @click="historiqueModal.visible = false"
            class="text-blue-200 hover:text-white text-xl"
          >✕</button>
        </div>

        <div class="p-6">

          <!-- KPI fournisseur -->
          <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-xl p-4 text-center">
              <p class="text-sm text-gray-500 mb-1">Total commandes</p>
              <p class="text-2xl font-bold text-blue-700">
                {{ historiqueModal.stats.total_commandes }}
              </p>
            </div>
            <div class="bg-green-50 rounded-xl p-4 text-center">
              <p class="text-sm text-gray-500 mb-1">Livrées</p>
              <p class="text-2xl font-bold text-green-700">
                {{ historiqueModal.stats.livrees }}
              </p>
            </div>
            <div class="bg-purple-50 rounded-xl p-4 text-center">
              <p class="text-sm text-gray-500 mb-1">Total dépensé</p>
              <p class="text-lg font-bold text-purple-700">
                {{ formatMoney(historiqueModal.stats.total_depense) }}
              </p>
            </div>
          </div>

          <!-- Liste des achats -->
          <div v-if="historiqueModal.achats.length">
            <h4 class="font-semibold text-gray-700 mb-3">
              10 dernières commandes
            </h4>
            <div class="space-y-3">
              <div
                v-for="achat in historiqueModal.achats"
                :key="achat.id"
                class="border rounded-xl p-4 hover:bg-gray-50 transition"
              >
                <!-- Header ligne -->
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-3">
                    <span class="font-mono text-sm font-bold text-blue-700">
                      {{ achat.reference }}
                    </span>
                    <span
                      :class="['px-2 py-0.5 rounded-full text-xs font-bold',
                               statutBadge(achat.statut).class]"
                    >
                      {{ statutBadge(achat.statut).label }}
                    </span>
                  </div>
                  <div class="text-right">
                    <p class="font-bold text-blue-700">
                      {{ formatMoney(achat.total) }}
                    </p>
                    <p class="text-xs text-gray-400">
                      {{ formatDate(achat.date_commande) }}
                    </p>
                  </div>
                </div>

                <!-- Articles -->
                <div
                  v-if="achat.details?.length"
                  class="bg-gray-50 rounded-lg px-3 py-2"
                >
                  <p class="text-xs text-gray-500 mb-1 font-medium">Articles :</p>
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="d in achat.details"
                      :key="d.id"
                      class="text-xs bg-white border rounded px-2 py-0.5 text-gray-600"
                    >
                      {{ d.medicament?.nom }} × {{ d.quantite }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pas d'historique -->
          <div
            v-else
            class="text-center py-12 text-gray-400"
          >
            <p class="text-4xl mb-3">📦</p>
            <p class="text-sm">Aucune commande passée avec ce fournisseur.</p>
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

// ─── State ────────────────────────────────────────────────
const fournisseurs       = ref([])
const fournisseursFiltres = ref([])
const loading            = ref(false)
const saving             = ref(false)
const erreur             = ref('')
const search             = ref('')

// ─── Modal CRUD ───────────────────────────────────────────
const modal = reactive({
  visible:     false,
  fournisseur: null,
})

const form = reactive({
  nom:       '',
  telephone: '',
  email:     '',
  adresse:   '',
})

// ─── Modal Historique ─────────────────────────────────────
const historiqueModal = reactive({
  visible:     false,
  fournisseur: null,
  achats:      [],
  stats: {
    total_commandes: 0,
    livrees:         0,
    total_depense:   0,
  },
})

// ─── Chargement ───────────────────────────────────────────
async function charger() {
  loading.value = true
  try {
    const { data } = await api.get('/fournisseurs')
    fournisseurs.value        = data.data || data
    fournisseursFiltres.value = fournisseurs.value
  } finally {
    loading.value = false
  }
}

// ─── Filtre local (pas d'appel API pour la recherche) ─────
function filtrerLocalement() {
  const q = search.value.toLowerCase().trim()
  if (!q) {
    fournisseursFiltres.value = fournisseurs.value
    return
  }
  fournisseursFiltres.value = fournisseurs.value.filter(f =>
    f.nom.toLowerCase().includes(q)       ||
    f.email?.toLowerCase().includes(q)    ||
    f.telephone?.toLowerCase().includes(q)||
    f.adresse?.toLowerCase().includes(q)
  )
}

// ─── Modal CRUD ───────────────────────────────────────────
function ouvrirModal(f = null) {
  modal.fournisseur = f
  modal.visible     = true
  erreur.value      = ''

  Object.assign(form, {
    nom:       f?.nom       || '',
    telephone: f?.telephone || '',
    email:     f?.email     || '',
    adresse:   f?.adresse   || '',
  })
}

function fermerModal() {
  modal.visible = false
}

async function sauvegarder() {
  saving.value = true
  erreur.value = ''
  try {
    // Nettoyer les champs vides
    const payload = Object.fromEntries(
      Object.entries(form).filter(([, v]) => v !== '')
    )

    if (modal.fournisseur) {
      await api.put(`/fournisseurs/${modal.fournisseur.id}`, payload)
    } else {
      await api.post('/fournisseurs', payload)
    }

    fermerModal()
    await charger()
    Swal.fire('✅ Succès', 'Fournisseur enregistré.', 'success')

  } catch (e) {
    erreur.value =
      e.response?.data?.message ||
      Object.values(e.response?.data?.errors || {}).flat().join(' ') ||
      'Erreur lors de la sauvegarde.'
  } finally {
    saving.value = false
  }
}

async function supprimer(f) {
  const result = await Swal.fire({
    title:             `Supprimer ${f.nom} ?`,
    text:              'Cette action est irréversible.',
    icon:              'warning',
    showCancelButton:  true,
    confirmButtonText: 'Oui, supprimer',
    cancelButtonText:  'Annuler',
    confirmButtonColor: '#dc2626',
  })

  if (!result.isConfirmed) return

  try {
    await api.delete(`/fournisseurs/${f.id}`)
    charger()
  } catch (e) {
    Swal.fire(
      'Impossible',
      e.response?.data?.message || 'Erreur lors de la suppression.',
      'error'
    )
  }
}

// ─── Historique ───────────────────────────────────────────
async function voirHistorique(f) {
  historiqueModal.fournisseur = f
  historiqueModal.achats      = []
  historiqueModal.stats       = {
    total_commandes: 0,
    livrees:         0,
    total_depense:   0,
  }
  historiqueModal.visible = true

  try {
    // Détail fournisseur (inclut les 10 derniers achats via le controller)
    const { data } = await api.get(`/fournisseurs/${f.id}`)

    // Achats déjà chargés dans le show()
    historiqueModal.achats = data.achats || []

    // Calcul stats depuis les achats
    const achats = historiqueModal.achats
    historiqueModal.stats = {
      total_commandes: achats.length,
      livrees:         achats.filter(a => a.statut === 'livree').length,
      total_depense:   achats
        .filter(a => a.statut === 'livree')
        .reduce((s, a) => s + Number(a.total), 0),
    }
  } catch {
    Swal.fire('Erreur', 'Impossible de charger l\'historique.', 'error')
    historiqueModal.visible = false
  }
}

// ─── Helpers ──────────────────────────────────────────────
function initiales(nom) {
  return nom
    .split(' ')
    .slice(0, 2)
    .map(w => w[0]?.toUpperCase())
    .join('')
}

function formatMoney(val) {
  return Number(val || 0).toLocaleString('fr-FR') + ' FCFA'
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', {
    day:   '2-digit',
    month: 'short',
    year:  'numeric',
  })
}

function statutBadge(statut) {
  return {
    commande: { label: '🕐 En attente', class: 'bg-yellow-100 text-yellow-700' },
    livree:   { label: '✅ Livrée',     class: 'bg-green-100 text-green-700'  },
    annulee:  { label: '❌ Annulée',    class: 'bg-red-100 text-red-700'      },
  }[statut] || { label: statut, class: 'bg-gray-100 text-gray-600' }
}

// ─── Init ─────────────────────────────────────────────────
onMounted(charger)
</script>

<style scoped>
@reference "tailwindcss";

.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input {
  @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
         focus:outline-none focus:ring-2 focus:ring-blue-400;
}
</style>