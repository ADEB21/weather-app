<template>
  <div class="app">
    <nav class="main-nav">
      <button 
        @click="currentView = 'weather'" 
        :class="{ active: currentView === 'weather' }"
        class="nav-btn"
      >
        Météo
      </button>
      <button 
        @click="currentView = 'favorites'" 
        :class="{ active: currentView === 'favorites' }"
        class="nav-btn"
      >
        Favoris
      </button>
    </nav>

    <WeatherDisplay 
      v-if="currentView === 'weather'" 
      @favoriteAdded="onFavoriteAdded"
    />
    <FavoritesList 
      v-else 
      ref="favoritesListRef"
      @favoriteSelected="onFavoriteSelected"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import WeatherDisplay from './components/WeatherDisplay.vue';
import FavoritesList from './components/FavoritesList.vue';

const currentView = ref('weather');
const favoritesListRef = ref(null);

const onFavoriteAdded = () => {
  if (favoritesListRef.value) {
    favoritesListRef.value.loadFavorites();
  }
};

const onFavoriteSelected = (cityData) => {
  currentView.value = 'weather';
  // L'événement sera géré par WeatherDisplay via un système d'événements global
  window.dispatchEvent(new CustomEvent('selectCity', { detail: cityData }));
};
</script>

<style scoped>
.app {
  min-height: 100vh;
  background: linear-gradient(to bottom, #e3f2fd, #bbdefb);
}

.main-nav {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  display: flex;
  justify-content: center;
  gap: 1rem;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-btn {
  background: transparent;
  border: 2px solid #e0e0e0;
  padding: 0.75rem 2rem;
  border-radius: 25px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  color: #7f8c8d;
}

.nav-btn:hover {
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-2px);
}

.nav-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: transparent;
  color: white;
}

@media (max-width: 768px) {
  .main-nav {
    padding: 0.75rem;
  }

  .nav-btn {
    padding: 0.6rem 1.5rem;
    font-size: 0.9rem;
  }
}
</style>
