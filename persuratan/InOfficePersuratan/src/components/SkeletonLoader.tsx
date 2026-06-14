import React, { useEffect, useRef } from 'react';
import { StyleSheet, View, Animated } from 'react-native';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

/**
 * SkeletonLoader — Shimmer Skeleton Loading Components
 * Uses native Animated API for smooth opacity pulsing (0.3 <-> 0.7).
 * No additional native libraries required.
 */

// ─── Base Shimmer Primitive ──────────────────────────────────────────────────

interface ShimmerBoxProps {
  width?: number | string;
  height?: number;
  borderRadius?: number;
  style?: object;
}

function useShimmer() {
  const opacity = useRef(new Animated.Value(0.3)).current;

  useEffect(() => {
    const animation = Animated.loop(
      Animated.sequence([
        Animated.timing(opacity, {
          toValue: 0.7,
          duration: 800,
          useNativeDriver: true,
        }),
        Animated.timing(opacity, {
          toValue: 0.3,
          duration: 800,
          useNativeDriver: true,
        }),
      ])
    );
    animation.start();
    return () => animation.stop();
  }, [opacity]);

  return opacity;
}

function ShimmerBox({ width, height = 14, borderRadius = 6, style }: ShimmerBoxProps) {
  const opacity = useShimmer();

  return (
    <Animated.View
      style={[
        {
          width: width ?? '100%',
          height,
          borderRadius,
          backgroundColor: COLORS.border,
          opacity,
        },
        style,
      ]}
    />
  );
}

// ─── CardListLoader ──────────────────────────────────────────────────────────
// For Surat Masuk/Keluar, Disposisi, and Notifikasi list screens.

interface CardListLoaderProps {
  itemCount?: number;
}

export function CardListLoader({ itemCount = 6 }: CardListLoaderProps) {
  return (
    <View style={listStyles.container}>
      {Array.from({ length: itemCount }).map((_, i) => (
        <View key={i} style={listStyles.card}>
          {/* Left: status dot + text lines */}
          <View style={listStyles.cardLeft}>
            <ShimmerBox width={10} height={10} borderRadius={5} />
            <View style={listStyles.cardInfo}>
              <ShimmerBox height={14} style={{ width: '70%' }} />
              <ShimmerBox height={11} style={{ width: '90%', marginTop: 6 }} />
              <ShimmerBox height={9} style={{ width: '40%', marginTop: 5 }} />
            </View>
          </View>
          {/* Right: badge */}
          <ShimmerBox width={52} height={20} borderRadius={100} />
        </View>
      ))}
    </View>
  );
}

const listStyles = StyleSheet.create({
  container: {
    flex: 1,
    padding: SPACING.xl,
    backgroundColor: COLORS.background,
  },
  card: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.sm,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    ...SHADOWS.sm,
  },
  cardLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    paddingRight: SPACING.sm,
  },
  cardInfo: {
    flex: 1,
    marginLeft: SPACING.md,
  },
});

// ─── DashboardLoader ─────────────────────────────────────────────────────────
// Skeleton for Dashboard: stats grid + recent surat list.

export function DashboardLoader() {
  return (
    <View style={dashStyles.container}>
      {/* Section title skeleton */}
      <ShimmerBox height={15} width={140} borderRadius={6} style={{ marginBottom: SPACING.md }} />

      {/* Stats grid: 4 cards */}
      <View style={dashStyles.statsGrid}>
        {Array.from({ length: 4 }).map((_, i) => (
          <View key={i} style={dashStyles.statCard}>
            <ShimmerBox width={36} height={36} borderRadius={8} style={{ marginBottom: SPACING.sm }} />
            <ShimmerBox height={24} width={50} style={{ marginBottom: 4 }} />
            <ShimmerBox height={11} width={80} />
          </View>
        ))}
      </View>

      {/* Recent section title */}
      <View style={dashStyles.recentHeader}>
        <ShimmerBox height={15} width={160} borderRadius={6} />
        <ShimmerBox height={13} width={70} borderRadius={6} />
      </View>

      {/* Recent list: 5 items */}
      {Array.from({ length: 5 }).map((_, i) => (
        <View key={i} style={dashStyles.recentCard}>
          <View style={dashStyles.recentLeft}>
            <ShimmerBox width={10} height={10} borderRadius={5} />
            <View style={dashStyles.recentInfo}>
              <ShimmerBox height={14} style={{ width: '65%' }} />
              <ShimmerBox height={11} style={{ width: '85%', marginTop: 5 }} />
              <ShimmerBox height={9} style={{ width: '35%', marginTop: 4 }} />
            </View>
          </View>
          <ShimmerBox width={52} height={20} borderRadius={100} />
        </View>
      ))}
    </View>
  );
}

