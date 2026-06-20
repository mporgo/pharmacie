<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="text-center">
      <p class="text-8xl font-bold text-blue-200 mb-4">404</p>
      <h1 class="text-2xl font-bold text-gray-700 mb-2">Page introuvable</h1>
      <p class="text-gray-400 mb-6">Cette page n'existe pas.</p>
      <router-link
        to="/dashboard"
        class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700"
      >
        Retour au dashboard
      </router-link>
    </div>
  </div>
</template>