<template>
  <div class="hourly-forecast">
    <div class="forecast-scroll">
      <div 
        v-for="(hour, index) in hourlyData" 
        :key="index"
        class="hour-card"
        :class="{ 'current': index === 0 }"
      >
        <div class="hour-time">{{ hour.time }}</div>
        <div class="hour-icon">{{ hour.icon }}</div>
        <div class="hour-precipitation" v-if="hour.precipitation > 0">{{ hour.precipitation }}%</div>
        <div class="hour-temp">{{ hour.temp }}°</div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  hourlyData: {
    type: Array,
    default: () => []
  }
});
</script>

<style scoped>
.hourly-forecast {
  padding: 1rem 0;
  margin: 1rem 0;
}

.forecast-scroll {
  display: flex;
  gap: 1rem;
  overflow-x: auto;
  padding: 1rem;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

.forecast-scroll::-webkit-scrollbar {
  display: none;
}

.hour-card {
  flex: 0 0 auto;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 1rem 1.25rem;
  text-align: center;
  color: white;
  min-width: 80px;
  scroll-snap-align: start;
  transition: all 0.3s;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.hour-card.current {
  background: rgba(255, 255, 255, 0.35);
  border-color: rgba(255, 255, 255, 0.5);
  transform: scale(1.05);
}

.hour-time {
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
  opacity: 0.95;
}

.hour-icon {
  font-size: 2rem;
  margin: 0.5rem 0;
}

.hour-precipitation {
  font-size: 0.85rem;
  color: #5AC8FA;
  font-weight: 500;
  margin: 0.25rem 0;
}

.hour-temp {
  font-size: 1.1rem;
  font-weight: 700;
}

@media (max-width: 768px) {
  .forecast-scroll {
    padding: 0.75rem;
    gap: 0.75rem;
  }

  .hour-card {
    min-width: 70px;
    padding: 0.875rem 1rem;
  }

  .hour-icon {
    font-size: 1.75rem;
  }
}
</style>
