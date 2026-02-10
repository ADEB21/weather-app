<template>
  <div class="weather-container">
    <div class="header">
      <h1>Météo</h1>
      <SearchBar @citySelected="onCitySelected" />
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Chargement des données météo...</p>
    </div>

    <div v-else-if="error" class="error">
      <p>❌ {{ error }}</p>
    </div>

    <div v-else-if="weather" class="weather-content">
      <div class="location-info">
        <div class="location-header">
          <h2>{{ currentCity }}</h2>
          <button @click="toggleFavorite" class="favorite-btn" :class="{ active: isFavorite }">
            {{ isFavorite ? '★' : '☆' }} {{ isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' }}
          </button>
        </div>
        <p class="update-time">Dernière mise à jour : {{ updateTime }}</p>
      </div>

      <div class="current-weather">
        <h2>Météo actuelle</h2>
        <div class="current-grid">
          <div class="weather-card">
            <div class="icon">🌡️</div>
            <div class="label">Température</div>
            <div class="value">{{ weather.current.temperature_2m }}°C</div>
          </div>
          <div class="weather-card">
            <div class="icon">🤔</div>
            <div class="label">Ressenti</div>
            <div class="value">{{ weather.current.apparent_temperature }}°C</div>
          </div>
          <div class="weather-card">
            <div class="icon">💧</div>
            <div class="label">Humidité</div>
            <div class="value">{{ weather.current.relative_humidity_2m }}%</div>
          </div>
          <div class="weather-card">
            <div class="icon">🌧️</div>
            <div class="label">Précipitations</div>
            <div class="value">{{ weather.current.precipitation }} mm</div>
          </div>
          <div class="weather-card">
            <div class="icon">💨</div>
            <div class="label">Vent</div>
            <div class="value">{{ weather.current.wind_speed_10m }} km/h</div>
            <div class="sub-value">{{ getWindDirection(weather.current.wind_direction_10m) }}</div>
          </div>
          <div class="weather-card">
            <div class="icon">{{ getWeatherIcon(weather.current.weather_code) }}</div>
            <div class="label">Conditions</div>
            <div class="value">{{ getWeatherDescription(weather.current.weather_code) }}</div>
          </div>
        </div>
      </div>

      <div class="forecast">
        <h2>Prévisions sur 7 jours</h2>
        <div class="forecast-grid">
          <div 
            v-for="(date, index) in weather.daily.time" 
            :key="index"
            class="forecast-card"
          >
            <div class="forecast-date">{{ formatDate(date) }}</div>
            <div class="forecast-icon">{{ getWeatherIcon(weather.daily.weather_code[index]) }}</div>
            <div class="forecast-temps">
              <span class="temp-max">{{ weather.daily.temperature_2m_max[index] }}°</span>
              <span class="temp-min">{{ weather.daily.temperature_2m_min[index] }}°</span>
            </div>
            <div class="forecast-details">
              <div>💧 {{ weather.daily.precipitation_sum[index] }} mm</div>
              <div>💨 {{ weather.daily.wind_speed_10m_max[index] }} km/h</div>
              <div class="wind-dir">{{ getWindDirection(weather.daily.wind_direction_10m_dominant[index]) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import SearchBar from './SearchBar.vue';

const emit = defineEmits(['favoriteAdded']);

const weather = ref(null);
const loading = ref(true);
const error = ref(null);
const updateTime = ref('');
const currentCity = ref('Dijon');
const currentLocation = ref({ latitude: 47.3220, longitude: 5.0415 });
const isFavorite = ref(false);
const favoriteId = ref(null);

const fetchWeather = async (latitude = 47.3220, longitude = 5.0415) => {
  try {
    loading.value = true;
    error.value = null;
    
    const response = await fetch(`/api/weather?latitude=${latitude}&longitude=${longitude}`);
    if (!response.ok) {
      throw new Error('Erreur lors de la récupération des données');
    }
    
    weather.value = await response.json();
    updateTime.value = new Date().toLocaleTimeString('fr-FR');
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);
  
  if (date.toDateString() === today.toDateString()) {
    return "Aujourd'hui";
  } else if (date.toDateString() === tomorrow.toDateString()) {
    return 'Demain';
  }
  
  return date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
};

const getWindDirection = (degrees) => {
  const directions = ['Nord', 'Nord-Est', 'Est', 'Sud-Est', 'Sud', 'Sud-Ouest', 'Ouest', 'Nord-Ouest'];
  const index = Math.round(degrees / 45) % 8;
  return directions[index];
};

const getWeatherIcon = (code) => {
  const icons = {
    0: '☀️',
    1: '🌤️',
    2: '⛅',
    3: '☁️',
    45: '🌫️',
    48: '🌫️',
    51: '🌦️',
    53: '🌦️',
    55: '🌦️',
    61: '🌧️',
    63: '🌧️',
    65: '🌧️',
    71: '🌨️',
    73: '🌨️',
    75: '🌨️',
    77: '🌨️',
    80: '🌦️',
    81: '🌧️',
    82: '⛈️',
    85: '🌨️',
    86: '🌨️',
    95: '⛈️',
    96: '⛈️',
    99: '⛈️'
  };
  return icons[code] || '🌤️';
};

const getWeatherDescription = (code) => {
  const descriptions = {
    0: 'Ciel dégagé',
    1: 'Principalement dégagé',
    2: 'Partiellement nuageux',
    3: 'Couvert',
    45: 'Brouillard',
    48: 'Brouillard givrant',
    51: 'Bruine légère',
    53: 'Bruine modérée',
    55: 'Bruine dense',
    61: 'Pluie légère',
    63: 'Pluie modérée',
    65: 'Pluie forte',
    71: 'Neige légère',
    73: 'Neige modérée',
    75: 'Neige forte',
    77: 'Grains de neige',
    80: 'Averses légères',
    81: 'Averses modérées',
    82: 'Averses violentes',
    85: 'Averses de neige légères',
    86: 'Averses de neige fortes',
    95: 'Orage',
    96: 'Orage avec grêle légère',
    99: 'Orage avec grêle forte'
  };
  return descriptions[code] || 'Inconnu';
};

const checkFavoriteStatus = async (latitude, longitude) => {
  try {
    const response = await fetch(`/api/favorites/check?latitude=${latitude}&longitude=${longitude}`);
    if (response.ok) {
      const data = await response.json();
      isFavorite.value = data.isFavorite;
      favoriteId.value = data.favoriteId;
    }
  } catch (err) {
    console.error('Erreur lors de la vérification du favori:', err);
  }
};

const toggleFavorite = async () => {
  try {
    if (isFavorite.value && favoriteId.value) {
      const response = await fetch(`/api/favorites/${favoriteId.value}`, {
        method: 'DELETE',
      });
      if (response.ok) {
        isFavorite.value = false;
        favoriteId.value = null;
      }
    } else {
      const response = await fetch('/api/favorites', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          city: currentCity.value,
          country: currentLocation.value.country || null,
          latitude: currentLocation.value.latitude,
          longitude: currentLocation.value.longitude,
        }),
      });
      if (response.ok) {
        const data = await response.json();
        isFavorite.value = true;
        favoriteId.value = data.data.id;
        emit('favoriteAdded');
      }
    }
  } catch (err) {
    console.error('Erreur lors de la gestion du favori:', err);
  }
};

const onCitySelected = (cityData) => {
  currentCity.value = cityData.city;
  currentLocation.value = {
    latitude: cityData.latitude,
    longitude: cityData.longitude,
    country: cityData.country
  };
  fetchWeather(cityData.latitude, cityData.longitude);
  checkFavoriteStatus(cityData.latitude, cityData.longitude);
};

onMounted(() => {
  fetchWeather();
  checkFavoriteStatus(currentLocation.value.latitude, currentLocation.value.longitude);
  
  window.addEventListener('selectCity', (event) => {
    const cityData = event.detail;
    onCitySelected(cityData);
  });
});
</script>

<style scoped>
.weather-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.loading {
  text-align: center;
  padding: 3rem;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
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

.error {
  background: #fee;
  border: 1px solid #fcc;
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
  color: #c00;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 2.5rem;
  margin: 0 0 1.5rem 0;
  color: #2c3e50;
}

.location-info {
  text-align: center;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  color: white;
}

.location-header {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.location-info h2 {
  font-size: 2rem;
  margin: 0;
  font-weight: bold;
}

.favorite-btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
  padding: 0.6rem 1.2rem;
  border-radius: 25px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.favorite-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-2px);
}

.favorite-btn.active {
  background: rgba(255, 215, 0, 0.3);
  border-color: rgba(255, 215, 0, 0.6);
}

.favorite-btn.active:hover {
  background: rgba(255, 215, 0, 0.4);
}

.update-time {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.current-weather {
  margin-bottom: 3rem;
}

.current-weather h2 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #34495e;
}

.current-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.weather-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s;
}

