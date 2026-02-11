<template>
  <div class="weather-page" :class="backgroundClass">
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Chargement...</p>
    </div>

    <div v-else-if="error" class="error">
      <p>{{ error }}</p>
    </div>

    <div v-else-if="weather" class="weather-content">
      <div class="top-bar">
        <div class="location-header">
          <h1 class="location-name">{{ currentLocation?.city || location.city }}</h1>
        </div>
        <div class="actions">
          <button @click="toggleSearch" class="action-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <path d="m21 21-4.35-4.35"></path>
            </svg>
          </button>
          <button @click="toggleFavorite" class="action-btn favorite-btn" :class="{ active: isFavorite }">
            <svg width="20" height="20" viewBox="0 0 24 24" :fill="isFavorite ? 'currentColor' : 'none'"
              stroke="currentColor" stroke-width="2">
              <path
                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
              </path>
            </svg>
          </button>
        </div>
      </div>

      <div v-if="showSearch" class="search-overlay">
        <SearchBar @citySelected="onCitySelected" />
      </div>

      <CurrentWeather
        :temperature="Math.round(weather.current.temperature_2m)"
        :condition="getWeatherDescription(weather.current.weather_code)"
        :feelsLike="Math.round(weather.current.apparent_temperature)" />

      <div class="section-title">PRÉVISIONS HEURE PAR HEURE</div>
      <HourlyForecast :hourlyData="hourlyForecastData" />

      <div class="section-title">PRÉVISIONS SUR 10 JOURS</div>
      <DailyForecast :dailyData="dailyForecastData" />

      <WeatherDetails
        :temperature="Math.round(weather.current.temperature_2m)"
        :feelsLike="Math.round(weather.current.apparent_temperature)"
        :uvIndex="Math.round(weather.current.uv_index || 0)"
        :windSpeed="Math.round(weather.current.wind_speed_10m)"
        :windGusts="Math.round(weather.current.wind_gusts_10m || 0)"
        :windDirection="getWindDirection(weather.current.wind_direction_10m)"
        :precipitation="weather.current.precipitation"
        :humidity="weather.current.relative_humidity_2m"
        :sunrise="formatTime(weather.daily.sunrise[0])"
        :sunset="formatTime(weather.daily.sunset[0])" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import SearchBar from './SearchBar.vue';
import CurrentWeather from './CurrentWeather.vue';
import HourlyForecast from './HourlyForecast.vue';
import DailyForecast from './DailyForecast.vue';
import WeatherDetails from './WeatherDetails.vue';

