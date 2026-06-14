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
import { useAuthStore } from '../store/authStore';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';

interface UserDetail {
  id: number;
  nama_lengkap: string;
  jabatan: string;
  initials: string;
  is_read?: boolean;
  read_at?: string | null;
}

interface LaporanItem {
  id: number;
  isi_laporan: string;
  status: string;
  tanggapan: string | null;
  status_tanggapan: string | null;
  pelapor: { id: number; nama_lengkap: string } | null;
  file_bukti: Array<{
    id: number;
    file_url: string;
    file_name: string;
  }>;
  created_at: string;
}

interface DisposisiDetail {
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
  pemberi: UserDetail | null;
  penerima: UserDetail[];
  laporan?: LaporanItem[];
}

export default function DisposisiDetailScreen() {
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const route = useRoute<any>();
  const navigation = useNavigation<any>();
  const currentUser = useAuthStore((state) => state.user);
  const disposisiId = route.params?.id;

  const [disposisi, setDisposisi] = useState<DisposisiDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [cancelling, setCancelling] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  const fetchDetail = async () => {
    try {
      setLoading(true);
      setErrorMsg('');
      const response = await apiClient.get(`/disposisi/${disposisiId}`);
      setDisposisi(response.data.data);
    } catch (e) {
      console.error('[DisposisiDetail] Gagal memuat detail:', e);
      setErrorMsg('Gagal memuat detail disposisi.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (disposisiId) fetchDetail();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [disposisiId]);

  // Refetch when screen focuses (to update status if report/tanggapan was submitted)
  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', () => {
      if (disposisiId) {
        apiClient.get(`/disposisi/${disposisiId}`)
          .then(res => setDisposisi(res.data.data))
          .catch(e => console.error(e));
      }
    });
    return unsubscribe;
  }, [navigation, disposisiId]);

  const handleCancelDisposisi = () => {
    Alert.alert(
      'Konfirmasi',
      'Apakah Anda yakin ingin membatalkan disposisi ini?',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Ya, Batalkan',
          style: 'destructive',
          onPress: async () => {
            try {
              setCancelling(true);
              await apiClient.patch(`/disposisi/${disposisiId}/batal`);
              Alert.alert('Sukses', 'Disposisi berhasil dibatalkan.');
              fetchDetail();
            } catch (e) {
              console.error(e);
              Alert.alert('Error', 'Gagal membatalkan disposisi.');
            } finally {
              setCancelling(false);
            }
          },
        },
      ]
    );
  };

  const handleOpenFile = (url: string) => {
    Linking.openURL(url).catch((err) => {
      console.error(err);
      Alert.alert('Error', 'Tidak dapat membuka berkas.');
    });
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

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  if (!disposisi) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.centerContainer}>
          <Text style={styles.errorText}>{errorMsg || 'Disposisi tidak ditemukan.'}</Text>
          <TouchableOpacity style={styles.backButton} onPress={() => navigation.goBack()}>
            <Text style={styles.backButtonText}>Kembali</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  // Check user roles/permissions
  const isPemberi = disposisi.pemberi?.id === currentUser?.id;
  const isPenerima = disposisi.penerima?.some((p) => p.id === currentUser?.id);
  const isActive = disposisi.status !== 'selesai' && disposisi.status !== 'dibatalkan';

  // Check if there's any report waiting for review
  const latestReport = disposisi.laporan && disposisi.laporan.length > 0 
    ? disposisi.laporan[disposisi.laporan.length - 1] 
    : null;
  const hasPendingReport = latestReport && latestReport.status === 'terkirim' && !latestReport.status_tanggapan;

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.headerBackBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle} numberOfLines={1}>Rincian Disposisi</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Disposisi Info Card */}
        <View style={styles.detailsCard}>
          <View style={styles.badgeRow}>
            <View style={[styles.badge, { backgroundColor: getStatusBgColor(disposisi.status) }]}>
              <Text style={[styles.badgeText, { color: getStatusTextColor(disposisi.status) }]}>
                {disposisi.status_label}
              </Text>
            </View>
            {disposisi.is_overdue && (
              <View style={[styles.badge, { backgroundColor: '#fee2e2' }]}>
                <Text style={[styles.badgeText, { color: colors.danger }]}>TERLEWAT DEADLINE</Text>
              </View>
            )}
          </View>

          <Text style={styles.instructionText}>"{disposisi.isi_disposisi}"</Text>
          <Text style={styles.dateText}>Dibuat: {disposisi.created_at}</Text>
          
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Tenggat Waktu (Deadline)</Text>
            <Text style={[styles.infoValue, disposisi.is_overdue && styles.deadlineOverdue]}>
              {disposisi.tanggal_deadline || 'Tidak Ada'}
            </Text>
          </View>

          <View style={styles.divider} />

          {/* Surat Masuk Info */}
          <Text style={styles.sectionTitle}>Dokumen Rujukan</Text>
          {disposisi.surat_masuk ? (
            <View style={styles.suratCard}>
              <Text style={styles.suratPerihal}>{disposisi.surat_masuk.perihal}</Text>
              <Text style={styles.suratMeta}>No: {disposisi.surat_masuk.nomor_surat} | Dari: {disposisi.surat_masuk.pengirim}</Text>
              <TouchableOpacity
                style={styles.viewSuratBtn}
                onPress={() => {
                  navigation.navigate('SuratMasukTab', {
                    screen: 'SuratMasukDetail',
                    params: { id: disposisi.surat_masuk?.id },
                  });
                }}
              >
                <Text style={styles.viewSuratBtnText}>Lihat Berkas Surat Masuk 📬</Text>
              </TouchableOpacity>
            </View>
          ) : (
            <Text style={styles.emptyText}>Dokumen surat tidak ditemukan.</Text>
          )}
        </View>

        {/* Pemberi & Penerima Card */}
        <View style={styles.detailsCard}>
          <Text style={styles.sectionTitle}>Staf Terkait</Text>
          
          <View style={styles.peopleRow}>
            <Text style={styles.peopleLabel}>Pemberi:</Text>
            <View style={styles.peopleValueContainer}>
              <Text style={styles.peopleName}>{disposisi.pemberi?.nama_lengkap}</Text>
              <Text style={styles.peopleSub}>{disposisi.pemberi?.jabatan}</Text>
            </View>
          </View>

          <View style={styles.peopleRow}>
            <Text style={styles.peopleLabel}>Penerima:</Text>
            <View style={styles.peopleValueContainer}>
              {disposisi.penerima.map((p) => (
                <View key={p.id} style={styles.penerimaItem}>
                  <Text style={styles.peopleName}>• {p.nama_lengkap}</Text>
                  <Text style={styles.peopleSub}>
                    {p.jabatan} {p.is_read ? `(Dibaca: ${p.read_at})` : '(Belum Dibaca)'}
                  </Text>
                </View>
              ))}
            </View>
          </View>
        </View>

        {/* Laporan Pelaksanaan Card */}
        <View style={styles.detailsCard}>
          <Text style={styles.sectionTitle}>Laporan Pelaksanaan</Text>
          {!disposisi.laporan || disposisi.laporan.length === 0 ? (
            <Text style={styles.emptyText}>Belum ada laporan pelaksanaan yang dikirim.</Text>
          ) : (
            disposisi.laporan.map((lap) => (
              <View key={lap.id} style={styles.laporanBox}>
                <View style={styles.laporanHeader}>
                  <Text style={styles.laporanUser}>{lap.pelapor?.nama_lengkap}</Text>
                  <Text style={styles.laporanTime}>{lap.created_at}</Text>
                </View>
                <Text style={styles.laporanContent}>{lap.isi_laporan}</Text>

                {lap.file_bukti && lap.file_bukti.length > 0 && (
                  <View style={styles.attachmentBox}>
                    <Text style={styles.attachmentLabel}>Bukti Lampiran:</Text>
                    {lap.file_bukti.map((file) => (
                      <TouchableOpacity
                        key={file.id}
                        style={styles.attachmentRow}
                        onPress={() => handleOpenFile(file.file_url)}
                      >
                        <Text style={styles.attachmentIcon}>📎</Text>
                        <Text style={styles.attachmentName} numberOfLines={1}>{file.file_name}</Text>
                      </TouchableOpacity>
                    ))}
                  </View>
                )}

                {lap.status_tanggapan && (
                  <View style={[styles.tanggapanBox, lap.status_tanggapan === 'approved' ? styles.tanggapanApproved : styles.tanggapanRejected]}>
                    <Text style={styles.tanggapanTitle}>
                      Tanggapan: {lap.status_tanggapan === 'approved' ? 'Disetujui ✅' : 'Ditolak ❌'}
                    </Text>
                    <Text style={styles.tanggapanText}>{lap.tanggapan || 'Tanpa catatan'}</Text>
                  </View>
                )}
              </View>
            ))
          )}
        </View>

        {/* Actions Area */}
        {isActive && (
          <View style={styles.actionsContainer}>
            {isPenerima && (
              <>
                <TouchableOpacity
                  style={[styles.btn, styles.btnPrimary]}
                  onPress={() => navigation.navigate('DisposisiLaporan', { id: disposisi.id })}
                >
                  <Text style={styles.btnText}>Kirim Laporan Pelaksanaan 📝</Text>
                </TouchableOpacity>

                <TouchableOpacity
                  style={[styles.btn, styles.btnSecondary, { marginTop: SPACING.sm }]}
                  onPress={() => navigation.navigate('DisposisiForward', { id: disposisi.id })}
                >
                  <Text style={[styles.btnText, { color: colors.primary }]}>Teruskan Disposisi ➡️</Text>
                </TouchableOpacity>
              </>
            )}

            {isPemberi && (
              <>
                {hasPendingReport && (
                  <TouchableOpacity
                    style={[styles.btn, styles.btnSuccess]}
                    onPress={() => navigation.navigate('DisposisiTanggapan', { id: disposisi.id })}
                  >
                    <Text style={styles.btnText}>Review & Tanggapi Laporan 📋</Text>
                  </TouchableOpacity>
                )}

                <TouchableOpacity
                  style={[styles.btn, styles.btnDanger, { marginTop: hasPendingReport ? SPACING.sm : 0 }]}
                  onPress={handleCancelDisposisi}
                  disabled={cancelling}
                >
                  {cancelling ? (
                    <ActivityIndicator size="small" color={colors.white} />
                  ) : (
                    <Text style={styles.btnText}>Batalkan Disposisi ❌</Text>
                  )}
                </TouchableOpacity>
              </>
            )}
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const getStyles = (colors: ThemeColors) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  loadingContainer: {
    flex: 1,
    backgroundColor: colors.background,
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
    color: colors.danger,
    fontSize: 16,
    fontWeight: '600',
    marginBottom: SPACING.md,
    textAlign: 'center',
  },
  backButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: SPACING.xl,
    paddingVertical: SPACING.md,
    borderRadius: SIZES.radiusSm,
  },
  backButtonText: {
    color: colors.white,
    fontWeight: '700',
  },
  header: {
    backgroundColor: colors.white,
    paddingVertical: SPACING.md,
    paddingHorizontal: SPACING.xl,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
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
    color: colors.primary,
    fontWeight: '700',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: colors.primary,
    flex: 1,
    textAlign: 'center',
  },
  scrollContent: {
    padding: SPACING.xl,
  },
  detailsCard: {
    backgroundColor: colors.white,
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
  instructionText: {
    fontSize: 16,
    fontWeight: '700',
    color: colors.text,
    lineHeight: 22,
    marginBottom: SPACING.xs,
    fontStyle: 'italic',
  },
  dateText: {
    fontSize: 11,
    color: colors.textMuted,
    marginBottom: SPACING.md,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: SPACING.xs,
    borderTopWidth: 1,
    borderTopColor: colors.border,
    marginTop: SPACING.xs,
  },
  infoLabel: {
    fontSize: 13,
    color: colors.textMuted,
    fontWeight: '500',
  },
  infoValue: {
    fontSize: 13,
    color: colors.text,
    fontWeight: '600',
  },
  deadlineOverdue: {
    color: colors.danger,
    fontWeight: '700',
  },
  divider: {
    height: 1,
    backgroundColor: colors.border,
    marginVertical: SPACING.md,
  },
  sectionTitle: {
    fontSize: 13,
    fontWeight: '800',
    color: colors.primary,
    marginBottom: SPACING.md,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  suratCard: {
    backgroundColor: colors.background,
    borderRadius: SIZES.radiusSm,
    padding: SPACING.md,
  },
  suratPerihal: {
    fontSize: 14,
    fontWeight: '700',
    color: colors.text,
    marginBottom: 2,
  },
  suratMeta: {
    fontSize: 12,
    color: colors.textMuted,
    marginBottom: SPACING.sm,
  },
  viewSuratBtn: {
    backgroundColor: 'rgba(37, 87, 167, 0.1)',
    borderRadius: SIZES.radiusSm,
    paddingVertical: 8,
    alignItems: 'center',
  },
  viewSuratBtnText: {
    color: colors.primary,
    fontSize: 12,
    fontWeight: '700',
  },
  emptyText: {
    fontSize: 13,
    color: colors.textMuted,
    fontStyle: 'italic',
  },
  peopleRow: {
    flexDirection: 'row',
    marginBottom: SPACING.md,
  },
  peopleLabel: {
    width: 80,
    fontSize: 13,
    color: colors.textMuted,
    fontWeight: '500',
  },
  peopleValueContainer: {
    flex: 1,
  },
  peopleName: {
    fontSize: 14,
    fontWeight: '700',
    color: colors.text,
  },
  peopleSub: {
    fontSize: 11,
    color: colors.textMuted,
    marginTop: 2,
  },
  penerimaItem: {
    marginBottom: SPACING.sm,
  },
  laporanBox: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: SIZES.radiusSm,
    padding: SPACING.md,
    marginBottom: SPACING.md,
  },
  laporanHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: SPACING.xs,
  },
  laporanUser: {
    fontSize: 13,
    fontWeight: '700',
    color: colors.primary,
  },
  laporanTime: {
    fontSize: 10,
    color: colors.textMuted,
  },
  laporanContent: {
    fontSize: 13,
    color: colors.text,
    lineHeight: 18,
  },
  attachmentBox: {
    marginTop: SPACING.sm,
    borderTopWidth: 1,
    borderTopColor: colors.border,
    paddingTop: SPACING.xs,
  },
  attachmentLabel: {
    fontSize: 11,
    color: colors.textMuted,
    fontWeight: '600',
    marginBottom: 4,
  },
  attachmentRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 4,
  },
  attachmentIcon: {
    fontSize: 14,
    marginRight: 6,
  },
  attachmentName: {
    fontSize: 12,
    color: colors.primary,
    fontWeight: '500',
  },
  tanggapanBox: {
    marginTop: SPACING.sm,
    padding: SPACING.sm,
    borderRadius: SIZES.radiusSm,
    borderLeftWidth: 3,
  },
  tanggapanApproved: {
    backgroundColor: '#f0fdf4',
    borderLeftColor: colors.successLight,
  },
  tanggapanRejected: {
    backgroundColor: '#fdf2f2',
    borderLeftColor: colors.danger,
  },
  tanggapanTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: colors.text,
    marginBottom: 2,
  },
  tanggapanText: {
    fontSize: 12,
    color: colors.textMuted,
  },
  actionsContainer: {
    marginBottom: SPACING.xxl,
  },
  btn: {
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    justifyContent: 'center',
    ...SHADOWS.sm,
  },
  btnPrimary: {
    backgroundColor: colors.primary,
  },
  btnSecondary: {
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: colors.primary,
  },
  btnSuccess: {
    backgroundColor: colors.successLight,
  },
  btnDanger: {
    backgroundColor: colors.danger,
  },
  btnText: {
    color: colors.white,
    fontSize: 14,
    fontWeight: '700',
  },
});
