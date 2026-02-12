/**
 * Point d'entrée de l'application Vue.js
 * 
 * Ce fichier initialise l'application Vue et la monte dans le DOM.
 * C'est le premier fichier JavaScript exécuté côté frontend.
 */

// Importe la fonction createApp depuis Vue.js
import { createApp } from 'vue';

// Importe le composant racine de l'application
import App from './App.vue';

// Crée l'application Vue avec le composant App et la monte sur l'élément #app du HTML
// L'élément #app se trouve dans templates/base.html.twig
createApp(App).mount('#app');
