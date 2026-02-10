<template>
  <div class="search-container">
    <div class="search-box">
      <input
        v-model="searchQuery"
        @input="onSearchInput"
        @focus="showDropdown = true"
        @keydown.enter="selectFirstResult"
        type="text"
        placeholder="Rechercher une ville..."
        class="search-input"
      />
      
      <div v-if="showDropdown && (searchResults.length > 0 || searchHistory.length > 0)" class="dropdown">
        <!-- Résultats de recherche -->
        <div v-if="searchResults.length > 0" class="dropdown-section">
          <div class="section-title">Résultats</div>
          <div
            v-for="(result, index) in searchResults"
            :key="`result-${index}`"
            @click="selectCity(result)"
            class="dropdown-item"
          >
            <span class="city-name">{{ result.name }}</span>
            <span class="city-details">{{ result.admin1 ? `${result.admin1}, ` : '' }}{{ result.country }}</span>
          </div>
        </div>

        <!-- Historique des recherches -->
        <div v-if="searchHistory.length > 0 && searchQuery.length === 0" class="dropdown-section">
          <div class="section-header">
            <div class="section-title">Recherches récentes</div>
            <button @click="clearHistory" class="clear-btn" title="Supprimer toutes les recherches">
              Supprimer récents
            </button>
          </div>
          <div
            v-for="history in searchHistory"
            :key="history.id"
            @click="selectFromHistory(history)"
            class="dropdown-item history-item"
          >
            <span class="city-name">{{ history.city || 'Ville inconnue' }}</span>
            <span class="city-details">{{ history.country }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const emit = defineEmits(['citySelected']);

const searchQuery = ref('');
const searchResults = ref([]);
const searchHistory = ref([]);
const showDropdown = ref(false);
let searchTimeout = null;

const onSearchInput = () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }

  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(async () => {
    try {
      const response = await fetch(`/api/geocoding/search?q=${encodeURIComponent(searchQuery.value)}`);
      if (response.ok) {
        const data = await response.json();
        console.log("SearchResult", data);
        
        searchResults.value = data.data || [];
      }
    } catch (error) {
      console.error('Erreur lors de la recherche:', error);
    }
  }, 300);
};

const selectCity = async (city) => {
  searchQuery.value = city.name;
  showDropdown.value = false;
  searchResults.value = [];

  await saveToHistory(city);
  
  emit('citySelected', {
    city: city.name,
    country: city.country,
    latitude: city.latitude,
    longitude: city.longitude
  });
};

const selectFromHistory = (history) => {
  searchQuery.value = history.city;
  showDropdown.value = false;
  
  emit('citySelected', {
    city: history.city,
    country: history.country,
    latitude: history.latitude,
    longitude: history.longitude
  });
};

const selectFirstResult = () => {
  if (searchResults.value.length > 0) {
    selectCity(searchResults.value[0]);
  }
};

const saveToHistory = async (city) => {
  try {
    await fetch('/api/history', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        city: city.name,
        country: city.country,
        latitude: city.latitude,
        longitude: city.longitude,
      }),
    });
    await loadHistory();
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error);
  }
};

const loadHistory = async () => {
  try {
    const response = await fetch('/api/history?unique=true&limit=5');
    if (response.ok) {
      const data = await response.json();
      searchHistory.value = data.data || [];
    }
  } catch (error) {
    console.error('Erreur lors du chargement de l\'historique:', error);
  }
};

const clearHistory = async () => {
  if (!confirm('Voulez-vous vraiment supprimer toutes les recherches récentes ?')) {
    return;
  }

  try {
    const response = await fetch('/api/history/clear', {
      method: 'DELETE',
    });
    if (response.ok) {
      searchHistory.value = [];
      showDropdown.value = false;
    }
  } catch (error) {
    console.error('Erreur lors de la suppression de l\'historique:', error);
  }
};

onMounted(() => {
  loadHistory();
  
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-container')) {
      showDropdown.value = false;
    }
  });
});
</script>

<style scoped>
.search-container {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
  position: relative;
}

.search-box {
  position: relative;
  margin: 0px 16px;
}

.search-input {
  width: 100%;
  padding: 1rem 1.5rem;
  font-size: 1.1rem;
  border: none;
  border-radius: 12px;
  outline: none;
  transition: all 0.3s;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.search-input:focus {
  background: white;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.dropdown {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  max-height: 400px;
  overflow-y: auto;
  z-index: 1000;
}

.dropdown-section {
  padding: 0.5rem 0;
}

.dropdown-section:not(:last-child) {
  border-bottom: 1px solid #f0f0f0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
}

.section-title {
  font-size: 0.85rem;
  font-weight: bold;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.5rem 1rem;
}

.clear-btn {
  background: rgba(231, 76, 60, 0.1);
  color: #e74c3c;
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.clear-btn:hover {
  background: #e74c3c;
  color: white;
  transform: scale(1.05);
}

.dropdown-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dropdown-item:hover {
  background: #f8f9fa;
}

.history-item {
  background: #fafbfc;
}

.history-item:hover {
  background: #f0f2f5;
}

.city-name {
  font-weight: 600;
  color: #2c3e50;
  font-size: 1rem;
}

.city-details {
  font-size: 0.85rem;
  color: #7f8c8d;
}

@media (max-width: 768px) {
  .search-input {
    font-size: 1rem;
    padding: 0.875rem 1.25rem;
  }
  
  .dropdown {
    max-height: 300px;
  }
  
  .search-container {
    margin: 0;
  }
}
</style>
