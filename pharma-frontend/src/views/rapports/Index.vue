<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">📈 Rapports & Statistiques</h1>

    <!-- section boutons  -->
    <div class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-3 items-end">
      <div>
        <label class="label">Date début</label>
        <input v-model="filtres.debut" type="date" class="input" />
      </div>
      <div>
        <label class="label">Date fin</label>
        <input v-model="filtres.fin" type="date" class="input" />
      </div>

      <button @click="chargerRapport"
              class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">
        📊 Générer
      </button>

      <!-- Groupe PDF -->
      <div class="flex gap-2">
        <button @click="exporterPDF"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
          📄 PDF Ventes
        </button>
        <button @click="exporterPDFStock"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
          📄 PDF Stock
        </button>
      </div>

      <!-- Groupe Excel -->
      <div class="flex gap-2">
        <button @click="exporterExcel"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
          📋 Excel Ventes
        </button>
        <button @click="exporterExcelComplet"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
          📋 Excel Complet
        </button>
        <button @click="exporterExcelStock"
                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
          📋 Excel Stock
        </button>
      </div>
    </div>

    <!-- KPI période -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
        <p class="text-sm text-gray-500">Nombre de ventes</p>
        <p class="text-3xl font-bold text-blue-700">{{ totalVentes }}</p>
      </div>
      <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
        <p class="text-sm text-gray-500">Chiffre d'affaires</p>
        <p class="text-2xl font-bold text-green-700">{{ formatMoney(totalCA) }}</p>
      </div>
      <div class="bg-white rounded-xl shadow p-5 border-l-4 border-purple-500">
        <p class="text-sm text-gray-500">Panier moyen</p>
        <p class="text-2xl font-bold text-purple-700">{{ formatMoney(panierMoyen) }}</p>
      </div>
    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

      <!-- Courbe CA par jour -->
      <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Évolution des ventes</h2>
        <Line v-if="chartVentes.labels?.length" :data="chartVentes" :options="optionsLine" />
        <p v-else class="text-gray-400 text-center py-12 text-sm">Aucune donnée sur la période</p>
      </div>

      <!-- Top produits -->
      <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Top 10 produits vendus</h2>
        <Bar v-if="chartTop.labels?.length" :data="chartTop" :options="optionsBar" />
        <p v-else class="text-gray-400 text-center py-12 text-sm">Aucune donnée</p>
      </div>
    </div>

    <!-- Tableau ventes par jour -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="px-4 py-3 border-b">
        <h2 class="font-semibold text-gray-700">Détail par jour</h2>
      </div>
      <table class="w-full text-sm">
        <thead class="bg-gray-100 text-gray-600">
          <tr>
            <th class="px-4 py-3 text-left">Date</th>
            <th class="px-4 py-3 text-center">Nb ventes</th>
            <th class="px-4 py-3 text-right">CA (FCFA)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ligne in ventesData" :key="ligne.date" class="border-b hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ formatDate(ligne.date) }}</td>
            <td class="px-4 py-3 text-center">{{ ligne.nombre }}</td>
            <td class="px-4 py-3 text-right font-bold text-blue-700">
              {{ Number(ligne.total).toLocaleString('fr-FR') }}
            </td>
          </tr>
          <tr v-if="!ventesData.length">
            <td colspan="3" class="text-center py-8 text-gray-400">Aucune donnée</td>
          </tr>
        </tbody>
        <tfoot v-if="ventesData.length" class="bg-blue-900 text-white font-bold">
          <tr>
            <td class="px-4 py-3">TOTAL</td>
            <td class="px-4 py-3 text-center">{{ totalVentes }}</td>
            <td class="px-4 py-3 text-right">{{ formatMoney(totalCA) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Line, Bar } from 'vue-chartjs'

import api, { buildExportUrl } from '@/services/api'

const ventesData  = ref([])
const topProduits = ref([])

const filtres = reactive({
  debut: new Date(new Date().setDate(1)).toISOString().substring(0, 10), // 1er du mois
  fin:   new Date().toISOString().substring(0, 10),
})

// Calculs totaux
const totalVentes = computed(() => ventesData.value.reduce((s, v) => s + v.nombre, 0))
const totalCA     = computed(() => ventesData.value.reduce((s, v) => s + Number(v.total), 0))
const panierMoyen = computed(() => totalVentes.value ? totalCA.value / totalVentes.value : 0)

// Chart ventes par jour
const chartVentes = computed(() => ({
  labels: ventesData.value.map(v => formatDate(v.date)),
  datasets: [{
    label: 'CA (FCFA)',
    data: ventesData.value.map(v => Number(v.total)),
    borderColor: '#3B82F6',
    backgroundColor: 'rgba(59,130,246,0.1)',
    tension: 0.4,
    fill: true,
  }]
}))

// Chart top produits
const chartTop = computed(() => ({
  labels: topProduits.value.map(p => p.medicament?.nom || '?'),
  datasets: [{
    label: 'Quantité vendue',
    data: topProduits.value.map(p => p.total_vendu),
    backgroundColor: [
      '#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6',
      '#06B6D4','#84CC16','#F97316','#EC4899','#6366F1'
    ],
    borderRadius: 6,
  }]
}))

const optionsLine = {
  responsive: true,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true } }
}

const optionsBar = {
  responsive: true,
  indexAxis: 'y', // horizontal bars
  plugins: { legend: { display: false } },
}

async function chargerRapport() {
  const [ventesRes, topRes] = await Promise.all([
    api.get('/rapports/ventes', { params: filtres }),
    api.get('/rapports/top-produits', {
      params: { limit: 10, debut: filtres.debut, fin: filtres.fin }
    }),
  ])
  ventesData.value  = ventesRes.data
  topProduits.value = topRes.data
}

function exporterPDF() {
  window.open(buildExportUrl('/rapports/export-pdf', filtres), '_blank')
}

function exporterPDFStock() {
  window.open(buildExportUrl('/rapports/export-pdf-stock'), '_blank')
}

function exporterExcel() {
  window.open(buildExportUrl('/rapports/export-excel', filtres), '_blank')
}

function exporterExcelComplet() {
  window.open(buildExportUrl('/rapports/export-excel-complet', filtres), '_blank')
}

function exporterExcelStock() {
  window.open(buildExportUrl('/rapports/export-excel-stock'), '_blank')
}

function formatMoney(val) {
  return Number(val || 0).toLocaleString('fr-FR') + ' FCFA'
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR', { weekday: 'short', day: '2-digit', month: 'short' })
}

onMounted(chargerRapport)
</script>

<style scoped>
@reference "tailwindcss";

.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400; }
</style>