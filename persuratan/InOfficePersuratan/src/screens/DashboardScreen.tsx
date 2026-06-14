import React, { useState, useCallback } from 'react';
import {
  StyleSheet,
  Text,
  View,
  ScrollView,
  TouchableOpacity,
  RefreshControl,
  SafeAreaView,
  StatusBar,
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { apiClient } from '../api/client';
import { useAuthStore } from '../store/authStore';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';
import { DashboardLoader } from '../components/SkeletonLoader';
import ErrorState from '../components/ErrorState';
import EmptyState from '../components/EmptyState';

interface DashboardStats {
  surat_belum_dibaca: number;
  disposisi_pending: number;
  deadline_hari_ini: number;
  surat_selesai_bulan: number;
}

interface RecentSurat {
  id: number;
  nomor_surat: string;
  pengirim: string;
  perihal: string;
  sifat: string;
  sifat_label: string;
  status: string;
  status_label: string;
  status_color: string;
  tanggal_surat: string;
}

export default function DashboardScreen() {
  const navigation = useNavigation<any>();
  const { user } = useAuthStore();
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [recentSurat, setRecentSurat] = useState<RecentSurat[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  const fetchData = useCallback(async () => {
    try {
      setErrorMsg('');
      const [statsRes, recentRes, unreadRes] = await Promise.all([
        apiClient.get('/dashboard/stats'),
        apiClient.get('/dashboard/surat-terbaru'),
        apiClient.get('/notifikasi/unread-count'),
      ]);

      setStats(statsRes.data.data);
      setRecentSurat(recentRes.data.data);
      setUnreadCount(unreadRes.data.data.count);
    } catch (e) {
      console.error(e);
      setErrorMsg('Gagal memuat data dari server.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      fetchData();
    }, [fetchData])
  );

  const onRefresh = () => {
    setRefreshing(true);
    fetchData();
  };

  const handleSuratPress = (id: number) => {
    navigation.navigate('SuratMasukTab', {
      screen: 'SuratMasukDetail',
      params: { id },
    });
  };

  const getSifatColor = (sifat: string) => {
    switch (sifat) {
      case 'segera': return '#fee2e2';
      case 'penting': return '#fef3c7';
      case 'rahasia': return '#f3e8ff';
      default: return '#f1f5f9';
    }
  };

  const getSifatTextColor = (sifat: string) => {
    switch (sifat) {
      case 'segera': return colors.danger;
      case 'penting': return colors.warningDark;
      case 'rahasia': return '#7c3aed';
      default: return colors.textMuted;
    }
  };

  const getStatusDotColor = (color: string) => {
    switch (color) {
      case 'danger': return colors.danger;
      case 'warning': return colors.warningDark;
      case 'info': return colors.info;
      case 'success': return colors.successLight;
      default: return colors.textMuted;
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
        <DashboardLoader />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
      
      {/* Top Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.welcomeText}>Halo, {user?.nama_lengkap || 'User'}</Text>
          <Text style={styles.roleText}>{user?.roles?.[0]?.nama_role || 'Pegawai'}</Text>
        </View>
        <View style={styles.headerActions}>
          <TouchableOpacity 
            style={styles.headerIconBtn} 
            onPress={() => navigation.navigate('NotifikasiTab')}
            activeOpacity={0.7}
          >
            <Text style={styles.headerIconText}>🔔</Text>
            {unreadCount > 0 && (
              <View style={styles.badge}>
                <Text style={styles.badgeText}>{unreadCount}</Text>
              </View>
            )}
          </TouchableOpacity>

          <TouchableOpacity 
            style={[styles.headerIconBtn, { marginLeft: SPACING.md }]} 
            onPress={() => navigation.navigate('Laporan')}
            activeOpacity={0.7}
          >
            <Text style={styles.headerIconText}>📊</Text>
          </TouchableOpacity>
        </View>
      </View>

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[colors.primary]} />
        }
      >
        {errorMsg ? <ErrorState message={errorMsg} onRetry={fetchData} /> : null}

        {/* Stats Section */}
        <Text style={styles.sectionTitle}>Ringkasan Tugas</Text>
        <View style={styles.statsGrid}>
          <View style={[styles.statCard, { borderLeftColor: colors.danger }]}>
            <View style={[styles.iconWrapper, { backgroundColor: '#fee2e2' }]}>
              <Text style={styles.cardEmoji}>📬</Text>
            </View>
            <Text style={styles.statValue}>{stats?.surat_belum_dibaca ?? 0}</Text>
            <Text style={styles.statLabel}>Belum Dibaca</Text>
          </View>

          <View style={[styles.statCard, { borderLeftColor: colors.warningDark }]}>
            <View style={[styles.iconWrapper, { backgroundColor: '#fef3c7' }]}>
              <Text style={styles.cardEmoji}>📋</Text>
            </View>
            <Text style={styles.statValue}>{stats?.disposisi_pending ?? 0}</Text>
            <Text style={styles.statLabel}>Disposisi Pending</Text>
          </View>

          <View style={[styles.statCard, { borderLeftColor: colors.info }]}>
            <View style={[styles.iconWrapper, { backgroundColor: '#eff6ff' }]}>
              <Text style={styles.cardEmoji}>⏰</Text>
            </View>
            <Text style={styles.statValue}>{stats?.deadline_hari_ini ?? 0}</Text>
            <Text style={styles.statLabel}>Deadline Hari Ini</Text>
          </View>

          <View style={[styles.statCard, { borderLeftColor: colors.successLight }]}>
            <View style={[styles.iconWrapper, { backgroundColor: '#dcfce7' }]}>
              <Text style={styles.cardEmoji}>✅</Text>
            </View>
            <Text style={styles.statValue}>{stats?.surat_selesai_bulan ?? 0}</Text>
            <Text style={styles.statLabel}>Selesai Bulan Ini</Text>
          </View>
        </View>

        {/* Recent Mail Section */}
        <View style={styles.recentHeader}>
          <Text style={styles.sectionTitle}>Surat Masuk Terbaru</Text>
          <TouchableOpacity 
            onPress={() => navigation.navigate('SuratMasukTab', { screen: 'SuratMasukList' })}
            activeOpacity={0.6}
          >
            <Text style={styles.viewAllText}>Lihat Semua</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.listContainer}>
          {recentSurat.length === 0 ? (
            <EmptyState icon="✉️" title="Tidak ada surat masuk terbaru." />
          ) : (
            recentSurat.map((item) => (
              <TouchableOpacity
                key={item.id}
                style={styles.suratCard}
                onPress={() => handleSuratPress(item.id)}
                activeOpacity={0.7}
              >
                <View style={styles.suratLeft}>
                  <View 
                    style={[
                      styles.statusDot, 
                      { backgroundColor: getStatusDotColor(item.status_color) }
                    ]} 
                  />
                  <View style={styles.suratInfo}>
                    <Text style={styles.suratPerihal} numberOfLines={1}>
                      {item.perihal}
                    </Text>
                    <Text style={styles.suratMeta} numberOfLines={1}>
                      Dari: {item.pengirim}
                    </Text>
                    <Text style={styles.suratDate}>
                      Tanggal: {item.tanggal_surat}
                    </Text>
                  </View>
                </View>

                <View 
                  style={[
                    styles.sifatBadge, 
                    { backgroundColor: getSifatColor(item.sifat) }
                  ]}
                >
                  <Text 
                    style={[
                      styles.sifatText, 
                      { color: getSifatTextColor(item.sifat) }
                    ]}
                  >
                    {item.sifat_label}
                  </Text>
                </View>
              </TouchableOpacity>
            ))
          )}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const getStyles = (colors: ThemeColors) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  header: {
    backgroundColor: colors.white,
    paddingHorizontal: SPACING.xl,
    paddingVertical: SPACING.lg,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  welcomeText: {
    fontSize: 18,
    fontWeight: '800',
    color: colors.primary,
  },
  roleText: {
    fontSize: 13,
    color: colors.textMuted,
    fontWeight: '500',
    marginTop: 2,
  },
  scrollContent: {
    padding: SPACING.xl,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: colors.primaryDark,
    marginBottom: SPACING.md,
    letterSpacing: 0.2,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    marginBottom: SPACING.xl,
  },
  statCard: {
    width: '48%',
    backgroundColor: colors.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.md,
    borderLeftWidth: 4,
    ...SHADOWS.sm,
  },
  iconWrapper: {
    width: 36,
    height: 36,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: SPACING.sm,
  },
  cardEmoji: {
    fontSize: 18,
  },
  statValue: {
    fontSize: 26,
    fontWeight: '800',
    color: colors.text,
    lineHeight: 30,
  },
  statLabel: {
    fontSize: 12,
    color: colors.textMuted,
    fontWeight: '600',
    marginTop: 2,
  },
  recentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.md,
  },
  viewAllText: {
    fontSize: 13,
    color: colors.primaryLight,
    fontWeight: '700',
  },
  listContainer: {
    width: '100%',
  },
  suratCard: {
    backgroundColor: colors.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.sm,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    ...SHADOWS.sm,
  },
  suratLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    paddingRight: SPACING.sm,
  },
  statusDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    marginRight: SPACING.md,
  },
  suratInfo: {
    flex: 1,
  },
  suratPerihal: {
    fontSize: 14,
    fontWeight: '700',
    color: colors.text,
  },
  suratMeta: {
    fontSize: 12,
    color: colors.textMuted,
    marginTop: 2,
  },
  suratDate: {
    fontSize: 10,
    color: colors.textMuted,
    marginTop: 2,
  },
  sifatBadge: {
    paddingHorizontal: SPACING.sm,
    paddingVertical: 3,
    borderRadius: 100,
  },
  sifatText: {
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  headerActions: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  headerIconBtn: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: colors.background,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.border,
    position: 'relative',
  },
  headerIconText: {
    fontSize: 18,
  },
  badge: {
    position: 'absolute',
    top: -4,
    right: -4,
    backgroundColor: colors.danger,
    borderRadius: 9,
    minWidth: 18,
    height: 18,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 3,
  },
  badgeText: {
    color: colors.white,
    fontSize: 9,
    fontWeight: '800',
    textAlign: 'center',
  },
});
