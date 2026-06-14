import React, { useState, useEffect } from 'react';
import {
  StyleSheet,
  Text,
  View,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
  Linking,
  Alert,
} from 'react-native';
import { useRoute, useNavigation } from '@react-navigation/native';
import { apiClient } from '../api/client';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

interface DisposisiItem {
  id: number;
  pemberi: { id: number; nama: string };
  penerima: Array<{ id: number; nama: string }>;
  isi_disposisi: string;
  status: string;
  status_label: string;
  tanggal_deadline: string | null;
  created_at: string;
}

interface SuratMasukDetail {
  id: number;
  nomor_surat: string;
  tanggal_surat: string;
  tanggal_terima: string;
  pengirim: string;
  perihal: string;
  sifat: string;
  sifat_label: string;
  ringkasan: string | null;
  status: string;
  status_label: string;
  status_color: string;
  file_url: string | null;
  file_name: string | null;
  unit_kerja: { id: number; nama: string } | null;
  created_by: { id: number; nama: string } | null;
  disposisi?: DisposisiItem[];
}

export default function SuratMasukDetailScreen() {
  const route = useRoute<any>();
  const navigation = useNavigation();
  const suratId = route.params?.id;

  const [surat, setSurat] = useState<SuratMasukDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [updatingStatus, setUpdatingStatus] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    const fetchDetail = async () => {
      try {
        setLoading(true);
        setErrorMsg('');
        const response = await apiClient.get(`/surat-masuk/${suratId}`);
        setSurat(response.data.data);
      } catch (e) {
        console.error('[SuratDetail] Gagal memuat detail:', e);
        setErrorMsg('Gagal memuat detail surat.');
      } finally {
        setLoading(false);
      }
    };

    if (suratId) fetchDetail();
  }, [suratId]);

  const handleMarkAsRead = async () => {
    if (!surat) return;
    try {
      setUpdatingStatus(true);
      await apiClient.patch(`/surat-masuk/${suratId}/baca`);
      
      // Update state lokal
      setSurat((prev) => prev ? {
        ...prev,
        status: 'dibaca',
        status_label: 'Dibaca',
        status_color: 'warning'
      } : null);

      Alert.alert('Sukses', 'Surat ditandai sudah dibaca.');
    } catch (e) {
      console.error(e);
      Alert.alert('Error', 'Gagal menandai surat.');
    } finally {
      setUpdatingStatus(false);
    }
  };

  const handleOpenAttachment = () => {
    if (surat?.file_url) {
      Linking.openURL(surat.file_url).catch((err) => {
        console.error(err);
        Alert.alert('Error', 'Tidak dapat membuka link lampiran.');
      });
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

  const getStatusColor = (color: string) => {
    switch (color) {
      case 'danger': return '#fee2e2';
      case 'warning': return '#fef3c7';
      case 'info': return '#eff6ff';
      case 'success': return '#dcfce7';
      default: return '#f1f5f9';
    }
  };

  const getStatusTextColor = (color: string) => {
    switch (color) {
      case 'danger': return COLORS.danger;
      case 'warning': return COLORS.warningDark;
      case 'info': return COLORS.primaryLight;
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

  if (!surat) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.centerContainer}>
          <Text style={styles.errorText}>{errorMsg || 'Surat tidak ditemukan.'}</Text>
          <TouchableOpacity style={styles.backButton} onPress={() => navigation.goBack()}>
            <Text style={styles.backButtonText}>Kembali</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />
      
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.headerBackBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle} numberOfLines={1}>Detail Surat</Text>
        <View style={{ width: 40 }} /> {/* Spacer */}
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Main Details Card */}
        <View style={styles.detailsCard}>
          <View style={styles.badgeRow}>
            {/* Status Badge */}
            <View style={[styles.badge, { backgroundColor: getStatusColor(surat.status_color) }]}>
              <Text style={[styles.badgeText, { color: getStatusTextColor(surat.status_color) }]}>
                {surat.status_label}
              </Text>
            </View>

            {/* Sifat Badge */}
            <View style={[styles.badge, { backgroundColor: getSifatColor(surat.sifat) }]}>
              <Text style={[styles.badgeText, { color: getSifatTextColor(surat.sifat) }]}>
                {surat.sifat_label}
              </Text>
            </View>
          </View>

          <Text style={styles.perihalText}>{surat.perihal}</Text>
          <Text style={styles.nomorText}>No: {surat.nomor_surat}</Text>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Pengirim</Text>
            <Text style={styles.infoValue}>{surat.pengirim}</Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Tanggal Surat</Text>
            <Text style={styles.infoValue}>{surat.tanggal_surat}</Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Tanggal Terima</Text>
            <Text style={styles.infoValue}>{surat.tanggal_terima}</Text>
          </View>

          {surat.unit_kerja ? (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Ditujukan Ke</Text>
              <Text style={styles.infoValue}>{surat.unit_kerja.nama}</Text>
            </View>
          ) : null}

          {surat.ringkasan ? (
            <View style={styles.ringkasanSection}>
              <Text style={styles.ringkasanTitle}>Ringkasan Isi</Text>
              <Text style={styles.ringkasanText}>{surat.ringkasan}</Text>
            </View>
          ) : null}
        </View>

        {/* Attachment Section */}
        {surat.file_url ? (
          <View style={styles.attachmentCard}>
            <Text style={styles.sectionTitle}>Lampiran Dokumen</Text>
            <View style={styles.attachmentBox}>
              <Text style={styles.attachmentIcon}>📄</Text>
              <View style={styles.attachmentInfo}>
                <Text style={styles.attachmentName} numberOfLines={1}>
                  {surat.file_name || 'Scan_Surat_Masuk.pdf'}
                </Text>
                <Text style={styles.attachmentSize}>Ketuk untuk melihat file</Text>
              </View>
              <TouchableOpacity style={styles.openBtn} onPress={handleOpenAttachment}>
                <Text style={styles.openBtnText}>Buka</Text>
              </TouchableOpacity>
            </View>
          </View>
        ) : null}

        {/* Mark as Read Action Button */}
        {surat.status === 'belum_dibaca' ? (
          <TouchableOpacity
            style={styles.actionButton}
            onPress={handleMarkAsRead}
            disabled={updatingStatus}
            activeOpacity={0.8}
          >
            {updatingStatus ? (
              <ActivityIndicator size="small" color={COLORS.white} />
            ) : (
              <Text style={styles.actionButtonText}>Tandai Sudah Dibaca</Text>
            )}
          </TouchableOpacity>
        ) : null}

        {/* Timeline Disposisi Section */}
        <View style={styles.timelineSection}>
          <Text style={styles.sectionTitle}>Riwayat Disposisi</Text>
          
          {!surat.disposisi || surat.disposisi.length === 0 ? (
            <View style={styles.emptyTimeline}>
              <Text style={styles.emptyTimelineText}>Belum ada disposisi untuk surat ini.</Text>
            </View>
          ) : (
            <View style={styles.timelineContainer}>
              {surat.disposisi.map((disp, index) => {
                const isLast = index === (surat.disposisi?.length ?? 0) - 1;
                return (
                  <View key={disp.id} style={styles.timelineItem}>
                    {/* Node Visual */}
                    <View style={styles.timelineIndicator}>
                      <View style={styles.timelineDot} />
                      {!isLast ? <View style={styles.timelineLine} /> : null}
                    </View>

                    {/* Node Content */}
                    <View style={styles.timelineContent}>
                      <View style={styles.timelineHeader}>
                        <Text style={styles.timelineSender}>{disp.pemberi.nama}</Text>
                        <Text style={styles.timelineStatus}>
                          {disp.status_label}
                        </Text>
                      </View>

                      <Text style={styles.timelineReceiver}>
                        Kepada: {disp.penerima.map(p => p.nama).join(', ')}
                      </Text>

                      <Text style={styles.timelineInstruction}>
                        Instruksi: "{disp.isi_disposisi}"
                      </Text>

                      {disp.tanggal_deadline ? (
                        <Text style={styles.timelineDeadline}>
                          Deadline: {disp.tanggal_deadline}
                        </Text>
                      ) : null}

                      <Text style={styles.timelineTime}>{disp.created_at}</Text>
                    </View>
                  </View>
                );
              })}
            </View>
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
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: SPACING.xl,
  },
  errorText: {
    color: COLORS.danger,
    fontSize: 16,
    fontWeight: '600',
    marginBottom: SPACING.md,
    textAlign: 'center',
  },
  backButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: SPACING.xl,
    paddingVertical: SPACING.md,
    borderRadius: SIZES.radiusSm,
  },
  backButtonText: {
    color: COLORS.white,
    fontWeight: '700',
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
  scrollContent: {
    padding: SPACING.xl,
  },
  detailsCard: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
    marginBottom: SPACING.md,
  },
  badgeRow: {
    flexDirection: 'row',
    gap: SPACING.sm,
    marginBottom: SPACING.sm,
  },
  badge: {
    paddingHorizontal: SPACING.md,
    paddingVertical: 4,
    borderRadius: 100,
  },
  badgeText: {
    fontSize: 10,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  perihalText: {
    fontSize: 18,
    fontWeight: '800',
    color: COLORS.text,
    lineHeight: 24,
    marginBottom: SPACING.xs,
  },
  nomorText: {
    fontSize: 12,
    color: COLORS.textMuted,
    fontWeight: '500',
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginVertical: SPACING.md,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: SPACING.xs,
  },
  infoLabel: {
    fontSize: 13,
    color: COLORS.textMuted,
    fontWeight: '500',
  },
  infoValue: {
    fontSize: 13,
    color: COLORS.text,
    fontWeight: '600',
    flex: 1,
    textAlign: 'right',
    marginLeft: SPACING.lg,
  },
  ringkasanSection: {
    marginTop: SPACING.md,
    backgroundColor: COLORS.background,
    padding: SPACING.md,
    borderRadius: SIZES.radiusSm,
  },
  ringkasanTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.primary,
    marginBottom: SPACING.xs,
  },
  ringkasanText: {
    fontSize: 13,
    color: COLORS.text,
    lineHeight: 18,
  },
  attachmentCard: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
    marginBottom: SPACING.md,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '800',
    color: COLORS.primary,
    marginBottom: SPACING.md,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  attachmentBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderRadius: SIZES.radiusSm,
    padding: SPACING.md,
  },
  attachmentIcon: {
    fontSize: 28,
    marginRight: SPACING.md,
  },
  attachmentInfo: {
    flex: 1,
    paddingRight: SPACING.sm,
  },
  attachmentName: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.text,
  },
  attachmentSize: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  openBtn: {
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.xs,
    borderRadius: SIZES.radiusSm,
  },
  openBtnText: {
    color: COLORS.white,
    fontWeight: '700',
    fontSize: 12,
  },
  actionButton: {
    backgroundColor: COLORS.primary,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    ...SHADOWS.sm,
    marginBottom: SPACING.md,
  },
  actionButtonText: {
    color: COLORS.white,
    fontSize: 15,
    fontWeight: '700',
  },
  timelineSection: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
    marginBottom: SPACING.xxl,
  },
  emptyTimeline: {
    paddingVertical: SPACING.md,
    alignItems: 'center',
  },
  emptyTimelineText: {
    color: COLORS.textMuted,
    fontSize: 13,
  },
  timelineContainer: {
    marginTop: SPACING.xs,
  },
  timelineItem: {
    flexDirection: 'row',
  },
  timelineIndicator: {
    alignItems: 'center',
    width: 24,
  },
  timelineDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: COLORS.primaryLight,
    zIndex: 10,
  },
  timelineLine: {
    width: 2,
    flex: 1,
    backgroundColor: COLORS.border,
    marginVertical: 2,
  },
  timelineContent: {
    flex: 1,
    marginLeft: SPACING.md,
    paddingBottom: SPACING.lg,
  },
  timelineHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  timelineSender: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
  },
  timelineStatus: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.accent,
    textTransform: 'uppercase',
  },
  timelineReceiver: {
    fontSize: 12,
    color: COLORS.textMuted,
    fontWeight: '500',
    marginTop: 2,
  },
  timelineInstruction: {
    fontSize: 13,
    color: COLORS.text,
    marginTop: SPACING.xs,
    fontStyle: 'italic',
  },
  timelineDeadline: {
    fontSize: 11,
    color: COLORS.danger,
    fontWeight: '600',
    marginTop: SPACING.xs,
  },
  timelineTime: {
    fontSize: 10,
    color: COLORS.textMuted,
    marginTop: SPACING.xs,
  },
});
