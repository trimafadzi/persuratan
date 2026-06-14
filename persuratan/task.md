# Tugas Implementasi — inOffice Persuratan Mobile

## Fase 3.10: Native Mobile Features & Skeleton Loading ✅

### Skeleton Shimmer Loader
- `[x]` Pembuatan komponen `SkeletonLoader.tsx` dengan `CardListLoader`, `DashboardLoader`, `ChartLoader`
- `[x]` Animasi shimmer menggunakan native `Animated.loop` (opacity 0.3 <-> 0.7)
- `[x]` Penerapan skeleton pada 6 screen (Dashboard, SuratMasukList, SuratKeluarList, DisposisiList, Notifikasi, Laporan)

### Kamera Native Integration
- `[x]` Integrasi `launchCamera` pada `SuratMasukCreateScreen.tsx`, `SuratKeluarCreateScreen.tsx`, `DisposisiLaporanScreen.tsx`

### Offline Draft System
- `[x]` Sistem draf pada `SuratMasukCreateScreen.tsx`, `SuratKeluarCreateScreen.tsx`, `DisposisiCreateScreen.tsx`
- `[x]` Auto-clear draf setelah submit berhasil ke server

---

## Fase 3.11: UI/UX Mobile Polish ✅

### Bottom Navigation Redesign
- `[x]` Redesign tab bar: Dashboard | Surat | Disposisi | Notifikasi | Profil
- `[x]` Replace emoji icons with `react-native-vector-icons/MaterialCommunityIcons`
- `[x]` Move Surat Keluar screens into SuratMasukNavigator stack
- `[x]` Move Notifikasi from root stack to bottom tab
- `[x]` Add `tabBarHideOnKeyboard: true`

### Dark Mode Support
- `[x]` Create `themeStore.ts` — Zustand store with `isDark`, `toggleTheme()`, persisted to AsyncStorage
- `[x]` Add `DARK_COLORS` palette + `getTheme()` helper to `theme.ts`
- `[x]` Create `ThemeContext.tsx` with `ThemeProvider` + `useTheme()` hook
- `[x]` Wrap app with `ThemeProvider` in `App.tsx`
- `[x]` Update all 17 screens: replace `COLORS` with `useTheme().colors`
- `[x]` Add Dark Mode toggle switch in `ProfilScreen.tsx`

### Reusable EmptyState Component
- `[x]` Create `EmptyState.tsx` — icon, title, message, optional action button
- `[x]` Apply to 6 list screens (SuratMasukList, SuratKeluarList, DisposisiList, Notifikasi, Dashboard recent, Laporan kinerja)

### Reusable ErrorState Component
- `[x]` Create `ErrorState.tsx` — message + retry button
- `[x]` Apply to DashboardScreen error handling

### Enhanced Transitions & Polish
- `[x]` Add `gestureEnabled: true` to all stack navigators (swipe-back)
- `[x]` Add `animation: 'fade_from_bottom'` for Laporan screen (modal-like)
- `[x]` Consistent `slide_from_right` for all other screen transitions

### Verification
- `[x]` TypeScript check (`npx tsc --noEmit`) — PASS (0 errors)
- `[x]` ESLint check (`npm run lint`) — PASS (0 errors, 45 pre-existing warnings)
