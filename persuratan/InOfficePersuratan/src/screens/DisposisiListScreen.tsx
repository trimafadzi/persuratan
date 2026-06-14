import React, { useState, useEffect, useCallback } from 'react';
import {
  StyleSheet,
  Text,
  View,
  FlatList,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
  ScrollView,
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { apiClient } from '../api/client';
import { useAuthStore } from '../store/authStore';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

interface DisposisiItem {
  id: number;
  isi_disposisi: string;
  status: string;
  status_label: string;
  status_color: string;
  tanggal_deadline: string | null;
  is_overdue: boolean;
  parent_disposisi_id: number | null;
  created_at: string;
  surat_masuk: {
    id: number;
    nomor_surat: string;
    perihal: string;
    pengirim: string;
    sifat: string;
  } | null;
  pemberi: {
    id: number;
    nama_lengkap: string;
    jabatan: string;
    initials: string;
  } | null;
  penerima: Array<{
    id: number;
    nama_lengkap: string;
    jabatan: string;
    initials: string;
    is_read: boolean;
    read_at: string | null;
  }>;
}

const STATUS_FILTERS = [
  { label: 'Semua Status', value: '' },
  { label: 'Menunggu', value: 'pending' },
  { label: 'Diteruskan', value: 'diteruskan' },
  { label: 'Selesai', value: 'selesai' },
  { label: 'Dibatalkan', value: 'dibatalkan' },
];

export default function DisposisiListScreen() {
  const navigation = useNavigation<any>();
  const currentUser = useAuthStore((state) => state.user);

  const [tab, setTab] = useState<'masuk' | 'keluar'>('masuk');
  const [disposisiList, setDisposisiList] = useState<DisposisiItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  // Search & Filter
  const [search, setSearch] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('');

  // Pagination
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  const fetchDisposisi = useCallback(async (pageNum = 1, isRefresh = false) => {
    try {
      if (pageNum === 1 && !isRefresh) {
        setLoading(true);
      } else if (pageNum > 1) {
        setLoadingMore(true);
      }

      const response = await apiClient.get('/disposisi', {
        params: {
          tab,
          search,
          status: selectedStatus,
          page: pageNum,
          per_page: 15,
        },
      });

      const fetchedData = response.data.data;
      const paginationMeta = response.data.meta;

      if (pageNum === 1) {
        setDisposisiList(fetchedData);
      } else {
        setDisposisiList((prev) => [...prev, ...fetchedData]);
      }

      setPage(paginationMeta.current_page);
      setLastPage(paginationMeta.last_page);
    } catch (e) {
      console.error('[DisposisiList] Gagal memuat disposisi:', e);
    } finally {
      setLoading(false);
      setLoadingMore(false);
      setRefreshing(false);
    }
  }, [tab, search, selectedStatus]);

  useFocusEffect(
    useCallback(() => {
      fetchDisposisi(1);
    }, [fetchDisposisi])
  );

  const handleSearchSubmit = () => {
    fetchDisposisi(1);
  };

  const handleRefresh = () => {
    setRefreshing(true);
    fetchDisposisi(1, true);
  };

  const handleLoadMore = () => {
    if (page < lastPage && !loadingMore && !loading) {
      fetchDisposisi(page + 1);
    }
  };

  const isUnreadForMe = (item: DisposisiItem) => {
    if (tab === 'keluar') return false;
    const me = item.penerima?.find((p) => p.id === currentUser?.id);
    return me ? !me.is_read : false;
  };

  const getStatusBgColor = (status: string) => {
    switch (status) {
      case 'pending': return '#fef3c7'; // yellow/warning
      case 'diteruskan': return '#eff6ff'; // blue/info
      case 'selesai': return '#dcfce7'; // green/success
      case 'dibatalkan': return '#fee2e2'; // red/danger
      default: return '#f1f5f9';
    }
  };

  const getStatusTextColor = (status: string) => {
    switch (status) {
      case 'pending': return COLORS.warningDark;
      case 'diteruskan': return COLORS.primaryLight;
      case 'selesai': return COLORS.successLight;
      case 'dibatalkan': return COLORS.danger;
      default: return COLORS.textMuted;
    }
  };

  const renderItem = ({ item }: { item: DisposisiItem }) => {
    const unread = isUnreadForMe(item);
    const deadlineFormatted = item.tanggal_deadline
      ? item.tanggal_deadline
      : 'Tidak ada deadline';

    return (
      <TouchableOpacity
        style={[styles.card, unread && styles.cardUnread]}
        onPress={() => navigation.navigate('DisposisiDetail', { id: item.id })}
        activeOpacity={0.7}
      >
        <View style={styles.cardHeader}>
          <View style={styles.cardHeaderLeft}>
            {unread && <View style={styles.unreadDot} />}
            <Text style={styles.letterPerihal} numberOfLines={1}>
              {item.surat_masuk?.perihal || 'Surat Tidak Ditemukan'}
            </Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: getStatusBgColor(item.status) }]}>
            <Text style={[styles.statusText, { color: getStatusTextColor(item.status) }]}>
              {item.status_label}
            </Text>
          </View>
        </View>

        <Text style={styles.instruction} numberOfLines={2}>
          "{item.isi_disposisi}"
        </Text>

        <View style={styles.divider} />

        <View style={styles.cardFooter}>
          <View style={styles.actorInfo}>
            <Text style={styles.actorLabel}>{tab === 'masuk' ? 'Dari:' : 'Kepada:'}</Text>
            <Text style={styles.actorName} numberOfLines={1}>
              {tab === 'masuk'
                ? item.pemberi?.nama_lengkap || 'System'
                : item.penerima?.map((p) => p.nama_lengkap).join(', ') || '-'}
            </Text>
          </View>
          <View style={styles.deadlineInfo}>
            <Text style={styles.deadlineLabel}>Deadline:</Text>
            <Text style={[styles.deadlineVal, item.is_overdue && styles.deadlineOverdue]}>
              {deadlineFormatted}
            </Text>
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />

      {/* Header Tabs */}
      <View style={styles.tabHeader}>
        <TouchableOpacity
          style={[styles.tabButton, tab === 'masuk' && styles.tabButtonActive]}
          onPress={() => {
            setTab('masuk');
            setSelectedStatus('');
          }}
        >
          <Text style={[styles.tabButtonText, tab === 'masuk' && styles.tabButtonTextActive]}>
            📥 Disposisi Masuk
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tabButton, tab === 'keluar' && styles.tabButtonActive]}
          onPress={() => {
            setTab('keluar');
            setSelectedStatus('');
          }}
        >
          <Text style={[styles.tabButtonText, tab === 'keluar' && styles.tabButtonTextActive]}>
            📤 Disposisi Keluar
          </Text>
        </TouchableOpacity>
      </View>

      {/* Search Bar */}
      <View style={styles.searchSection}>
        <View style={styles.searchContainer}>
          <TextInput
            style={styles.searchInput}
            placeholder="Cari isi instruksi disposisi..."
            placeholderTextColor={COLORS.textMuted}
            value={search}
            onChangeText={setSearch}
            onSubmitEditing={handleSearchSubmit}
            returnKeyType="search"
          />
          {search ? (
            <TouchableOpacity
              onPress={() => {
                setSearch('');
                setTimeout(() => fetchDisposisi(1), 50);
              }}
              style={styles.clearSearch}
            >
              <Text style={styles.clearSearchText}>×</Text>
            </TouchableOpacity>
          ) : null}
        </View>
        <TouchableOpacity style={styles.searchButton} onPress={handleSearchSubmit}>
          <Text style={styles.searchButtonText}>Cari</Text>
        </TouchableOpacity>
      </View>

      {/* Filter Status */}
      <View style={styles.filterSection}>
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={styles.filterRow}
          contentContainerStyle={styles.filterRowContent}
        >
          {STATUS_FILTERS.map((f) => (
            <TouchableOpacity
              key={f.value}
              style={[
                styles.filterTag,
                selectedStatus === f.value && styles.filterTagActive,
                selectedStatus === f.value && f.value === 'pending' && { backgroundColor: '#fef3c7', borderColor: COLORS.warningDark },
                selectedStatus === f.value && f.value === 'diteruskan' && { backgroundColor: '#eff6ff', borderColor: COLORS.primaryLight },
                selectedStatus === f.value && f.value === 'selesai' && { backgroundColor: '#dcfce7', borderColor: COLORS.successLight },
                selectedStatus === f.value && f.value === 'dibatalkan' && { backgroundColor: '#fee2e2', borderColor: COLORS.danger },
              ]}
              onPress={() => setSelectedStatus(f.value)}
            >
              <Text
                style={[
                  styles.filterTagText,
                  selectedStatus === f.value && styles.filterTagTextActive,
                  selectedStatus === f.value && f.value === 'pending' && { color: COLORS.warningDark },
                  selectedStatus === f.value && f.value === 'diteruskan' && { color: COLORS.primaryLight },
                  selectedStatus === f.value && f.value === 'selesai' && { color: COLORS.successLight },
                  selectedStatus === f.value && f.value === 'dibatalkan' && { color: COLORS.danger },
                ]}
              >
                {f.label}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      <FilterEffect trigger={[tab, selectedStatus]} effect={() => fetchDisposisi(1)} />

      {/* Main List */}
      {loading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
      ) : (
        <FlatList
          data={disposisiList}
          renderItem={renderItem}
          keyExtractor={(item) => item.id.toString()}
          contentContainerStyle={styles.listContent}
          refreshing={refreshing}
          onRefresh={handleRefresh}
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.3}
          ListFooterComponent={
            loadingMore ? (
              <ActivityIndicator
                size="small"
                color={COLORS.primary}
                style={styles.footerLoader}
              />
            ) : null
          }
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>📋</Text>
              <Text style={styles.emptyText}>Tidak ada disposisi yang cocok.</Text>
            </View>
          }
        />
      )}

      {/* Floating Action Button (FAB) only on Disposisi Keluar (or optional pimpinan privilege) */}
      <TouchableOpacity
        style={styles.fab}
        onPress={() => navigation.navigate('DisposisiCreate', {})}
        activeOpacity={0.8}
      >
        <Text style={styles.fabText}>+</Text>
      </TouchableOpacity>
    </SafeAreaView>
  );
}

