<template>
  <div class="app">
    <WeatherSwiper 
      :locations="allLocations"
      @addFavorite="handleAddFavorite"
      @removeFavorite="handleRemoveFavorite"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import WeatherSwiper from './components/WeatherSwiper.vue';

const allLocations = ref([]);

/**
 * Charge les favoris depuis l'API
 * Construit le tableau allLocations avec Dijon + favoris
 */
const loadFavorites = async () => {
  try {
    const response = await fetch('/api/favorites');
    if (response.ok) {
      const data = await response.json();
      const favorites = data.data || [];
      
      allLocations.value = [
        {
          id: 'default',
          city: 'Dijon',
          latitude: 47.3220,
          longitude: 5.0415,
          country: 'France'
        },
        ...favorites.map(fav => ({
          id: fav.id,
          city: fav.city,
          latitude: fav.latitude,
          longitude: fav.longitude,
          country: fav.country
        }))
      ];
    }
  } catch (error) {
    console.error('Erreur lors du chargement des favoris:', error);
    // Affiche au moins Dijon par défaut
    allLocations.value = [{
      id: 'default',
      city: 'Dijon',
      latitude: 47.3220,
      longitude: 5.0415,
      country: 'France'
    }];
  }
};

const handleAddFavorite = async () => {
  await loadFavorites();
};

/**
 * Gère la suppression d'un favori
 * 
 * @param {string|number} locationId L'ID de la localisation à supprimer
 */
const handleRemoveFavorite = async (locationId) => {
  // Empêche la suppression de la ville par défaut (Dijon)
  if (locationId === 'default') return;
  
  try {
    // Appelle API pour supprimer le favori
    const response = await fetch(`/api/favorites/${locationId}`, {
      method: 'DELETE',
    });
    
    // Si la suppression a réussi, recharge la liste
    if (response.ok) {
      await loadFavorites();
    }
  } catch (error) {
    console.error('Erreur lors de la suppression du favori:', error);
  }
};

onMounted(() => {
  loadFavorites(); // Charge les favoris au démarrage de l'app
});
</script>

<style scoped>
.app {
  min-height: 100vh;
}
</style>
