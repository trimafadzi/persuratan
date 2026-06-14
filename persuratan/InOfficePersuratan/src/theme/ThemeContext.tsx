import React, { createContext, useContext, useEffect, useMemo } from 'react';
import { useThemeStore } from '../store/themeStore';
import { getTheme, ThemeColors, SPACING, SIZES, SHADOWS } from './theme';

interface ThemeContextValue {
  colors: ThemeColors;
  isDark: boolean;
  toggleTheme: () => void;
}

const ThemeContext = createContext<ThemeContextValue>({
  colors: getTheme(false).colors,
  isDark: false,
  toggleTheme: () => {},
});

export const ThemeProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { isDark, toggleTheme, initialize } = useThemeStore();

  useEffect(() => {
    initialize();
  }, [initialize]);

  const value = useMemo(() => {
    const { colors } = getTheme(isDark);
    return { colors, isDark, toggleTheme };
  }, [isDark, toggleTheme]);

  return (
    <ThemeContext.Provider value={value}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => useContext(ThemeContext);

// Re-export static theme constants for convenience
export { SPACING, SIZES, SHADOWS };
