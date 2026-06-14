import axios from 'axios';
import { useAuthStore } from '../store/authStore';

// Default base URL fallback ke Android emulator localhost (10.0.2.2)
// Untuk real device atau iOS, ganti dengan IP host lokal komputer Anda
const DEFAULT_API_URL = 'http://10.0.2.2:8000/api/v1';

export const apiClient = axios.create({
  baseURL: DEFAULT_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  timeout: 15000,
});

// Interceptor untuk request: Menyisipkan token Sanctum ke header Authorization
apiClient.interceptors.request.use(
  async (config) => {
    const token = useAuthStore.getState().token;
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor untuk response: Menangani session expired (401)
apiClient.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    // Jika server mengembalikan 401 Unauthorized, hapus session dan auto-logout
    if (error.response && error.response.status === 401) {
      const logout = useAuthStore.getState().logout;
      await logout();
    }
    return Promise.reject(error);
  }
);
