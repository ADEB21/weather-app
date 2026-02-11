<template>
  <div class="weather-details">
    <div class="details-grid">
      <!-- Ressenti -->
      <div class="detail-card">
        <div class="detail-header">
          <svg class="detail-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path>
          </svg>
          <span class="detail-title">RESSENTI</span>
        </div>
        <div class="detail-value-large">{{ feelsLike }}°</div>
        <div class="detail-description">Réelle : {{ temperature }}°</div>
      </div>

      <!-- Indice UV -->
      <div class="detail-card">
        <div class="detail-header">
          <svg class="detail-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v2"></path>
            <path d="M12 20v2"></path>
            <path d="m4.93 4.93 1.41 1.41"></path>
            <path d="m17.66 17.66 1.41 1.41"></path>
            <path d="M2 12h2"></path>
            <path d="M20 12h2"></path>
            <path d="m6.34 17.66-1.41 1.41"></path>
            <path d="m19.07 4.93-1.41 1.41"></path>
          </svg>
          <span class="detail-title">INDICE UV</span>
        </div>
        <div class="detail-value-large">{{ uvIndex }}</div>
        <div class="detail-description">{{ getUVDescription(uvIndex) }}</div>
      </div>

      <!-- Vent -->
      <div class="detail-card">
        <div class="detail-header">
          <svg class="detail-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"></path>
          </svg>
          <span class="detail-title">VENT</span>
        </div>
        <div class="detail-value-large">{{ windSpeed }} km/h</div>
        <div class="detail-description">
          Rafales : {{ windGusts }} km/h<br>
          Direction : {{ windDirection }}
        </div>
      </div>

      <!-- Précipitations -->
      <div class="detail-card">
        <div class="detail-header">
          <svg class="detail-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 13v8m-8-8v8m-4-6v6m16-4v4M4 4.5A3.5 3.5 0 0 1 7.5 1 3.5 3.5 0 0 1 11 4.5V6a4 4 0 0 1 4 4v7"></path>
          </svg>
          <span class="detail-title">PRÉCIPITATIONS</span>
        </div>
        <div class="detail-value-large">{{ precipitation }} mm</div>
        <div class="detail-description">dans les dernières 24h</div>
      </div>

      <!-- Humidité -->
      <div class="detail-card">
        <div class="detail-header">
          <svg class="detail-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
          </svg>
          <span class="detail-title">HUMIDITÉ</span>
        </div>
        <div class="detail-value-large">{{ humidity }}%</div>
        <div class="detail-description">{{ getHumidityDescription(humidity) }}</div>
      </div>

      <!-- Lever/Coucher du soleil -->
      <div class="detail-card">
        <div class="detail-header">
          <svg class="detail-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2v8m-7.07 1.93 1.41 1.41M2 12h2m16 0h2m-2.93-5.66 1.41-1.41M22 17H2"></path>
            <path d="M8 19a4 4 0 0 0 8 0"></path>
          </svg>
          <span class="detail-title">LEVER/COUCHER</span>
        </div>
        <div class="detail-sun-times">
          <div class="sun-time">
            <span class="sun-label">Lever</span>
            <span class="sun-value">{{ sunrise }}</span>
          </div>
          <div class="sun-time">
            <span class="sun-label">Coucher</span>
            <span class="sun-value">{{ sunset }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  temperature: Number,
  feelsLike: Number,
  uvIndex: Number,
  windSpeed: Number,
  windGusts: Number,
  windDirection: String,
  precipitation: Number,
  humidity: Number,
  sunrise: String,
  sunset: String
});

const getUVDescription = (uv) => {
  if (uv <= 2) return 'Faible';
  if (uv <= 5) return 'Modéré';
  if (uv <= 7) return 'Élevé';
  if (uv <= 10) return 'Très élevé';
  return 'Extrême';
};

const getHumidityDescription = (humidity) => {
  if (humidity < 30) return 'Très sec';
  if (humidity < 50) return 'Sec';
  if (humidity < 70) return 'Confortable';
  if (humidity < 85) return 'Humide';
  return 'Très humide';
};
</script>

<style scoped>
.weather-details {
  padding: 1rem 0 2rem;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.detail-card {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 1rem;
  color: white;
}

.day-mode .detail-card {
  background: rgb(57 130 163 / 36%)
}

.detail-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  opacity: 0.7;
}

.detail-icon {
  width: 16px;
  height: 16px;
  opacity: 0.9;
}

.detail-title {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.detail-value-large {
  font-size: 2rem;
  font-weight: 300;
  margin-bottom: 0.25rem;
}

.detail-description {
  font-size: 0.9rem;
  opacity: 0.8;
  line-height: 1.4;
}

.detail-sun-times {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.sun-time {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1rem;
}

.sun-label {
  opacity: 0.8;
}

.sun-value {
  font-weight: 500;
  font-size: 1.1rem;
}
</style>
