<template>
  <div class="current-weather">
    <div class="temperature">{{ temperature }}°</div>
    <div class="condition">{{ condition }} | Ressenti : {{ feelsLike }}°</div>
  </div>
</template>

<script setup>
const props = defineProps({
  temperature: Number,
  condition: String,
  feelsLike: Number
});
</script>

<style scoped>
.current-weather {
  text-align: center;
  padding: 1rem 0 2rem;
  color: white;
}

.temperature {
  font-size: 5rem;
  font-weight: 200;
  line-height: 1;
  margin-bottom: 0.5rem;
}

.condition {
  font-size: 1.1rem;
  opacity: 0.85;
  font-weight: 400;
}
</style>
