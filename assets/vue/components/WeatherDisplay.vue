<template>
  <div class="weather-container" :class="backgroundClass">
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Chargement...</p>
    </div>

    <div v-else-if="error" class="error">
      <p>❌ {{ error }}</p>
    </div>

    <div v-else-if="weather" class="weather-content">
      <div class="top-bar">
        <div class="location" @click="showSearchModal = true">
          <span class="location-icon">📍</span>
          <span class="location-name">{{ currentCity }}</span>
          <span class="dropdown-icon">▼</span>
        </div>
        <button @click="showSearchModal = true" class="search-btn">
          🔍
        </button>
      </div>

      <CurrentWeather
        :weatherIcon="getWeatherIcon(weather.current.weather_code)"
        :temperature="Math.round(weather.current.temperature_2m)"
        :condition="getWeatherDescription(weather.current.weather_code)"
        :maxTemp="Math.round(weather.daily.temperature_2m_max[0])"
        :minTemp="Math.round(weather.daily.temperature_2m_min[0])"
        :humidity="weather.current.relative_humidity_2m"
        :windSpeed="Math.round(weather.current.wind_speed_10m)"
        :precipitation="weather.current.precipitation"
      />

      <div class="section-title">Aujourd'hui</div>
      <HourlyForecast :hourlyData="hourlyForecastData" />

      <DailyForecast :dailyData="dailyForecastData" />
    </div>

    <div v-if="showSearchModal" class="modal-overlay" @click="showSearchModal = false">
      <div class="modal-content" @click.stop>
        <button @click="showSearchModal = false" class="close-btn">✕</button>
        <SearchBar @citySelected="onCitySelectedFromModal" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import SearchBar from './SearchBar.vue';
import CurrentWeather from './CurrentWeather.vue';
import HourlyForecast from './HourlyForecast.vue';
import DailyForecast from './DailyForecast.vue';

const emit = defineEmits(['favoriteAdded']);

const weather = ref(null);
const loading = ref(true);
const error = ref(null);
const updateTime = ref('');
const currentCity = ref('Dijon');
const currentLocation = ref({ latitude: 47.3220, longitude: 5.0415 });
const isFavorite = ref(false);
const favoriteId = ref(null);
const showSearchModal = ref(false);
const isNightTime = ref(false);

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
  
  return date.toLocaleDateString('fr-FR', { weekday: 'long' });
};

const formatHour = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const checkNightTime = () => {
  const hour = new Date().getHours();
  isNightTime.value = hour < 6 || hour >= 17;
};

const backgroundClass = computed(() => {
  return isNightTime.value ? 'night-mode' : 'day-mode';
});

const hourlyForecastData = computed(() => {
  if (!weather.value || !weather.value.hourly) return [];
  
  return weather.value.hourly.time.slice(0, 24).map((time, index) => ({
    time: formatHour(time),
    icon: getWeatherIcon(weather.value.hourly.weather_code[index]),
    temp: Math.round(weather.value.hourly.temperature_2m[index])
  }));
});

const dailyForecastData = computed(() => {
  if (!weather.value || !weather.value.daily) return [];
  
  return weather.value.daily.time.slice(1, 7).map((date, index) => ({
    day: formatDate(date),
    icon: getWeatherIcon(weather.value.daily.weather_code[index + 1]),
    maxTemp: Math.round(weather.value.daily.temperature_2m_max[index + 1]),
    minTemp: Math.round(weather.value.daily.temperature_2m_min[index + 1])
  }));
});

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

const onCitySelectedFromModal = (cityData) => {
  onCitySelected(cityData);
  showSearchModal.value = false;
};

onMounted(() => {
  fetchWeather();
  checkFavoriteStatus(currentLocation.value.latitude, currentLocation.value.longitude);
  checkNightTime();
  
  setInterval(checkNightTime, 60000);
  
  window.addEventListener('selectCity', (event) => {
    const cityData = event.detail;
    onCitySelected(cityData);
  });
});
</script>

<style scoped>
.weather-container {
  min-height: 100vh;
  transition: background 0.5s ease;
  position: relative;
  overflow-x: hidden;
}

.weather-container.day-mode {
  background: linear-gradient(180deg, #4A90E2 0%, #87CEEB 50%, #B0E0E6 100%);
}

.weather-container.night-mode {
  background: linear-gradient(180deg, #0F2027 0%, #203A43 50%, #2C5364 100%);
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  color: white;
}

.spinner {
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top: 4px solid white;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 2rem;
  margin: 2rem;
  text-align: center;
  color: white;
  font-size: 1.1rem;
}

.weather-content {
  max-width: 500px;
  margin: 0 auto;
  padding: 1rem;
  padding-bottom: 2rem;
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1rem;
  color: white;
}

.location {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: opacity 0.2s;
}

.location:hover {
  opacity: 0.8;
}

.location-icon {
  font-size: 1.25rem;
}

.location-name {
  font-size: 1.1rem;
  font-weight: 600;
}

.dropdown-icon {
  font-size: 0.7rem;
  opacity: 0.7;
}

.search-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 45px;
  height: 45px;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
}

.search-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.section-title {
  color: white;
  font-size: 1rem;
  font-weight: 600;
  padding: 0 1rem;
  margin-top: 1.5rem;
  margin-bottom: 0.5rem;
  opacity: 0.9;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(5px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  width: 100%;
  max-width: 500px;
  position: relative;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(0, 0, 0, 0.05);
  border: none;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.1);
  transform: rotate(90deg);
}

@media (max-width: 768px) {
  .weather-content {
    padding: 4rem;
    max-width: 100%;
  }

  .top-bar {
    padding: 1rem 0.5rem;
  }

  .modal-content {
    padding: 1.5rem;
    max-width: 100%;
  }
}
</style>
