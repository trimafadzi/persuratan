import { create } from 'zustand';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { apiClient } from '../api/client';

interface User {
  id: number;
  name: string;
  email: string;
  username: string;
  nama_lengkap: string;
  unit_kerja?: {
    id: number;
    nama: string;
  };
  roles?: Array<{
    id: number;
    nama_role: string;
    slug: string;
  }>;
}

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (loginVal: string, passwordVal: string) => Promise<{ success: boolean; message: string }>;
  logout: () => Promise<void>;
  initialize: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,

  login: async (loginVal, passwordVal) => {
    try {
      const response = await apiClient.post('/auth/login', {
        login: loginVal,
        password: passwordVal,
      });

      const { token, user } = response.data;

      // Simpan ke AsyncStorage
      await AsyncStorage.setItem('@inoffice_token', token);
      await AsyncStorage.setItem('@inoffice_user', JSON.stringify(user));

      set({
        token,
        user,
        isAuthenticated: true,
        isLoading: false,
      });

      return { success: true, message: 'Login berhasil' };
    } catch (error: any) {
      const message = error.response?.data?.message || 'Terjadi kesalahan koneksi internet.';
      return { success: false, message };
    }
  },

  logout: async () => {
    try {
      // Panggil endpoint logout backend (optional, ignore failure if token already invalid)
      await apiClient.post('/auth/logout').catch(() => {});
    } finally {
      // Hapus data lokal dari storage dan reset state
      await AsyncStorage.removeItem('@inoffice_token');
      await AsyncStorage.removeItem('@inoffice_user');

      set({
        user: null,
        token: null,
        isAuthenticated: false,
        isLoading: false,
      });
    }
  },

  initialize: async () => {
    set({ isLoading: true });
    try {
      const token = await AsyncStorage.getItem('@inoffice_token');
      const userJson = await AsyncStorage.getItem('@inoffice_user');

      if (token && userJson) {
        // Set token sementara untuk request /me
        set({ token, user: JSON.parse(userJson), isAuthenticated: true });

        try {
          // Validasi token ke server untuk mendapatkan data profil terbaru
          const response = await apiClient.get('/auth/me');
          const freshUser = response.data.data;
          
          await AsyncStorage.setItem('@inoffice_user', JSON.stringify(freshUser));
          set({
            user: freshUser,
            isAuthenticated: true,
          });
        } catch (meError) {
          // Jika token tidak valid / expired / network error yang kritis, logout
          console.warn('[AuthStore] Gagal validasi token:', meError);
          // Hanya logout jika status code 401/403 (Unauthorized/Forbidden)
          const status = (meError as any).response?.status;
          if (status === 401 || status === 403) {
            await get().logout();
          }
        }
      }
    } catch (e) {
      console.error('[AuthStore] Error saat restorasi session:', e);
    } finally {
      set({ isLoading: false });
    }
  },
}));
