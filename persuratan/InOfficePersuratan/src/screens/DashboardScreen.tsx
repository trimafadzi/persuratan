import React, { useState, useCallback } from 'react';
import {
  StyleSheet,
  Text,
  View,
  ScrollView,
  TouchableOpacity,
  RefreshControl,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { apiClient } from '../api/client';
import { useAuthStore } from '../store/authStore';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

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
    // Navigasi silang ke stack SuratMasuk -> SuratMasukDetail
    navigation.navigate('SuratMasukTab', {
      screen: 'SuratMasukDetail',
      params: { id },
    });
  };

  const getSifatColor = (sifat: string) => {
    switch (sifat) {
      case 'segera': return '#fee2e2'; // Light red
      case 'penting': return '#fef3c7'; // Light yellow
      case 'rahasia': return '#f3e8ff'; // Light purple
      default: return '#f1f5f9'; // Light gray
    }
  };

  const getSifatTextColor = (sifat: string) => {
    switch (sifat) {
      case 'segera': return COLORS.danger;
      case 'penting': return COLORS.warningDark;
      case 'rahasia': return '#7c3aed';
      default: return COLORS.textMuted;
    }
  };

  const getStatusDotColor = (color: string) => {
    switch (color) {
      case 'danger': return COLORS.danger;
      case 'warning': return COLORS.warningDark;
      case 'info': return COLORS.info;
      case 'success': return COLORS.successLight;
      default: return COLORS.textMuted;
    }
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />
      
      {/* Top Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.welcomeText}>Halo, {user?.nama_lengkap || 'User'}</Text>
          <Text style={styles.roleText}>{user?.roles?.[0]?.nama_role || 'Pegawai'}</Text>
        </View>
        <View style={styles.headerActions}>
          <TouchableOpacity 
            style={styles.headerIconBtn} 
            onPress={() => navigation.navigate('Notifikasi')}
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
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[COLORS.primary]} />
        }
      >
        {errorMsg ? (
          <View style={styles.errorContainer}>
            <Text style={styles.errorText}>{errorMsg}</Text>
            <TouchableOpacity style={styles.retryButton} onPress={fetchData}>
              <Text style={styles.retryButtonText}>Coba Lagi</Text>
            </TouchableOpacity>
          </View>
        ) : null}

        {/* Stats Section */}
        <Text style={styles.sectionTitle}>Ringkasan Tugas</Text>
        <View style={styles.statsGrid}>
          {/* Card 1: Belum Dibaca */}
          <View style={[styles.statCard, { borderLeftColor: COLORS.danger }]}>
            <View style={[styles.iconWrapper, { backgroundColor: '#fee2e2' }]}>
              <Text style={styles.cardEmoji}>📬</Text>
            </View>
            <Text style={styles.statValue}>{stats?.surat_belum_dibaca ?? 0}</Text>
            <Text style={styles.statLabel}>Belum Dibaca</Text>
          </View>

          {/* Card 2: Disposisi Pending */}
          <View style={[styles.statCard, { borderLeftColor: COLORS.warningDark }]}>
            <View style={[styles.iconWrapper, { backgroundColor: '#fef3c7' }]}>
              <Text style={styles.cardEmoji}>📋</Text>
            </View>
            <Text style={styles.statValue}>{stats?.disposisi_pending ?? 0}</Text>
            <Text style={styles.statLabel}>Disposisi Pending</Text>
          </View>

          {/* Card 3: Deadline Hari Ini */}
          <View style={[styles.statCard, { borderLeftColor: COLORS.info }]}>
            <View style={[styles.iconWrapper, { backgroundColor: '#eff6ff' }]}>
              <Text style={styles.cardEmoji}>⏰</Text>
            </View>
            <Text style={styles.statValue}>{stats?.deadline_hari_ini ?? 0}</Text>
            <Text style={styles.statLabel}>Deadline Hari Ini</Text>
          </View>

          {/* Card 4: Selesai Bulan Ini */}
          <View style={[styles.statCard, { borderLeftColor: COLORS.successLight }]}>
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
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>✉️</Text>
              <Text style={styles.emptyText}>Tidak ada surat masuk terbaru.</Text>
            </View>
          ) : (
            recentSurat.map((item) => (
              <TouchableOpacity
                key={item.id}
                style={styles.suratCard}
                onPress={() => handleSuratPress(item.id)}
                activeOpacity={0.7}
              >
                <View style={styles.suratLeft}>
                  {/* Status Indicator Dot */}
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

                {/* Sifat Badge */}
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

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  loadingContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    backgroundColor: COLORS.white,
    paddingHorizontal: SPACING.xl,
    paddingVertical: SPACING.lg,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  welcomeText: {
    fontSize: 18,
    fontWeight: '800',
    color: COLORS.primary,
  },
  roleText: {
    fontSize: 13,
    color: COLORS.textMuted,
    fontWeight: '500',
    marginTop: 2,
  },
  hospitalBadge: {
    backgroundColor: COLORS.accent,
    paddingHorizontal: SPACING.sm,
    paddingVertical: SPACING.xs,
    borderRadius: SIZES.radiusSm,
  },
  hospitalText: {
    color: COLORS.white,
    fontWeight: '800',
    fontSize: 10,
    letterSpacing: 0.5,
  },
  scrollContent: {
    padding: SPACING.xl,
  },
  errorContainer: {
    backgroundColor: '#fff0f0',
    borderWidth: 1,
    borderColor: '#fca5a5',
    borderRadius: SIZES.radiusMd,
    padding: SPACING.lg,
    alignItems: 'center',
    marginBottom: SPACING.lg,
  },
  errorText: {
    color: '#991b1b',
    fontWeight: '600',
    fontSize: 14,
    marginBottom: SPACING.md,
  },
  retryButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.sm,
    borderRadius: SIZES.radiusSm,
  },
  retryButtonText: {
    color: COLORS.white,
    fontWeight: '700',
    fontSize: 12,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: COLORS.primaryDark,
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
    backgroundColor: COLORS.white,
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
    color: COLORS.text,
    lineHeight: 30,
  },
  statLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
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
    color: COLORS.primaryLight,
    fontWeight: '700',
  },
  listContainer: {
    width: '100%',
  },
  emptyContainer: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.xxl,
    alignItems: 'center',
    ...SHADOWS.sm,
  },
  emptyIcon: {
    fontSize: 36,
    marginBottom: SPACING.sm,
  },
  emptyText: {
    color: COLORS.textMuted,
    fontSize: 14,
    fontWeight: '500',
  },
  suratCard: {
    backgroundColor: COLORS.white,
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
    color: COLORS.text,
  },
  suratMeta: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  suratDate: {
    fontSize: 10,
    color: COLORS.textMuted,
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
    backgroundColor: COLORS.background,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
    position: 'relative',
  },
  headerIconText: {
    fontSize: 18,
  },
  badge: {
    position: 'absolute',
    top: -4,
    right: -4,
    backgroundColor: COLORS.danger,
    borderRadius: 9,
    minWidth: 18,
    height: 18,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 3,
  },
  badgeText: {
    color: COLORS.white,
    fontSize: 9,
    fontWeight: '800',
    textAlign: 'center',
  },
});
