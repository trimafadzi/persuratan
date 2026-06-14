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
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

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

  const [notifList, setNotifList] = useState<NotifikasiItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  // Pagination
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
    // 1. Mark as read on server if not read yet
    if (!item.is_read) {
      try {
        await apiClient.patch(`/notifikasi/${item.id}/read`);
        // Update local state to show read
        setNotifList((prev) =>
          prev.map((n) => (n.id === item.id ? { ...n, is_read: true } : n))
        );
      } catch (err) {
        console.error('[Notifikasi] Gagal tandai baca:', err);
      }
    }

    // 2. Perform redirection logic based on entity_type
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
      navigation.navigate('SuratKeluarTab', {
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
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.headerBackBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Notifikasi Saya</Text>
        <View style={{ width: 40 }} />
      </View>

      {/* List */}
      {loading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
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
                color={COLORS.primary}
                style={styles.footerLoader}
              />
            ) : null
          }
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>🔔</Text>
              <Text style={styles.emptyText}>Tidak ada notifikasi baru.</Text>
            </View>
          }
        />
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
  listContent: {
    padding: SPACING.xl,
  },
  notifCard: {
    flexDirection: 'row',
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusMd,
    padding: SPACING.md,
    marginBottom: SPACING.sm,
    ...SHADOWS.sm,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.border,
  },
  notifCardUnread: {
    borderLeftColor: COLORS.primary,
    backgroundColor: 'rgba(37, 87, 167, 0.02)',
  },
  notifIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.background,
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
    color: COLORS.textMuted,
    flex: 1,
  },
  notifTitleUnread: {
    color: COLORS.text,
    fontWeight: '800',
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.primary,
    marginLeft: SPACING.xs,
  },
  notifMessage: {
    fontSize: 13,
    color: COLORS.text,
    lineHeight: 18,
  },
  notifTime: {
    fontSize: 10,
    color: COLORS.textMuted,
    marginTop: 6,
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
});
