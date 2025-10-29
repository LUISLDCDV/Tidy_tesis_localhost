import axios from 'axios';

const instance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'https://tidyback-production.up.railway.app/',
  // timeout: 10000, // Puedes ajustar el tiempo de espera
  withCredentials: false,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

export default instance;
