<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

    <!-- Cartes KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <KpiCard icon="🛒" label="Ventes du jour"
               :value="stats.ventes_jour?.nombre || 0" color="blue" />
      <KpiCard icon="💰" label="CA du jour"
               :value="formatMoney(stats.ventes_jour?.chiffre_affaires)" color="green" />
      <KpiCard icon="⚠️" label="Stock faible"
               :value="stats.stock_faible || 0" color="orange" />
      <KpiCard icon="🗓️" label="Médicaments expirés"
               :value="stats.expires || 0" color="red" />
    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- CA 7 jours -->
      <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Chiffre d'affaires — 7 derniers jours</h2>
        <Bar v-if="chartCA.labels.length" :data="chartCA" :options="chartOptions" />
        <p v-else class="text-gray-400 text-sm text-center py-8">Aucune donnée</p>
      </div>

      <!-- Top produits -->
      <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Top 5 produits du mois</h2>
        <Doughnut v-if="chartTop.labels.length" :data="chartTop" :options="chartOptions" />
        <p v-else class="text-gray-400 text-sm text-center py-8">Aucune donnée</p>
      </div>
    </div>

    <!-- Alertes rapides -->
    <div v-if="stats.stock_faible > 0 || stats.expires > 0" class="bg-red-50 border border-red-200 rounded-xl p-4">
      <h3 class="font-semibold text-red-700 mb-2">⚠️ Alertes en attente</h3>
      <p v-if="stats.stock_faible" class="text-sm text-red-600">
        {{ stats.stock_faible }} médicament(s) en rupture ou stock faible
      </p>
      <p v-if="stats.expires" class="text-sm text-red-600">
        {{ stats.expires }} médicament(s) expirés à retirer
      </p>
      <router-link to="/stock" class="mt-2 inline-block text-sm text-red-700 font-semibold underline">
        Voir le stock →
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import api from '@/services/api'
import KpiCard from '@/components/KpiCard.vue'

const stats = ref({})
const loading = ref(true)

const chartOptions = { responsive: true, plugins: { legend: { position: 'bottom' } } }

const chartCA = computed(() => ({
  labels: (stats.value.ca_7jours || []).map(d => d.date),
  datasets: [{
    label: 'CA (FCFA)',
    data: (stats.value.ca_7jours || []).map(d => d.total),
    backgroundColor: '#3B82F6',
    borderRadius: 6,
  }]
}))

const chartTop = computed(() => ({
  labels: (stats.value.top_produits || []).map(p => p.medicament?.nom || '?'),
  datasets: [{
    data: (stats.value.top_produits || []).map(p => p.total_vendu),
    backgroundColor: ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6'],
  }]
}))

function formatMoney(val) {
  if (!val) return '0 FCFA'
  return Number(val).toLocaleString('fr-FR') + ' FCFA'
}

onMounted(async () => {
  try {
    const { data } = await api.get('/rapports/dashboard')
    stats.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>