/**
 * inOffice Mobile — Theme System
 * Custom theme matching web branding
 */

export const COLORS = {
  primary: '#1a3a6b',
  primaryLight: '#2557a7',
  primaryDark: '#0f2347',
  accent: '#e63946',
  accentLight: '#ff6b6b',
  success: '#2d6a4f',
  successLight: '#40916c',
  warning: '#e9c46a',
  warningDark: '#c49a00',
  info: '#457b9d',
  danger: '#e63946',
  background: '#f0f4f8',
  backgroundDark: '#1a202c',
  cardBg: '#ffffff',
  text: '#1a202c',
  textMuted: '#718096',
  border: '#e2e8f0',
  white: '#ffffff',
  transparent: 'transparent',
  overlay: 'rgba(0, 0, 0, 0.4)',
};

export const SPACING = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  xxl: 24,
  xxxl: 32,
};

export const SIZES = {
  radiusSm: 8,
  radiusMd: 12,
  radiusLg: 16,
  radiusXl: 24,
};

export const FONTS = {
  regular: 'System',
  medium: 'System',
  semibold: 'System',
  bold: 'System',
};

export const DARK_COLORS = {
  primary: '#5b8cc9',
  primaryLight: '#7ba7d9',
  primaryDark: '#3a6ea5',
  accent: '#ff6b6b',
  accentLight: '#ff8a8a',
  success: '#52b788',
  successLight: '#74c69d',
  warning: '#f4d35e',
  warningDark: '#e9c46a',
  info: '#6db3d4',
  danger: '#ff6b6b',
  background: '#0f1419',
  backgroundDark: '#0a0e13',
  cardBg: '#1a202c',
  text: '#e2e8f0',
  textMuted: '#a0aec0',
  border: '#2d3748',
  white: '#1a202c',
  transparent: 'transparent',
  overlay: 'rgba(0, 0, 0, 0.6)',
};

export const SHADOWS = {
  sm: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 2,
    elevation: 2,
  },
  md: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.07,
    shadowRadius: 6,
    elevation: 4,
  },
  lg: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.1,
    shadowRadius: 15,
    elevation: 8,
  },
};

export type ThemeColors = typeof COLORS;

export function getTheme(isDark: boolean): { colors: ThemeColors } {
  return { colors: isDark ? DARK_COLORS : COLORS };
}