// Custom Helper component to trigger filter reload
function FilterEffect({ trigger, effect }: { trigger: any[]; effect: () => void }) {
  useEffect(() => {
    effect();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, trigger);
  return null;
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
  tabHeader: {
    flexDirection: 'row',
    backgroundColor: COLORS.white,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  tabButton: {
    flex: 1,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  tabButtonActive: {
    borderBottomColor: COLORS.primary,
  },
  tabButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  tabButtonTextActive: {
    color: COLORS.primary,
    fontWeight: '700',
  },
  searchSection: {
    flexDirection: 'row',
    paddingHorizontal: SPACING.xl,
    paddingTop: SPACING.md,
    paddingBottom: SPACING.xs,
    backgroundColor: COLORS.white,
    alignItems: 'center',
    gap: SPACING.sm,
  },
  searchContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: SIZES.radiusSm,
    paddingRight: SPACING.xs,
  },
  searchInput: {
    flex: 1,
    paddingHorizontal: SPACING.md,
    paddingVertical: 8,
    fontSize: 13,
    color: COLORS.text,
  },
  clearSearch: {
    padding: SPACING.xs,
  },
  clearSearchText: {
    fontSize: 18,
    color: COLORS.textMuted,
    fontWeight: '700',
  },
  searchButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: SPACING.md,
    paddingVertical: 9,
    borderRadius: SIZES.radiusSm,
  },
  searchButtonText: {
    color: COLORS.white,
    fontWeight: '700',
    fontSize: 12,
  },
  filterSection: {
    backgroundColor: COLORS.white,
    paddingBottom: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  filterRow: {
    flexDirection: 'row',
  },
  filterRowContent: {
    paddingHorizontal: SPACING.xl,
    paddingVertical: SPACING.xs,
    gap: SPACING.xs,
  },
  filterTag: {
    paddingHorizontal: SPACING.md,
    paddingVertical: 6,
    borderRadius: 100,
    borderWidth: 1,
    borderColor: COLORS.border,
    backgroundColor: COLORS.background,
  },
  filterTagActive: {
    backgroundColor: 'rgba(37, 87, 167, 0.1)',
    borderColor: COLORS.primary,
  },
  filterTagText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  filterTagTextActive: {
    color: COLORS.primary,
    fontWeight: '700',
  },
  listContent: {
    padding: SPACING.xl,
    paddingBottom: 80,
  },
  card: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.sm,
    ...SHADOWS.sm,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.border,
  },
  cardUnread: {
    borderLeftColor: COLORS.primary,
    backgroundColor: 'rgba(37, 87, 167, 0.02)',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.xs,
  },
  cardHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    paddingRight: SPACING.sm,
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.primary,
    marginRight: SPACING.xs,
  },
  letterPerihal: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
    flex: 1,
  },
  statusBadge: {
    paddingHorizontal: SPACING.sm,
    paddingVertical: 3,
    borderRadius: 100,
  },
  statusText: {
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  instruction: {
    fontSize: 13,
    color: COLORS.text,
    lineHeight: 18,
    fontStyle: 'italic',
    marginBottom: SPACING.sm,
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginBottom: SPACING.sm,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  actorInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 0.6,
  },
  actorLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginRight: 4,
  },
  actorName: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.text,
    flex: 1,
  },
  deadlineInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 0.4,
    justifyContent: 'flex-end',
  },
  deadlineLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginRight: 4,
  },
  deadlineVal: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.text,
  },
  deadlineOverdue: {
    color: COLORS.danger,
    fontWeight: '700',
  },
  footerLoader: {
    marginVertical: SPACING.md,
  },
  emptyContainer: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.xxl,
    alignItems: 'center',
    marginTop: SPACING.xl,
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
  fab: {
    position: 'absolute',
    right: SPACING.xl,
    bottom: SPACING.xl,
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: COLORS.accent,
    justifyContent: 'center',
    alignItems: 'center',
    ...SHADOWS.lg,
  },
  fabText: {
    fontSize: 28,
    color: COLORS.white,
    fontWeight: '600',
    lineHeight: 32,
  },
});
