<template>
  <div class="favorites-container">
    <h2>Mes Favoris</h2>
    
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Chargement des favoris...</p>
    </div>

    <div v-else-if="favorites.length === 0" class="empty-state">
      <p>Aucun favori pour le moment</p>
      <p class="hint">Recherchez une ville et ajoutez-la à vos favoris !</p>
    </div>

    <div v-else class="favorites-grid">
      <div
        v-for="favorite in favorites"
        :key="favorite.id"
        class="favorite-card"
        @click="selectFavorite(favorite)"
      >
        <div class="favorite-header">
          <h3>{{ favorite.city || 'Ville inconnue' }}</h3>
          <button
            @click.stop="removeFavorite(favorite.id)"
            class="remove-btn"
            title="Retirer des favoris"
          >
            ✕
          </button>
        </div>
        <p class="favorite-country">{{ favorite.country }}</p>
        <p class="favorite-coords">
          {{ favorite.latitude.toFixed(4) }}, {{ favorite.longitude.toFixed(4) }}
        </p>
        <p class="favorite-date">
          Ajouté le {{ formatDate(favorite.addedAt) }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const emit = defineEmits(['favoriteSelected']);

const favorites = ref([]);
const loading = ref(true);

const loadFavorites = async () => {
  try {
    loading.value = true;
    const response = await fetch('/api/favorites');
    if (response.ok) {
      const data = await response.json();
      favorites.value = data.data || [];
    }
  } catch (error) {
    console.error('Erreur lors du chargement des favoris:', error);
  } finally {
    loading.value = false;
  }
};

const selectFavorite = (favorite) => {
  emit('favoriteSelected', {
    city: favorite.city,
    country: favorite.country,
    latitude: favorite.latitude,
    longitude: favorite.longitude
  });
};

const removeFavorite = async (id) => {
  if (!confirm('Voulez-vous vraiment retirer ce favori ?')) {
    return;
  }

  try {
    const response = await fetch(`/api/favorites/${id}`, {
      method: 'DELETE',
    });
    if (response.ok) {
      await loadFavorites();
    }
  } catch (error) {
    console.error('Erreur lors de la suppression du favori:', error);
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

onMounted(() => {
  loadFavorites();
});

defineExpose({ loadFavorites });
</script>

<style scoped>
.favorites-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.favorites-container h2 {
  font-size: 2rem;
  color: #2c3e50;
  margin-bottom: 1.5rem;
  text-align: center;
}

.loading {
  text-align: center;
  padding: 3rem;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 3rem;
  background: #f8f9fa;
  border-radius: 12px;
  color: #7f8c8d;
}

.empty-state p {
  font-size: 1.2rem;
  margin: 0.5rem 0;
}

.hint {
  font-size: 1rem !important;
  color: #95a5a6;
}

.favorites-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.favorite-card {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
}

.favorite-card:hover {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
  transform: translateY(-4px);
}

.favorite-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.favorite-card h3 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin: 0;
  font-weight: bold;
}

.remove-btn {
  background: rgba(231, 76, 60, 0.1);
  color: #e74c3c;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.remove-btn:hover {
  background: #e74c3c;
  color: white;
  transform: rotate(90deg);
}

.favorite-country {
  color: #7f8c8d;
  font-size: 1rem;
  margin: 0.5rem 0;
}

.favorite-coords {
  color: #95a5a6;
  font-size: 0.85rem;
  margin: 0.5rem 0;
}

.favorite-date {
  color: #bdc3c7;
  font-size: 0.8rem;
  margin-top: 1rem;
  font-style: italic;
}

@media (max-width: 768px) {
  .favorites-container {
    padding: 1rem;
  }

  .favorites-container h2 {
    font-size: 1.5rem;
  }

  .favorites-grid {
    grid-template-columns: 1fr;
  }
}
</style>
