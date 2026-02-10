<template>
  <div class="app">
    <WeatherDisplay />
  </div>
</template>

<script setup>
import WeatherDisplay from './components/WeatherDisplay.vue';
</script>

<style scoped>
.app {
  min-height: 100vh;
  background: linear-gradient(to bottom, #e3f2fd, #bbdefb);
}
</style>
