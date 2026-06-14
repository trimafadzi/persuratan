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
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';
import { CardListLoader } from '../components/SkeletonLoader';
import EmptyState from '../components/EmptyState';

interface SuratKeluarItem {
  id: number;
  nomor_surat_otomatis: string;
  tanggal: string;
  penerima: string;
  perihal: string;
  sifat: string;
  sifat_label: string;
  status: string;
  file_url: string | null;
  file_name: string | null;
}

const SIFAT_FILTERS = [
  { label: 'Semua Sifat', value: '' },
  { label: 'Biasa', value: 'biasa' },
  { label: 'Penting', value: 'penting' },
  { label: 'Rahasia', value: 'rahasia' },
  { label: 'Segera', value: 'segera' },
];

export default function SuratKeluarListScreen() {
  const navigation = useNavigation<any>();
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const [suratList, setSuratList] = useState<SuratKeluarItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');
  const [selectedSifat, setSelectedSifat] = useState('');
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  const fetchSurat = useCallback(async (pageNum = 1, isRefresh = false) => {
    try {
      if (pageNum === 1 && !isRefresh) setLoading(true);
      else if (pageNum > 1) setLoadingMore(true);

      const response = await apiClient.get('/surat-keluar', {
        params: { search, sifat: selectedSifat, page: pageNum, per_page: 15 },
      });

      const fetchedData = response.data.data;
      const paginationMeta = response.data.meta;

      if (pageNum === 1) setSuratList(fetchedData);
      else setSuratList((prev) => [...prev, ...fetchedData]);

      setPage(paginationMeta.current_page);
      setLastPage(paginationMeta.last_page);
    } catch (e) {
      console.error('[SuratKeluarList] Gagal memuat surat:', e);
    } finally {
      setLoading(false);
      setLoadingMore(false);
      setRefreshing(false);
    }
  }, [search, selectedSifat]);

  useFocusEffect(useCallback(() => { fetchSurat(1); }, [fetchSurat]));

  const handleSearchSubmit = () => fetchSurat(1);
  const handleRefresh = () => { setRefreshing(true); fetchSurat(1, true); };
  const handleLoadMore = () => { if (page < lastPage && !loadingMore && !loading) fetchSurat(page + 1); };

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

  const renderItem = ({ item }: { item: SuratKeluarItem }) => (
    <TouchableOpacity
      style={styles.suratCard}
      onPress={() => navigation.navigate('SuratKeluarDetail', { id: item.id })}
      activeOpacity={0.7}
    >
      <View style={styles.suratLeft}>
        <View style={styles.statusDot} />
        <View style={styles.suratInfo}>
          <Text style={styles.suratPerihal} numberOfLines={1}>{item.perihal}</Text>
          <Text style={styles.suratMeta} numberOfLines={1}>
            No: {item.nomor_surat_otomatis || 'Draft'} | Kepada: {item.penerima}
          </Text>
          <Text style={styles.suratDate}>Tanggal: {item.tanggal}</Text>
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
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Surat Keluar</Text>
      </View>

      <View style={styles.searchSection}>
        <View style={styles.searchContainer}>
          <TextInput
            style={styles.searchInput}
            placeholder="Cari perihal, nomor, atau penerima..."
            placeholderTextColor={colors.textMuted}
            value={search}
            onChangeText={setSearch}
            onSubmitEditing={handleSearchSubmit}
            returnKeyType="search"
          />
          {search ? (
            <TouchableOpacity onPress={() => { setSearch(''); setTimeout(() => fetchSurat(1), 50); }} style={styles.clearSearch}>
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
          {SIFAT_FILTERS.map((f) => (
            <TouchableOpacity
              key={f.value}
              style={[styles.filterTag, selectedSifat === f.value && styles.filterTagActive]}
              onPress={() => setSelectedSifat(f.value)}
            >
              <Text style={[styles.filterTagText, selectedSifat === f.value && styles.filterTagTextActive]}>
                {f.label}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      <FilterEffect trigger={[selectedSifat]} effect={() => fetchSurat(1)} />

      {loading ? (
        <CardListLoader itemCount={6} />
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
          ListFooterComponent={loadingMore ? <ActivityIndicator size="small" color={colors.primary} style={styles.footerLoader} /> : null}
          ListEmptyComponent={<EmptyState icon="📤" title="Tidak ada surat keluar yang cocok." />}
        />
      )}

      <TouchableOpacity style={styles.fab} onPress={() => navigation.navigate('SuratKeluarCreate')} activeOpacity={0.8}>
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
  header: { backgroundColor: colors.white, paddingVertical: SPACING.md, paddingHorizontal: SPACING.xl, borderBottomWidth: 1, borderBottomColor: colors.border, alignItems: 'center' },
  headerTitle: { fontSize: 18, fontWeight: '700', color: colors.primary },
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
  suratCard: { backgroundColor: colors.white, borderRadius: SIZES.radiusMd, padding: SPACING.md, marginBottom: SPACING.sm, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', ...SHADOWS.sm },
  suratLeft: { flexDirection: 'row', alignItems: 'center', flex: 1, paddingRight: SPACING.sm },
  statusDot: { width: 10, height: 10, borderRadius: 5, marginRight: SPACING.md, backgroundColor: colors.successLight },
  suratInfo: { flex: 1 },
  suratPerihal: { fontSize: 14, fontWeight: '700', color: colors.text },
  suratMeta: { fontSize: 12, color: colors.textMuted, marginTop: 2 },
  suratDate: { fontSize: 10, color: colors.textMuted, marginTop: 2 },
  sifatBadge: { paddingHorizontal: SPACING.sm, paddingVertical: 3, borderRadius: 100 },
  sifatText: { fontSize: 10, fontWeight: '700', textTransform: 'uppercase' },
  footerLoader: { marginVertical: SPACING.md },
  fab: { position: 'absolute', right: SPACING.xl, bottom: SPACING.xl, width: 56, height: 56, borderRadius: 28, backgroundColor: colors.accent, justifyContent: 'center', alignItems: 'center', ...SHADOWS.lg },
  fabText: { fontSize: 28, color: colors.white, fontWeight: '600', lineHeight: 32 },
});