.weather-card:hover {
  transform: translateY(-5px);
}

.weather-card .icon {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.weather-card .label {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-bottom: 0.5rem;
}

.weather-card .value {
  font-size: 1.8rem;
  font-weight: bold;
}

.weather-card .sub-value {
  font-size: 1rem;
  margin-top: 0.3rem;
  opacity: 0.9;
}

.forecast h2 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #34495e;
}

.forecast-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.forecast-card {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  padding: 1rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
}

.forecast-card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.forecast-date {
  font-weight: bold;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.forecast-icon {
  font-size: 2.5rem;
  margin: 0.5rem 0;
}

.forecast-temps {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin: 0.5rem 0;
  font-size: 1.2rem;
}

.temp-max {
  color: #e74c3c;
  font-weight: bold;
}

.temp-min {
  color: #3498db;
}

.forecast-details {
  font-size: 0.85rem;
  color: #7f8c8d;
  margin-top: 0.5rem;
}

.forecast-details > div {
  margin: 0.2rem 0;
}

.wind-dir {
  font-weight: bold;
  color: #95a5a6;
}

@media (max-width: 768px) {
  .weather-container {
    padding: 1rem;
  }
  
  .header h1 {
    font-size: 2rem;
  }
  
  .current-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
  
  .forecast-grid {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  }
}
</style>
