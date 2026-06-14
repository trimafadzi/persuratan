import { create } from 'zustand';
import AsyncStorage from '@react-native-async-storage/async-storage';

const THEME_KEY = '@inoffice_theme';

interface ThemeState {
  isDark: boolean;
  isLoading: boolean;
  toggleTheme: () => void;
  initialize: () => Promise<void>;
}

export const useThemeStore = create<ThemeState>((set, get) => ({
  isDark: false,
  isLoading: true,

  toggleTheme: async () => {
    const newIsDark = !get().isDark;
    set({ isDark: newIsDark });
    try {
      await AsyncStorage.setItem(THEME_KEY, newIsDark ? 'dark' : 'light');
    } catch (e) {
      console.warn('[ThemeStore] Failed to persist theme:', e);
    }
  },

  initialize: async () => {
    try {
      const stored = await AsyncStorage.getItem(THEME_KEY);
      set({ isDark: stored === 'dark', isLoading: false });
    } catch {
      set({ isLoading: false });
    }
  },
}));