const props = defineProps({
  location: {
    type: Object,
    required: true
  },
  isActive: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['addFavorite', 'removeFavorite']);

const weather = ref(null);
const loading = ref(true);
const error = ref(null);
const isNightTime = ref(false);
const showSearch = ref(false);
const isFavorite = ref(false);
const favoriteId = ref(null);
const currentLocation = ref(null);

const fetchWeather = async (latitude, longitude) => {
  try {
    loading.value = true;
    error.value = null;

    const lat = latitude || currentLocation.value?.latitude || props.location.latitude;
    const lon = longitude || currentLocation.value?.longitude || props.location.longitude;

    const response = await fetch(`/api/weather?latitude=${lat}&longitude=${lon}`);
    if (!response.ok) {
      throw new Error('Erreur lors de la récupération des données');
    }

    weather.value = await response.json();
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const checkNightTime = () => {
  const hour = new Date().getHours();
  isNightTime.value = hour < 6 || hour >= 17;
};

const backgroundClass = computed(() => {
  return isNightTime.value ? 'night-mode' : 'day-mode';
});

const formatHour = (dateString) => {
  const date = new Date(dateString);
  const hour = date.getHours();
  const now = new Date();
  if (date.getDate() === now.getDate() && hour === now.getHours()) {
    return 'Maint.';
  }
  return `${hour} h`;
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
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

const hourlyForecastData = computed(() => {
  if (!weather.value || !weather.value.hourly) return [];

  return weather.value.hourly.time.slice(0, 24).map((time, index) => ({
    time: formatHour(time),
    icon: getWeatherIcon(weather.value.hourly.weather_code[index]),
    temp: Math.round(weather.value.hourly.temperature_2m[index]),
    precipitation: weather.value.hourly.precipitation_probability[index] || 0
  }));
});

const dailyForecastData = computed(() => {
  if (!weather.value || !weather.value.daily) return [];

  return weather.value.daily.time.slice(1, 10).map((date, index) => ({
    day: formatDate(date),
    icon: getWeatherIcon(weather.value.daily.weather_code[index + 1]),
    maxTemp: Math.round(weather.value.daily.temperature_2m_max[index + 1]),
    minTemp: Math.round(weather.value.daily.temperature_2m_min[index + 1]),
    precipitation: weather.value.daily.precipitation_probability_max[index + 1] || 0
  }));
});

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

const getWindDirection = (degrees) => {
  const directions = ['Nord', 'Nord-Est', 'Est', 'Sud-Est', 'Sud', 'Sud-Ouest', 'Ouest', 'Nord-Ouest'];
  const index = Math.round(degrees / 45) % 8;
  return directions[index];
};

const toggleSearch = () => {
  showSearch.value = !showSearch.value;
};

const onCitySelected = (cityData) => {
  showSearch.value = false;
  // Mettre à jour la localisation actuelle et afficher la météo
  currentLocation.value = {
    city: cityData.city,
    latitude: cityData.latitude,
    longitude: cityData.longitude,
    country: cityData.country
  };
  fetchWeather(cityData.latitude, cityData.longitude);
  checkFavoriteStatus();
};

const checkFavoriteStatus = async () => {
  try {
    const lat = currentLocation.value?.latitude || props.location.latitude;
    const lon = currentLocation.value?.longitude || props.location.longitude;
    const response = await fetch(`/api/favorites/check?latitude=${lat}&longitude=${lon}`);
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
        emit('removeFavorite', props.location.id);
      }
    } else {
      const locationToAdd = currentLocation.value || props.location;
      const response = await fetch('/api/favorites', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          city: locationToAdd.city,
          country: locationToAdd.country || null,
          latitude: locationToAdd.latitude,
          longitude: locationToAdd.longitude,
        }),
      });
      
      if (response.ok || response.status === 409) {
        const data = await response.json();
        isFavorite.value = true;
        favoriteId.value = data.data.id;
        if (response.ok) {
          emit('addFavorite', locationToAdd);
        }
      }
    }
  } catch (err) {
    console.error('Erreur lors de la gestion du favori:', err);
  }
};

watch(() => props.isActive, (newVal) => {
  if (newVal) {
    currentLocation.value = null;
    if (!weather.value) {
      fetchWeather();
    }
    checkFavoriteStatus();
  }
});

onMounted(() => {
  if (props.isActive) {
    fetchWeather();
  }
  checkFavoriteStatus();
  checkNightTime();
  setInterval(checkNightTime, 60000);
});
</script>

<style scoped>
.weather-page {
  min-height: 100vh;
  transition: background 0.5s ease;
  position: relative;
  overflow-y: auto;
  overflow-x: hidden;
  height: 100%;
}

.weather-page.day-mode {
  background: linear-gradient(180deg, #4A90E2 0%, #87CEEB 50%, #B0E0E6 100%);
}

.weather-page.night-mode {
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
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.error {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  color: white;
  padding: 2rem;
  text-align: center;
}

.weather-content {
  max-width: 500px;
  margin: 0 auto;
  padding: 1rem;
  padding-bottom: 4rem;
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem 0rem 0.5rem;
  color: white;
}

.location-header {
  flex: 1;
}

.location-name {
  font-size: 2rem;
  font-weight: 300;
  margin: 0;
  color: white;
}

.actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.action-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
  color: white;
  backdrop-filter: blur(10px);
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.favorite-btn.active {
  background: rgba(255, 215, 0, 0.3);
}

.search-overlay {
  position: absolute;
  top: 80px;
  left: 0;
  right: 0;
  z-index: 100;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.section-title {
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  margin-top: 2rem;
  margin-bottom: 1rem;
  opacity: 0.7;
  text-transform: uppercase;
}

@media screen and (max-width: 1024px) {
  .weather-content {
    max-width: 100%;
    padding: 0 1rem 4rem 1rem;
  }

  .top-bar {
    padding: 1rem 1rem 0.5rem;
  }

  .location-name {
    font-size: 1.75rem;
  }
}
</style>
