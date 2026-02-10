<template>
  <div class="counter">
    <p>Compteur : {{ count }}</p>

    <button @click="count--">-</button>
    <button @click="count++">+</button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const count = ref(0);
</script>

<style scoped>
.counter {
  padding: 1rem;
  border: 1px solid #ccc;
  display: inline-block;
  margin-top: 1rem;
}

button {
  margin: 0 0.5rem;
}
</style>
