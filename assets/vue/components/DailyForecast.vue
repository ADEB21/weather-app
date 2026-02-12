<template>
  <div class="daily-forecast">
    <div class="forecast-header">
      <h3>Prochaines prévisions</h3>
    </div>
    <div class="forecast-list">
      <div v-for="(day, index) in dailyData" :key="index" class="day-item">
        <div class="day-name">{{ day.day.charAt(0).toUpperCase() + day.day.slice(1) }}</div>
        <div class="day-icon-wrapper">
          <div class="day-icon">{{ day.icon }}</div>
          <div class="day-precipitation" v-if="day.precipitation > 0">{{ day.precipitation }}%</div>
        </div>
        <div class="temp-bar-container">
          <span class="temp-min">{{ day.minTemp }}°</span>
          <div class="temp-bar">
            <div class="temp-bar-fill" :style="getTempBarStyle(day.minTemp, day.maxTemp)"></div>
          </div>
          <span class="temp-max">{{ day.maxTemp }}°</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  dailyData: Array
});

const getTempBarStyle = (min, max) => {
  const allTemps = props.dailyData.flatMap(d => [d.minTemp, d.maxTemp]);
  const globalMin = Math.min(...allTemps);
  const globalMax = Math.max(...allTemps);
  const range = globalMax - globalMin;

  const leftPercent = ((min - globalMin) / range) * 100;
  const widthPercent = ((max - min) / range) * 100;

  return {
    left: `${leftPercent}%`,
    width: `${widthPercent}%`
  };
};
</script>

<style scoped>
.daily-forecast {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  padding: 1.5rem;
  margin: 1rem 0;
  color: white;
}

.day-mode .daily-forecast {
  background: rgb(57 130 163 / 36%)
}

.forecast-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.forecast-header h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
}

.forecast-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.day-item {
  display: grid;
  grid-template-columns: 60px 80px 1fr;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.day-item:last-child {
  border-bottom: none;
}

.day-name {
  font-size: 1rem;
  font-weight: 400;
}

.day-icon-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.day-icon {
  font-size: 1.5rem;
}

.day-precipitation {
  font-size: 0.85rem;
  color: #94ddff;
  font-weight: 500;
}

.temp-bar-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
}

.temp-min,
.temp-max {
  font-size: 1rem;
  min-width: 35px;
  text-align: center;
}

.temp-min {
  opacity: 0.6;
}

.temp-max {
  font-weight: 500;
}

.temp-bar {
  flex: 1;
  height: 4px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 2px;
  position: relative;
}

.day-mode .hour-card {
  background: rgba(255, 255, 255, 0.1);
}

.temp-bar-fill {
  position: absolute;
  height: 100%;
  background: linear-gradient(90deg, #5AC8FA, #FF9500);
  border-radius: 2px;
}

@media (max-width: 768px) {
  .daily-forecast {
    padding: 1.25rem;
  }

  .day-name {
    font-size: 0.95rem;
    min-width: 70px;
  }

  .day-icon {
    font-size: 1.5rem;
    margin: 0 0.75rem;
  }

  .day-temps {
    font-size: 0.95rem;
    min-width: 70px;
  }
}
</style>
