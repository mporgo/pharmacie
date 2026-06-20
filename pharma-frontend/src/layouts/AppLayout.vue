<template>
  <div class="flex h-screen bg-gray-100 overflow-hidden">

    <!-- Sidebar -->
    <aside :class="['bg-blue-900 text-white flex flex-col transition-all duration-300',
                    sidebarOpen ? 'w-64' : 'w-16']">

      <!-- Logo -->
      <div class="flex items-center gap-3 px-4 py-4 border-b border-blue-700">
        <span class="text-2xl">💊</span>
        <span v-if="sidebarOpen" class="font-bold text-lg">Pharma</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 py-4 space-y-1 overflow-y-auto">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center gap-3 px-4 py-2.5 rounded-lg mx-2 transition hover:bg-blue-700"
          active-class="bg-blue-700"
        >
          <span class="text-xl flex-shrink-0">{{ item.icon }}</span>
          <span v-if="sidebarOpen" class="text-sm font-medium">{{ item.label }}</span>
        </router-link>
      </nav>

      <!-- Utilisateur -->
      <div class="border-t border-blue-700 p-4">
        <div v-if="sidebarOpen" class="mb-2">
          <p class="text-sm font-semibold">{{ auth.user?.name }}</p>
          <p class="text-xs text-blue-300">{{ auth.user?.roles?.[0] }}</p>
        </div>
        <button
          @click="logout"
          class="flex items-center gap-2 text-sm text-blue-300 hover:text-white transition"
        >
          <span>🚪</span>
          <span v-if="sidebarOpen">Déconnexion</span>
        </button>
      </div>
    </aside>

    <!-- Contenu principal -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Topbar -->
      <header class="bg-white border-b px-6 py-3 flex items-center justify-between shadow-sm">
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-800">
          <span class="text-xl">☰</span>
        </button>

        <!-- Alertes badge -->
        <div class="flex items-center gap-4">
          <router-link to="/stock" class="relative text-gray-500 hover:text-red-500">
            🔔
            <span v-if="totalAlertes > 0"
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
              {{ totalAlertes }}
            </span>
          </router-link>
          <span class="text-sm text-gray-600 font-medium">{{ auth.user?.name }}</span>
        </div>
      </header>

      <!-- Router View -->
      <main class="flex-1 overflow-y-auto p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'

const router     = useRouter()
const auth       = useAuthStore()
const sidebarOpen = ref(true)
const alertes    = ref({ totaux: { stock_faible: 0, expires: 0, expire_bientot: 0 } })

const totalAlertes = computed(() =>
  (alertes.value.totaux?.stock_faible || 0) +
  (alertes.value.totaux?.expires || 0)
)

const menuItems = computed(() => {
  const items = [
    { path: '/dashboard',    icon: '📊', label: 'Dashboard'    },
    { path: '/ventes',       icon: '🛒', label: 'Ventes'       },
    { path: '/medicaments',  icon: '💊', label: 'Médicaments'  },
    { path: '/stock',        icon: '📦', label: 'Stock'        },
    { path: '/achats',       icon: '🚚', label: 'Achats'       },
    { path: '/fournisseurs', icon: '🏭', label: 'Fournisseurs' },
    { path: '/rapports',     icon: '📈', label: 'Rapports'     },
  ]
  // ✅ Vérification robuste
  const roles = auth.user?.roles ?? []
  if (roles.includes('admin')) {
    items.push({ path: '/utilisateurs', icon: '👥', label: 'Utilisateurs' })
  }
  return items
})

async function logout() {
  await auth.logout()
  router.push('/login')
}

/* onMounted(async () => {
  try {
    const { data } = await api.get('/alertes')
    alertes.value = data
  } catch {}
}) */

let alerteInterval = null

onMounted(async () => {
  await chargerAlertes()
  // Rafraîchir toutes les 5 minutes
  alerteInterval = setInterval(chargerAlertes, 3 * 60 * 1000)
})

onUnmounted(() => {
  if (alerteInterval) clearInterval(alerteInterval)
})

async function chargerAlertes() {
  try {
    const { data } = await api.get('/alertes')
    alertes.value = data
  } catch {}
}

</script>