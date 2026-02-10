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

const handleRemoveFavorite = async (locationId) => {
  if (locationId === 'default') return;
  
  try {
    const response = await fetch(`/api/favorites/${locationId}`, {
      method: 'DELETE',
    });
    
    if (response.ok) {
      await loadFavorites();
    }
  } catch (error) {
    console.error('Erreur lors de la suppression du favori:', error);
  }
};

onMounted(() => {
  loadFavorites();
});
</script>

<style scoped>
.app {
  min-height: 100vh;
}
</style>
