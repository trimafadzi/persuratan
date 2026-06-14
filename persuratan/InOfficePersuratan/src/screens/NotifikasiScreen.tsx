import React, { useState, useEffect, useCallback } from 'react';
import {
  StyleSheet,
  Text,
  View,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { apiClient } from '../api/client';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';
import { CardListLoader } from '../components/SkeletonLoader';
import EmptyState from '../components/EmptyState';

interface NotifikasiItem {
  id: number;
  judul: string;
  pesan: string;
  tipe: string;
  entity_type: string;
  entity_id: number;
  is_read: boolean;
  read_at: string | null;
  created_at: string;
}

export default function NotifikasiScreen() {
  const navigation = useNavigation<any>();
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const [notifList, setNotifList] = useState<NotifikasiItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  const fetchNotifikasi = useCallback(async (pageNum = 1, isRefresh = false) => {
    try {
      if (pageNum === 1 && !isRefresh) {
        setLoading(true);
      } else if (pageNum > 1) {
        setLoadingMore(true);
      }

      const response = await apiClient.get('/notifikasi', {
        params: {
          page: pageNum,
          per_page: 15,
        },
      });

      const fetchedData = response.data.data;
      const paginationMeta = response.data.meta;

      if (pageNum === 1) {
        setNotifList(fetchedData);
      } else {
        setNotifList((prev) => [...prev, ...fetchedData]);
      }

      setPage(paginationMeta.current_page);
      setLastPage(paginationMeta.last_page);
    } catch (e) {
      console.error('[Notifikasi] Gagal memuat notifikasi:', e);
    } finally {
      setLoading(false);
      setLoadingMore(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchNotifikasi(1);
  }, [fetchNotifikasi]);

  const handleRefresh = () => {
    setRefreshing(true);
    fetchNotifikasi(1, true);
  };

  const handleLoadMore = () => {
    if (page < lastPage && !loadingMore && !loading) {
      fetchNotifikasi(page + 1);
    }
  };

  const handleNotifPress = async (item: NotifikasiItem) => {
    if (!item.is_read) {
      try {
        await apiClient.patch(`/notifikasi/${item.id}/read`);
        setNotifList((prev) =>
          prev.map((n) => (n.id === item.id ? { ...n, is_read: true } : n))
        );
      } catch (err) {
        console.error('[Notifikasi] Gagal tandai baca:', err);
      }
    }

    if (item.entity_type === 'Disposisi') {
      navigation.navigate('DisposisiTab', {
        screen: 'DisposisiDetail',
        params: { id: item.entity_id },
      });
    } else if (item.entity_type === 'SuratMasuk') {
      navigation.navigate('SuratMasukTab', {
        screen: 'SuratMasukDetail',
        params: { id: item.entity_id },
      });
    } else if (item.entity_type === 'SuratKeluar') {
      navigation.navigate('SuratMasukTab', {
        screen: 'SuratKeluarDetail',
        params: { id: item.entity_id },
      });
    }
  };

  const getNotifIcon = (tipe: string) => {
    switch (tipe) {
      case 'disposisi': return '📋';
      case 'surat_masuk': return '📬';
      case 'surat_keluar': return '📤';
      case 'laporan': return '📝';
      default: return '🔔';
    }
  };

  const formatNotifTime = (dateStr: string) => {
    try {
      const date = new Date(dateStr);
      return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
      });
    } catch {
      return dateStr;
    }
  };

  const renderItem = ({ item }: { item: NotifikasiItem }) => (
    <TouchableOpacity
      style={[styles.notifCard, !item.is_read && styles.notifCardUnread]}
      onPress={() => handleNotifPress(item)}
      activeOpacity={0.7}
    >
      <View style={styles.notifIconContainer}>
        <Text style={styles.notifIcon}>{getNotifIcon(item.tipe)}</Text>
      </View>
      <View style={styles.notifContent}>
        <View style={styles.notifHeader}>
          <Text style={[styles.notifTitle, !item.is_read && styles.notifTitleUnread]}>
            {item.judul}
          </Text>
          {!item.is_read && <View style={styles.unreadDot} />}
        </View>
        <Text style={styles.notifMessage} numberOfLines={2}>
          {item.pesan}
        </Text>
        <Text style={styles.notifTime}>{formatNotifTime(item.created_at)}</Text>
      </View>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />

      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Notifikasi Saya</Text>
      </View>

      {/* List */}
      {loading ? (
        <CardListLoader itemCount={8} />
      ) : (
        <FlatList
          data={notifList}
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
                color={colors.primary}
                style={styles.footerLoader}
              />
            ) : null
          }
          ListEmptyComponent={
            <EmptyState icon="🔔" title="Tidak ada notifikasi baru." />
          }
        />
      )}
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
    paddingVertical: SPACING.md,
    paddingHorizontal: SPACING.xl,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: colors.primary,
  },
  listContent: {
    padding: SPACING.xl,
  },
  notifCard: {
    flexDirection: 'row',
    backgroundColor: colors.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.sm,
    ...SHADOWS.sm,
    borderLeftWidth: 4,
    borderLeftColor: colors.border,
  },
  notifCardUnread: {
    borderLeftColor: colors.primary,
    backgroundColor: 'rgba(37, 87, 167, 0.02)',
  },
  notifIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: colors.background,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: SPACING.md,
  },
  notifIcon: {
    fontSize: 20,
  },
  notifContent: {
    flex: 1,
  },
  notifHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 2,
  },
  notifTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.textMuted,
    flex: 1,
  },
  notifTitleUnread: {
    color: colors.text,
    fontWeight: '800',
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: colors.primary,
    marginLeft: SPACING.xs,
  },
  notifMessage: {
    fontSize: 13,
    color: colors.text,
    lineHeight: 18,
  },
  notifTime: {
    fontSize: 10,
    color: colors.textMuted,
    marginTop: 6,
  },
  footerLoader: {
    marginVertical: SPACING.md,
  },
});
