import axios from 'axios';

const instance = axios.create({
  baseURL: (import.meta.env.VITE_API_URL || 'https://tidyback-production.up.railway.app/api') + '/login/',
  // timeout: 10000, // Puedes ajustar el tiempo de espera
});

export default instance;
