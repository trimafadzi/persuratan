import React, { useState, useEffect, useCallback } from 'react';
import {
  StyleSheet, Text, View, FlatList, TextInput, TouchableOpacity,
  ActivityIndicator, SafeAreaView, StatusBar, ScrollView,
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { apiClient } from '../api/client';
import { useAuthStore } from '../store/authStore';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';
import { CardListLoader } from '../components/SkeletonLoader';
import EmptyState from '../components/EmptyState';

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
  surat_masuk: { id: number; nomor_surat: string; perihal: string; pengirim: string; sifat: string } | null;
  pemberi: { id: number; nama_lengkap: string; jabatan: string; initials: string } | null;
  penerima: Array<{ id: number; nama_lengkap: string; jabatan: string; initials: string; is_read: boolean; read_at: string | null }>;
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
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const [tab, setTab] = useState<'masuk' | 'keluar'>('masuk');
  const [disposisiList, setDisposisiList] = useState<DisposisiItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('');
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  const fetchDisposisi = useCallback(async (pageNum = 1, isRefresh = false) => {
    try {
      if (pageNum === 1 && !isRefresh) setLoading(true);
      else if (pageNum > 1) setLoadingMore(true);

      const response = await apiClient.get('/disposisi', {
        params: { tab, search, status: selectedStatus, page: pageNum, per_page: 15 },
      });

      const fetchedData = response.data.data;
      const paginationMeta = response.data.meta;

      if (pageNum === 1) setDisposisiList(fetchedData);
      else setDisposisiList((prev) => [...prev, ...fetchedData]);

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

  useFocusEffect(useCallback(() => { fetchDisposisi(1); }, [fetchDisposisi]));

  const handleSearchSubmit = () => fetchDisposisi(1);
  const handleRefresh = () => { setRefreshing(true); fetchDisposisi(1, true); };
  const handleLoadMore = () => { if (page < lastPage && !loadingMore && !loading) fetchDisposisi(page + 1); };

  const isUnreadForMe = (item: DisposisiItem) => {
    if (tab === 'keluar') return false;
    const me = item.penerima?.find((p) => p.id === currentUser?.id);
    return me ? !me.is_read : false;
  };

  const getStatusBgColor = (status: string) => {
    switch (status) {
      case 'pending': return '#fef3c7';
      case 'diteruskan': return '#eff6ff';
      case 'selesai': return '#dcfce7';
      case 'dibatalkan': return '#fee2e2';
      default: return '#f1f5f9';
    }
  };

  const getStatusTextColor = (status: string) => {
    switch (status) {
      case 'pending': return colors.warningDark;
      case 'diteruskan': return colors.primaryLight;
      case 'selesai': return colors.successLight;
      case 'dibatalkan': return colors.danger;
      default: return colors.textMuted;
    }
  };

  const renderItem = ({ item }: { item: DisposisiItem }) => {
    const unread = isUnreadForMe(item);
    const deadlineFormatted = item.tanggal_deadline || 'Tidak ada deadline';

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

        <Text style={styles.instruction} numberOfLines={2}>"{item.isi_disposisi}"</Text>
        <View style={styles.divider} />

        <View style={styles.cardFooter}>
          <View style={styles.actorInfo}>
            <Text style={styles.actorLabel}>{tab === 'masuk' ? 'Dari:' : 'Kepada:'}</Text>
            <Text style={styles.actorName} numberOfLines={1}>
              {tab === 'masuk' ? item.pemberi?.nama_lengkap || 'System' : item.penerima?.map((p) => p.nama_lengkap).join(', ') || '-'}
            </Text>
          </View>
          <View style={styles.deadlineInfo}>
            <Text style={styles.deadlineLabel}>Deadline:</Text>
            <Text style={[styles.deadlineVal, item.is_overdue && styles.deadlineOverdue]}>{deadlineFormatted}</Text>
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />

      <View style={styles.tabHeader}>
        <TouchableOpacity style={[styles.tabButton, tab === 'masuk' && styles.tabButtonActive]} onPress={() => { setTab('masuk'); setSelectedStatus(''); }}>
          <Text style={[styles.tabButtonText, tab === 'masuk' && styles.tabButtonTextActive]}>📥 Disposisi Masuk</Text>
        </TouchableOpacity>
        <TouchableOpacity style={[styles.tabButton, tab === 'keluar' && styles.tabButtonActive]} onPress={() => { setTab('keluar'); setSelectedStatus(''); }}>
          <Text style={[styles.tabButtonText, tab === 'keluar' && styles.tabButtonTextActive]}>📤 Disposisi Keluar</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.searchSection}>
        <View style={styles.searchContainer}>
          <TextInput style={styles.searchInput} placeholder="Cari isi instruksi disposisi..." placeholderTextColor={colors.textMuted} value={search} onChangeText={setSearch} onSubmitEditing={handleSearchSubmit} returnKeyType="search" />
          {search ? (
            <TouchableOpacity onPress={() => { setSearch(''); setTimeout(() => fetchDisposisi(1), 50); }} style={styles.clearSearch}>
              <Text style={styles.clearSearchText}>×</Text>
            </TouchableOpacity>
          ) : null}
        </View>
        <TouchableOpacity style={styles.searchButton} onPress={handleSearchSubmit}>
          <Text style={styles.searchButtonText}>Cari</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.filterSection}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.filterRow} contentContainerStyle={styles.filterRowContent}>
          {STATUS_FILTERS.map((f) => (
            <TouchableOpacity key={f.value} style={[styles.filterTag, selectedStatus === f.value && styles.filterTagActive]} onPress={() => setSelectedStatus(f.value)}>
              <Text style={[styles.filterTagText, selectedStatus === f.value && styles.filterTagTextActive]}>{f.label}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      <FilterEffect trigger={[tab, selectedStatus]} effect={() => fetchDisposisi(1)} />

      {loading ? (
        <CardListLoader itemCount={6} />
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
          ListFooterComponent={loadingMore ? <ActivityIndicator size="small" color={colors.primary} style={styles.footerLoader} /> : null}
          ListEmptyComponent={<EmptyState icon="📋" title="Tidak ada disposisi yang cocok." />}
        />
      )}

      <TouchableOpacity style={styles.fab} onPress={() => navigation.navigate('DisposisiCreate', {})} activeOpacity={0.8}>
        <Text style={styles.fabText}>+</Text>
      </TouchableOpacity>
    </SafeAreaView>
  );
}

function FilterEffect({ trigger, effect }: { trigger: any[]; effect: () => void }) {
  useEffect(() => { effect();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, trigger);
  return null;
}

const getStyles = (colors: ThemeColors) => StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background },
  tabHeader: { flexDirection: 'row', backgroundColor: colors.white, borderBottomWidth: 1, borderBottomColor: colors.border },
  tabButton: { flex: 1, paddingVertical: SPACING.md, alignItems: 'center', borderBottomWidth: 2, borderBottomColor: 'transparent' },
  tabButtonActive: { borderBottomColor: colors.primary },
  tabButtonText: { fontSize: 14, fontWeight: '600', color: colors.textMuted },
  tabButtonTextActive: { color: colors.primary, fontWeight: '700' },
  searchSection: { flexDirection: 'row', paddingHorizontal: SPACING.xl, paddingTop: SPACING.md, paddingBottom: SPACING.xs, backgroundColor: colors.white, alignItems: 'center', gap: SPACING.sm },
  searchContainer: { flex: 1, flexDirection: 'row', alignItems: 'center', backgroundColor: colors.background, borderWidth: 1, borderColor: colors.border, borderRadius: SIZES.radiusSm, paddingRight: SPACING.xs },
  searchInput: { flex: 1, paddingHorizontal: SPACING.md, paddingVertical: 8, fontSize: 13, color: colors.text },
  clearSearch: { padding: SPACING.xs },
  clearSearchText: { fontSize: 18, color: colors.textMuted, fontWeight: '700' },
  searchButton: { backgroundColor: colors.primary, paddingHorizontal: SPACING.md, paddingVertical: 9, borderRadius: SIZES.radiusSm },
  searchButtonText: { color: colors.white, fontWeight: '700', fontSize: 12 },
  filterSection: { backgroundColor: colors.white, paddingBottom: SPACING.sm, borderBottomWidth: 1, borderBottomColor: colors.border },
  filterRow: { flexDirection: 'row' },
  filterRowContent: { paddingHorizontal: SPACING.xl, paddingVertical: SPACING.xs, gap: SPACING.xs },
  filterTag: { paddingHorizontal: SPACING.md, paddingVertical: 6, borderRadius: 100, borderWidth: 1, borderColor: colors.border, backgroundColor: colors.background },
  filterTagActive: { backgroundColor: 'rgba(37, 87, 167, 0.1)', borderColor: colors.primary },
  filterTagText: { fontSize: 11, fontWeight: '600', color: colors.textMuted },
  filterTagTextActive: { color: colors.primary, fontWeight: '700' },
  listContent: { padding: SPACING.xl, paddingBottom: 80 },
  card: { backgroundColor: colors.white, borderRadius: SIZES.radiusMd, padding: SPACING.md, marginBottom: SPACING.sm, ...SHADOWS.sm, borderLeftWidth: 4, borderLeftColor: colors.border },
  cardUnread: { borderLeftColor: colors.primary, backgroundColor: 'rgba(37, 87, 167, 0.02)' },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: SPACING.xs },
  cardHeaderLeft: { flexDirection: 'row', alignItems: 'center', flex: 1, paddingRight: SPACING.sm },
  unreadDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: colors.primary, marginRight: SPACING.xs },
  letterPerihal: { fontSize: 14, fontWeight: '700', color: colors.text, flex: 1 },
  statusBadge: { paddingHorizontal: SPACING.sm, paddingVertical: 3, borderRadius: 100 },
  statusText: { fontSize: 10, fontWeight: '700', textTransform: 'uppercase' },
  instruction: { fontSize: 13, color: colors.text, lineHeight: 18, fontStyle: 'italic', marginBottom: SPACING.sm },
  divider: { height: 1, backgroundColor: colors.border, marginBottom: SPACING.sm },
  cardFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  actorInfo: { flexDirection: 'row', alignItems: 'center', flex: 0.6 },
  actorLabel: { fontSize: 11, color: colors.textMuted, marginRight: 4 },
  actorName: { fontSize: 11, fontWeight: '600', color: colors.text, flex: 1 },
  deadlineInfo: { flexDirection: 'row', alignItems: 'center', flex: 0.4, justifyContent: 'flex-end' },
  deadlineLabel: { fontSize: 11, color: colors.textMuted, marginRight: 4 },
  deadlineVal: { fontSize: 11, fontWeight: '600', color: colors.text },
  deadlineOverdue: { color: colors.danger, fontWeight: '700' },
  footerLoader: { marginVertical: SPACING.md },
  fab: { position: 'absolute', right: SPACING.xl, bottom: SPACING.xl, width: 56, height: 56, borderRadius: 28, backgroundColor: colors.accent, justifyContent: 'center', alignItems: 'center', ...SHADOWS.lg },
  fabText: { fontSize: 28, color: colors.white, fontWeight: '600', lineHeight: 32 },
});
