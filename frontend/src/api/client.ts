import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL as string;

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
});

export const authClient = axios.create({
  baseURL: '/',
  withCredentials: true,
  // withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
});