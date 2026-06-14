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
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

interface SuratMasukItem {
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

const STATUS_FILTERS = [
  { label: 'Semua Status', value: '' },
  { label: 'Belum Dibaca', value: 'belum_dibaca' },
  { label: 'Dibaca', value: 'dibaca' },
  { label: 'Didisposisi', value: 'didisposisi' },
  { label: 'Selesai', value: 'selesai' },
];

const SIFAT_FILTERS = [
  { label: 'Semua Sifat', value: '' },
  { label: 'Biasa', value: 'biasa' },
  { label: 'Penting', value: 'penting' },
  { label: 'Rahasia', value: 'rahasia' },
  { label: 'Segera', value: 'segera' },
];

export default function SuratMasukListScreen() {
  const navigation = useNavigation<any>();

  const [suratList, setSuratList] = useState<SuratMasukItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  // Filter & Search states
  const [search, setSearch] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('');
  const [selectedSifat, setSelectedSifat] = useState('');

  // Pagination states
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  const fetchSurat = useCallback(async (pageNum = 1, isRefresh = false) => {
    try {
      if (pageNum === 1 && !isRefresh) {
        setLoading(true);
      } else if (pageNum > 1) {
        setLoadingMore(true);
      }

      const response = await apiClient.get('/surat-masuk', {
        params: {
          search,
          status: selectedStatus,
          sifat: selectedSifat,
          page: pageNum,
          per_page: 15,
        },
      });

      const fetchedData = response.data.data;
      const paginationMeta = response.data.meta;

      if (pageNum === 1) {
        setSuratList(fetchedData);
      } else {
        setSuratList((prev) => [...prev, ...fetchedData]);
      }

      setPage(paginationMeta.current_page);
      setLastPage(paginationMeta.last_page);
    } catch (e) {
      console.error('[SuratList] Gagal memuat surat:', e);
    } finally {
      setLoading(false);
      setLoadingMore(false);
      setRefreshing(false);
    }
  }, [search, selectedStatus, selectedSifat]);

  // Muat ulang ketika screen mendapat fokus kembali (misalnya setelah buat surat baru atau tandai baca)
  useFocusEffect(
    useCallback(() => {
      fetchSurat(1);
    }, [fetchSurat])
  );

  // Trigger search saat search input berubah (atau ditekan enter)
  const handleSearchSubmit = () => {
    fetchSurat(1);
  };

  const handleRefresh = () => {
    setRefreshing(true);
    fetchSurat(1, true);
  };

  const handleLoadMore = () => {
    if (page < lastPage && !loadingMore && !loading) {
      fetchSurat(page + 1);
    }
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

  const renderItem = ({ item }: { item: SuratMasukItem }) => (
    <TouchableOpacity
      style={styles.suratCard}
      onPress={() => navigation.navigate('SuratMasukDetail', { id: item.id })}
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
            No: {item.nomor_surat} | Dari: {item.pengirim}
          </Text>
          <Text style={styles.suratDate}>
            Terima: {item.tanggal_surat}
          </Text>
        </View>
      </View>

      <View style={[styles.sifatBadge, { backgroundColor: getSifatColor(item.sifat) }]}>
        <Text style={[styles.sifatText, { color: getSifatTextColor(item.sifat) }]}>
          {item.sifat_label}
        </Text>
      </View>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />
      
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Surat Masuk</Text>
      </View>

      {/* Search Bar */}
      <View style={styles.searchSection}>
        <View style={styles.searchContainer}>
          <TextInput
            style={styles.searchInput}
            placeholder="Cari perihal, nomor, atau pengirim..."
            placeholderTextColor={COLORS.textMuted}
            value={search}
            onChangeText={setSearch}
            onSubmitEditing={handleSearchSubmit}
            returnKeyType="search"
          />
          {search ? (
            <TouchableOpacity 
              onPress={() => { setSearch(''); setTimeout(() => fetchSurat(1), 50); }}
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

      {/* Dynamic Filters Bar */}
      <View style={styles.filterSection}>
        {/* Status Filters */}
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
              ]}
              onPress={() => {
                setSelectedStatus(f.value);
                // Trigger reload via state change hook
              }}
            >
              <Text
                style={[
                  styles.filterTagText,
                  selectedStatus === f.value && styles.filterTagTextActive,
                ]}
              >
                {f.label}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        {/* Sifat Filters */}
        <ScrollView 
          horizontal 
          showsHorizontalScrollIndicator={false} 
          style={[styles.filterRow, { marginTop: SPACING.xs }]}
          contentContainerStyle={styles.filterRowContent}
        >
          {SIFAT_FILTERS.map((f) => (
            <TouchableOpacity
              key={f.value}
              style={[
                styles.filterTag,
                selectedSifat === f.value && styles.filterTagActive,
                selectedSifat === f.value && { backgroundColor: 'rgba(230, 57, 70, 0.1)', borderColor: COLORS.accent },
              ]}
              onPress={() => {
                setSelectedSifat(f.value);
              }}
            >
              <Text
                style={[
                  styles.filterTagText,
                  selectedSifat === f.value && styles.filterTagTextActive,
                  selectedSifat === f.value && { color: COLORS.accent },
                ]}
              >
                {f.label}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      {/* Auto reload when filters change */}
      <FilterEffect trigger={[selectedStatus, selectedSifat]} effect={() => fetchSurat(1)} />

      {/* Mail List */}
      {loading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
      ) : (
        <FlatList
          data={suratList}
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
              <Text style={styles.emptyIcon}>📬</Text>
              <Text style={styles.emptyText}>Tidak ada surat masuk yang cocok.</Text>
            </View>
          }
        />
      )}

      {/* Floating Action Button (FAB) */}
      <TouchableOpacity
        style={styles.fab}
        onPress={() => navigation.navigate('SuratMasukCreate')}
        activeOpacity={0.8}
      >
        <Text style={styles.fabText}>+</Text>
      </TouchableOpacity>
    </SafeAreaView>
  );
}

// Custom Helper component untuk trigger callback ketika state berubah
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
  header: {
    backgroundColor: COLORS.white,
    paddingVertical: SPACING.md,
    paddingHorizontal: SPACING.xl,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.primary,
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
    paddingBottom: 80, // Space agar FAB tidak menutupi item terakhir
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