const dashStyles = StyleSheet.create({
  container: {
    flex: 1,
    padding: SPACING.xl,
    backgroundColor: COLORS.background,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    marginBottom: SPACING.xl,
  },
  statCard: {
    width: '48%',
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.md,
    ...SHADOWS.sm,
  },
  recentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.md,
  },
  recentCard: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.sm,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    ...SHADOWS.sm,
  },
  recentLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    paddingRight: SPACING.sm,
  },
  recentInfo: {
    flex: 1,
    marginLeft: SPACING.md,
  },
});

// ─── ChartLoader ─────────────────────────────────────────────────────────────
// Skeleton for Laporan screen: stats cards + chart area.

export function ChartLoader() {
  return (
    <View style={chartStyles.container}>
      {/* Stats grid: 4 cards */}
      <View style={chartStyles.statsGrid}>
        {Array.from({ length: 4 }).map((_, i) => (
          <View key={i} style={chartStyles.statCard}>
            <ShimmerBox width={28} height={28} borderRadius={14} style={{ marginBottom: 6 }} />
            <ShimmerBox height={18} width={40} style={{ marginBottom: 4 }} />
            <ShimmerBox height={10} width={70} />
          </View>
        ))}
      </View>

      {/* Chart area skeleton */}
      <View style={chartStyles.chartCard}>
        <ShimmerBox height={14} width={200} borderRadius={6} style={{ marginBottom: SPACING.lg }} />
        <View style={chartStyles.barsRow}>
          {Array.from({ length: 6 }).map((_, i) => (
            <View key={i} style={chartStyles.barCol}>
              <ShimmerBox width={12} height={40 + i * 15} borderRadius={3} />
              <ShimmerBox width={12} height={30 + i * 10} borderRadius={3} />
            </View>
          ))}
        </View>
        <View style={chartStyles.legendRow}>
          <ShimmerBox width={80} height={12} borderRadius={6} />
          <ShimmerBox width={80} height={12} borderRadius={6} />
        </View>
      </View>

      {/* Breakdown section skeleton */}
      <View style={chartStyles.breakdownCard}>
        <ShimmerBox height={13} width={180} borderRadius={6} style={{ marginBottom: SPACING.md }} />
        {Array.from({ length: 4 }).map((_, i) => (
          <View key={i} style={chartStyles.breakdownItem}>
            <View style={chartStyles.breakdownHeader}>
              <ShimmerBox width={80} height={12} borderRadius={4} />
              <ShimmerBox width={50} height={12} borderRadius={4} />
            </View>
            <ShimmerBox height={8} borderRadius={4} style={{ width: `${70 - i * 10}%` }} />
          </View>
        ))}
      </View>
    </View>
  );
}

const chartStyles = StyleSheet.create({
  container: {
    flex: 1,
    padding: SPACING.xl,
    backgroundColor: COLORS.background,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    marginBottom: SPACING.md,
  },
  statCard: {
    width: '48%',
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.md,
    alignItems: 'center',
    ...SHADOWS.sm,
  },
  chartCard: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
    marginBottom: SPACING.md,
  },
  barsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    height: 120,
    marginBottom: SPACING.md,
  },
  barCol: {
    flexDirection: 'row',
    gap: 4,
    alignItems: 'flex-end',
  },
  legendRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: SPACING.xl,
  },
  breakdownCard: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
  },
  breakdownItem: {
    marginBottom: SPACING.md,
  },
  breakdownHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
});
