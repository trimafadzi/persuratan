import React, { useState, useEffect, useCallback } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
  ScrollView,
  RefreshControl,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { apiClient } from '../api/client';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

interface StatsData {
  total_masuk: number;
  total_keluar: number;
  total_disposisi: number;
  selesai_bulan: number;
}

interface VolumeItem {
  bulan: string;
  masuk: number;
  keluar: number;
}

interface EmployeeItem {
  user_id: number;
  nama: string;
  jabatan: string;
  unit_kerja: string | null;
  volume: number;
  total_disp: number;
  selesai: number;
  ketuntasan: number;
  skor: number;
}

export default function LaporanScreen() {
  const navigation = useNavigation();

  const [activeTab, setActiveTab] = useState<'stats' | 'kinerja'>('stats');
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  // Stats data
  const [stats, setStats] = useState<StatsData | null>(null);
  const [volumeList, setVolumeList] = useState<VolumeItem[]>([]);
  const [statusBreakdown, setStatusBreakdown] = useState<Record<string, number>>({});

  // Performance data
  const [employees, setEmployees] = useState<EmployeeItem[]>([]);

  const fetchLaporanData = useCallback(async () => {
    try {
      setLoading(true);
      const [statsRes, kinerjaRes] = await Promise.all([
        apiClient.get('/laporan/stats'),
        apiClient.get('/laporan/kinerja'),
      ]);

      const dataStats = statsRes.data.data;
      setStats(dataStats.stats);
      setVolumeList(dataStats.volume_per_bulan || []);
      setStatusBreakdown(dataStats.status_breakdown || {});

      setEmployees(kinerjaRes.data.data || []);
    } catch (e) {
      console.error('[LaporanScreen] Gagal memuat data:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchLaporanData();
  }, [fetchLaporanData]);

  const handleRefresh = () => {
    setRefreshing(true);
    fetchLaporanData();
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'belum_dibaca': return COLORS.danger;
      case 'dibaca': return COLORS.warningDark;
      case 'didisposisi': return COLORS.primaryLight;
      case 'selesai': return COLORS.successLight;
      default: return COLORS.textMuted;
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'belum_dibaca': return 'Belum Dibaca';
      case 'dibaca': return 'Dibaca';
      case 'didisposisi': return 'Didisposisi';
      case 'selesai': return 'Selesai';
      default: return status;
    }
  };

  const getScoreColor = (skor: number) => {
    if (skor >= 80) return COLORS.successLight;
    if (skor >= 50) return COLORS.warningDark;
    return COLORS.danger;
  };

  const getRankBadge = (rank: number) => {
    switch (rank) {
      case 1: return '🥇';
      case 2: return '🥈';
      case 3: return '🥉';
      default: return `#${rank}`;
    }
  };

  // Pure CSS Chart Rendering
  const renderChart = () => {
    if (volumeList.length === 0) return null;

    // Find the maximum value to scale heights
    const maxVal = Math.max(
      ...volumeList.map((item) => Math.max(item.masuk, item.keluar, 1))
    );

    const chartHeight = 120; // Maximum bar height in dp

    return (
      <View style={styles.chartContainer}>
        <Text style={styles.chartTitle}>Volume Surat (6 Bulan Terakhir)</Text>
        
        {/* Chart Bars */}
        <View style={styles.chartBarsContainer}>
          {volumeList.map((item, index) => {
            const masukHeight = (item.masuk / maxVal) * chartHeight;
            const keluarHeight = (item.keluar / maxVal) * chartHeight;

            return (
              <View key={index} style={styles.chartCol}>
                <View style={styles.chartBarGroup}>
                  {/* Masuk Bar */}
                  <View style={styles.chartBarWrapper}>
                    <View style={[styles.chartBar, styles.barMasuk, { height: Math.max(masukHeight, 4) }]} />
                    <Text style={styles.barValueText}>{item.masuk}</Text>
                  </View>
                  
                  {/* Keluar Bar */}
                  <View style={styles.chartBarWrapper}>
                    <View style={[styles.chartBar, styles.barKeluar, { height: Math.max(keluarHeight, 4) }]} />
                    <Text style={styles.barValueText}>{item.keluar}</Text>
                  </View>
                </View>
                <Text style={styles.chartLabelText}>{item.bulan}</Text>
              </View>
            );
          })}
        </View>

        {/* Legend */}
        <View style={styles.chartLegend}>
          <View style={styles.legendItem}>
            <View style={[styles.legendDot, styles.barMasuk]} />
            <Text style={styles.legendText}>Surat Masuk</Text>
          </View>
          <View style={styles.legendItem}>
            <View style={[styles.legendDot, styles.barKeluar]} />
            <Text style={styles.legendText}>Surat Keluar</Text>
          </View>
        </View>
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.headerBackBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Laporan & Statistik</Text>
        <View style={{ width: 40 }} />
      </View>

      {/* Segmented Control */}
      <View style={styles.tabBar}>
        <TouchableOpacity
          style={[styles.tabBtn, activeTab === 'stats' && styles.tabBtnActive]}
          onPress={() => setActiveTab('stats')}
        >
          <Text style={[styles.tabBtnText, activeTab === 'stats' && styles.tabBtnTextActive]}>
            📊 Statistik Surat
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tabBtn, activeTab === 'kinerja' && styles.tabBtnActive]}
          onPress={() => setActiveTab('kinerja')}
        >
          <Text style={[styles.tabBtnText, activeTab === 'kinerja' && styles.tabBtnTextActive]}>
            🏆 Kinerja Staf
          </Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
      ) : (
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} colors={[COLORS.primary]} />
          }
        >
          {activeTab === 'stats' ? (
            <>
              {/* Aggregated Stats Grid */}
              <View style={styles.statsGrid}>
                <View style={styles.statCard}>
                  <Text style={styles.statEmoji}>📬</Text>
                  <Text style={styles.statVal}>{stats?.total_masuk ?? 0}</Text>
                  <Text style={styles.statLabel}>Total Masuk</Text>
                </View>

                <View style={styles.statCard}>
                  <Text style={styles.statEmoji}>📤</Text>
                  <Text style={styles.statVal}>{stats?.total_keluar ?? 0}</Text>
                  <Text style={styles.statLabel}>Total Keluar</Text>
                </View>

                <View style={styles.statCard}>
                  <Text style={styles.statEmoji}>📋</Text>
                  <Text style={styles.statVal}>{stats?.total_disposisi ?? 0}</Text>
                  <Text style={styles.statLabel}>Total Disposisi</Text>
                </View>

                <View style={styles.statCard}>
                  <Text style={styles.statEmoji}>✅</Text>
                  <Text style={styles.statVal}>{stats?.selesai_bulan ?? 0}</Text>
                  <Text style={styles.statLabel}>Selesai Bulan Ini</Text>
                </View>
              </View>

              {/* Monthly Volume Chart */}
              {renderChart()}

              {/* Status Breakdown Section */}
              <View style={styles.card}>
                <Text style={styles.cardSectionTitle}>Breakdown Status Surat Masuk</Text>
                <View style={styles.breakdownList}>
                  {Object.entries(statusBreakdown).map(([statusKey, count]) => {
                    const total = stats?.total_masuk || 1;
                    const percent = Math.round((count / total) * 100);
                    const color = getStatusColor(statusKey);

                    return (
                      <View key={statusKey} style={styles.breakdownItem}>
                        <View style={styles.breakdownHeader}>
                          <Text style={styles.statusLabelText}>{getStatusLabel(statusKey)}</Text>
                          <Text style={styles.statusCountText}>
                            {count} ({percent}%)
                          </Text>
                        </View>
                        <View style={styles.progressBarBg}>
                          <View style={[styles.progressBarFill, { width: `${percent}%`, backgroundColor: color }]} />
                        </View>
                      </View>
                    );
                  })}
                </View>
              </View>
            </>
          ) : (
            <>
              {/* Employee Performance Leaderboard */}
              <Text style={styles.leaderboardTitle}>Papan Peringkat Kinerja Staf</Text>
              
              {employees.length === 0 ? (
                <View style={styles.emptyCard}>
                  <Text style={styles.emptyText}>Tidak ada data kinerja pegawai.</Text>
                </View>
              ) : (
                employees.map((emp, index) => {
                  const rank = index + 1;
                  const scoreColor = getScoreColor(emp.skor);

                  return (
                    <View key={emp.user_id} style={styles.employeeCard}>
                      {/* Rank Indicator */}
                      <View style={styles.rankContainer}>
                        <Text style={[styles.rankText, rank <= 3 && styles.topRankText]}>
                          {getRankBadge(rank)}
                        </Text>
                      </View>

                      {/* Employee Info */}
                      <View style={styles.empInfo}>
                        <Text style={styles.empName}>{emp.nama}</Text>
                        <Text style={styles.empJob} numberOfLines={1}>
                          {emp.jabatan} {emp.unit_kerja ? `| ${emp.unit_kerja}` : ''}
                        </Text>
                        
                        <View style={styles.empStatsRow}>
                          <Text style={styles.empStatCol}>
                            Disposisi: <Text style={styles.boldText}>{emp.total_disp}</Text>
                          </Text>
                          <Text style={styles.empStatCol}>
                            Selesai: <Text style={styles.boldText}>{emp.selesai}</Text>
                          </Text>
                          <Text style={styles.empStatCol}>
                            Ketuntasan: <Text style={styles.boldText}>{emp.ketuntasan}%</Text>
                          </Text>
                        </View>
                      </View>

                      {/* Overall Performance Score Badge */}
                      <View style={[styles.scoreBadge, { backgroundColor: scoreColor }]}>
                        <Text style={styles.scoreText}>{emp.skor}</Text>
                        <Text style={styles.scoreLabel}>Skor</Text>
                      </View>
                    </View>
                  );
                })
              )}
            </>
          )}
        </ScrollView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    backgroundColor: COLORS.white,
    paddingVertical: SPACING.md,
    paddingHorizontal: SPACING.xl,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  headerBackBtn: {
    width: 40,
    height: 40,
    justifyContent: 'center',
  },
  backArrow: {
    fontSize: 24,
    color: COLORS.primary,
    fontWeight: '700',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.primary,
    flex: 1,
    textAlign: 'center',
  },
  tabBar: {
    flexDirection: 'row',
    backgroundColor: COLORS.white,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  tabBtn: {
    flex: 1,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  tabBtnActive: {
    borderBottomColor: COLORS.primary,
  },
  tabBtnText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  tabBtnTextActive: {
    color: COLORS.primary,
    fontWeight: '700',
  },
  scrollContent: {
    padding: SPACING.xl,
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
  statEmoji: {
    fontSize: 24,
    marginBottom: 4,
  },
  statVal: {
    fontSize: 20,
    fontWeight: '800',
    color: COLORS.text,
  },
  statLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    fontWeight: '600',
    marginTop: 2,
  },
  card: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
    marginBottom: SPACING.xl,
  },
  cardSectionTitle: {
    fontSize: 13,
    fontWeight: '800',
    color: COLORS.primary,
    marginBottom: SPACING.md,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  chartContainer: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
    marginBottom: SPACING.md,
  },
  chartTitle: {
    fontSize: 13,
    fontWeight: '800',
    color: COLORS.primary,
    marginBottom: SPACING.lg,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  chartBarsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    height: 160,
    paddingBottom: SPACING.xs,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  chartCol: {
    alignItems: 'center',
    flex: 1,
  },
  chartBarGroup: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: 4,
  },
  chartBarWrapper: {
    alignItems: 'center',
  },
  chartBar: {
    width: 12,
    borderTopLeftRadius: 3,
    borderTopRightRadius: 3,
  },
  barMasuk: {
    backgroundColor: COLORS.primaryLight,
  },
  barKeluar: {
    backgroundColor: COLORS.accent,
  },
  barValueText: {
    fontSize: 9,
    fontWeight: '700',
    color: COLORS.textMuted,
    marginTop: 2,
  },
  chartLabelText: {
    fontSize: 9,
    fontWeight: '600',
    color: COLORS.textMuted,
    marginTop: 6,
    textAlign: 'center',
  },
  chartLegend: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: SPACING.xl,
    marginTop: SPACING.md,
  },
  legendItem: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  legendDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    marginRight: 6,
  },
  legendText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  breakdownList: {
    gap: SPACING.md,
  },
  breakdownItem: {
    marginBottom: SPACING.xs,
  },
  breakdownHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 4,
  },
  statusLabelText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
  },
  statusCountText: {
    fontSize: 12,
    color: COLORS.textMuted,
    fontWeight: '600',
  },
  progressBarBg: {
    height: 8,
    backgroundColor: COLORS.background,
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressBarFill: {
    height: '100%',
    borderRadius: 4,
  },
  leaderboardTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: COLORS.primaryDark,
    marginBottom: SPACING.md,
  },
  employeeCard: {
    flexDirection: 'row',
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.sm,
    alignItems: 'center',
    ...SHADOWS.sm,
  },
  rankContainer: {
    width: 36,
    justifyContent: 'center',
    alignItems: 'center',
  },
  rankText: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.textMuted,
  },
  topRankText: {
    fontSize: 20,
  },
  empInfo: {
    flex: 1,
    paddingLeft: SPACING.sm,
    paddingRight: SPACING.md,
  },
  empName: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
  },
  empJob: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  empStatsRow: {
    flexDirection: 'row',
    gap: SPACING.sm,
    marginTop: SPACING.xs,
  },
  empStatCol: {
    fontSize: 10,
    color: COLORS.textMuted,
  },
  boldText: {
    fontWeight: '700',
    color: COLORS.text,
  },
  scoreBadge: {
    width: 46,
    height: 46,
    borderRadius: 23,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scoreText: {
    fontSize: 16,
    fontWeight: '800',
    color: COLORS.white,
  },
  scoreLabel: {
    fontSize: 8,
    color: COLORS.white,
    fontWeight: '600',
    textTransform: 'uppercase',
  },
  emptyCard: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.xl,
    alignItems: 'center',
  },
  emptyText: {
    color: COLORS.textMuted,
    fontSize: 13,
    fontStyle: 'italic',
  },
});
