import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept':       'application/json',
  },
})

// Injecter token sur chaque requête
api.interceptors.request.use(config => {
  const token = localStorage.getItem('pharma_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Gérer 401 globalement
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('pharma_token')
      localStorage.removeItem('pharma_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// ✅ Helper export avec token dans l'URL
// Utilisé pour les téléchargements (PDF/Excel)
export function buildExportUrl(path, params = {}) {
  const token  = localStorage.getItem('pharma_token')
  const base   = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  const query  = new URLSearchParams({ ...params, token }).toString()
  return `${base}${path}?${query}`
}

export default api