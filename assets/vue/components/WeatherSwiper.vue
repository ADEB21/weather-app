<template>
  <div class="weather-swiper">
    <div 
      class="swiper-container"
      @touchstart="handleTouchStart"
      @touchmove="handleTouchMove"
      @touchend="handleTouchEnd"
      @mousedown="handleMouseDown"
      @mousemove="handleMouseMove"
      @mouseup="handleMouseUp"
      @mouseleave="handleMouseUp"
      :style="{ transform: `translateX(${translateX}px)` }"
    >
      <div 
        v-for="(location, index) in locations" 
        :key="location.id"
        class="swiper-slide"
      >
        <WeatherPage 
          :location="location"
          :isActive="currentIndex === index"
          @addFavorite="$emit('addFavorite', $event)"
          @removeFavorite="$emit('removeFavorite', $event)"
        />
      </div>
    </div>

    <div class="page-indicators">
      <div 
        v-for="(location, index) in locations" 
        :key="`indicator-${index}`"
        class="indicator"
        :class="{ active: currentIndex === index }"
        @click="goToSlide(index)"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import WeatherPage from './WeatherPage.vue';

const props = defineProps({
  locations: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['addFavorite', 'removeFavorite']);

const currentIndex = ref(0);
const translateX = ref(0);
const startX = ref(0);
const currentX = ref(0);
const isDragging = ref(false);
const containerWidth = ref(0);

const handleTouchStart = (e) => {
  startX.value = e.touches[0].clientX;
  isDragging.value = true;
};

const handleTouchMove = (e) => {
  if (!isDragging.value) return;
  currentX.value = e.touches[0].clientX;
  const diff = currentX.value - startX.value;
  translateX.value = -currentIndex.value * containerWidth.value + diff;
};

const handleTouchEnd = () => {
  if (!isDragging.value) return;
  const diff = currentX.value - startX.value;
  const threshold = containerWidth.value / 4;

  if (diff < -threshold && currentIndex.value < props.locations.length - 1) {
    currentIndex.value++;
  } else if (diff > threshold && currentIndex.value > 0) {
    currentIndex.value--;
  }

  translateX.value = -currentIndex.value * containerWidth.value;
  isDragging.value = false;
};

const handleMouseDown = (e) => {
  startX.value = e.clientX;
  isDragging.value = true;
};

const handleMouseMove = (e) => {
  if (!isDragging.value) return;
  currentX.value = e.clientX;
  const diff = currentX.value - startX.value;
  translateX.value = -currentIndex.value * containerWidth.value + diff;
};

const handleMouseUp = () => {
  if (!isDragging.value) return;
  const diff = currentX.value - startX.value;
  const threshold = containerWidth.value / 4;

  if (diff < -threshold && currentIndex.value < props.locations.length - 1) {
    currentIndex.value++;
  } else if (diff > threshold && currentIndex.value > 0) {
    currentIndex.value--;
  }

  translateX.value = -currentIndex.value * containerWidth.value;
  isDragging.value = false;
};

const goToSlide = (index) => {
  currentIndex.value = index;
  translateX.value = -currentIndex.value * containerWidth.value;
};

onMounted(() => {
  containerWidth.value = document.documentElement.clientWidth;
  window.addEventListener('resize', () => {
    containerWidth.value = document.documentElement.clientWidth;
    translateX.value = -currentIndex.value * containerWidth.value;
  });
});
</script>

<style scoped>
.weather-swiper {
  position: relative;
  width: 100%;
  height: 100vh;
  overflow-x: hidden;
  overflow-y: auto;
}

.swiper-container {
  display: flex;
  height: 100%;
  transition: transform 0.3s ease-out;
  will-change: transform;
}

.swiper-slide {
  flex: 0 0 100%;
  width: 100%;
  min-height: 100%;
  overflow-y: auto;
}

.page-indicators {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 8px;
  z-index: 100;
}

.indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  transition: all 0.3s;
}

.indicator.active {
  background: white;
  width: 8px;
}

.indicator:hover {
  background: rgba(255, 255, 255, 0.7);
}
</style>
