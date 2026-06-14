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
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';

interface SuratKeluarDetail {
  id: number;
  nomor_surat_otomatis: string;
  tanggal: string;
  penerima: string;
  perihal: string;
  sifat: string;
  sifat_label: string;
  isi: string | null;
  status: string;
  file_url: string | null;
  file_name: string | null;
  created_by: { id: number; nama: string } | null;
}

export default function SuratKeluarDetailScreen() {
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const route = useRoute<any>();
  const navigation = useNavigation();
  const suratId = route.params?.id;

  const [surat, setSurat] = useState<SuratKeluarDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    const fetchDetail = async () => {
      try {
        setLoading(true);
        setErrorMsg('');
        const response = await apiClient.get(`/surat-keluar/${suratId}`);
        setSurat(response.data.data);
      } catch (e) {
        console.error('[SuratKeluarDetail] Gagal memuat detail:', e);
        setErrorMsg('Gagal memuat detail surat.');
      } finally {
        setLoading(false);
      }
    };

    if (suratId) fetchDetail();
  }, [suratId]);

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
      case 'segera': return colors.danger;
      case 'penting': return colors.warningDark;
      case 'rahasia': return '#7c3aed';
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
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
      
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.headerBackBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle} numberOfLines={1}>Detail Surat Keluar</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Main Details Card */}
        <View style={styles.detailsCard}>
          <View style={styles.badgeRow}>
            {/* Status Badge */}
            <View style={[styles.badge, { backgroundColor: '#dcfce7' }]}>
              <Text style={[styles.badgeText, { color: colors.successLight }]}>
                {surat.status ? surat.status.toUpperCase() : 'APPROVED'}
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
          <Text style={styles.nomorText}>No: {surat.nomor_surat_otomatis || 'Draft'}</Text>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Kepada / Penerima</Text>
            <Text style={styles.infoValue}>{surat.penerima}</Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Tanggal Keluar</Text>
            <Text style={styles.infoValue}>{surat.tanggal}</Text>
          </View>

          {surat.created_by ? (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Dibuat Oleh</Text>
              <Text style={styles.infoValue}>{surat.created_by.nama}</Text>
            </View>
          ) : null}

          {surat.isi ? (
            <View style={styles.isiSection}>
              <Text style={styles.isiTitle}>Ringkasan / Isi Surat</Text>
              <Text style={styles.isiText}>{surat.isi}</Text>
            </View>
          ) : null}
        </View>

        {/* Attachment Card */}
        {surat.file_url ? (
          <View style={styles.attachmentCard}>
            <Text style={styles.sectionTitle}>Berkas Lampiran</Text>
            <View style={styles.attachmentBox}>
              <Text style={styles.attachmentIcon}>📄</Text>
              <View style={styles.attachmentInfo}>
                <Text style={styles.attachmentName} numberOfLines={1}>
                  {surat.file_name || 'Scan_Surat_Keluar.pdf'}
                </Text>
                <Text style={styles.attachmentSize}>Ketuk untuk melihat file</Text>
              </View>
              <TouchableOpacity style={styles.openBtn} onPress={handleOpenAttachment}>
                <Text style={styles.openBtnText}>Buka</Text>
              </TouchableOpacity>
            </View>
          </View>
        ) : (
          <View style={styles.noAttachmentBox}>
            <Text style={styles.noAttachmentText}>Tidak ada file lampiran terunggah.</Text>
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
  perihalText: {
    fontSize: 18,
    fontWeight: '800',
    color: colors.text,
    lineHeight: 24,
    marginBottom: SPACING.xs,
  },
  nomorText: {
    fontSize: 12,
    color: colors.textMuted,
    fontWeight: '500',
  },
  divider: {
    height: 1,
    backgroundColor: colors.border,
    marginVertical: SPACING.md,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: SPACING.xs,
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
    flex: 1,
    textAlign: 'right',
    marginLeft: SPACING.lg,
  },
  isiSection: {
    marginTop: SPACING.md,
    backgroundColor: colors.background,
    padding: SPACING.md,
    borderRadius: SIZES.radiusSm,
  },
  isiTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: colors.primary,
    marginBottom: SPACING.xs,
  },
  isiText: {
    fontSize: 13,
    color: colors.text,
    lineHeight: 18,
  },
  attachmentCard: {
    backgroundColor: colors.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
    marginBottom: SPACING.xl,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '800',
    color: colors.primary,
    marginBottom: SPACING.md,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  attachmentBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.background,
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
    color: colors.text,
  },
  attachmentSize: {
    fontSize: 11,
    color: colors.textMuted,
    marginTop: 2,
  },
  openBtn: {
    backgroundColor: colors.primaryLight,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.xs,
    borderRadius: SIZES.radiusSm,
  },
  openBtnText: {
    color: colors.white,
    fontWeight: '700',
    fontSize: 12,
  },
  noAttachmentBox: {
    backgroundColor: colors.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    alignItems: 'center',
    ...SHADOWS.sm,
    marginBottom: SPACING.xl,
  },
  noAttachmentText: {
    fontSize: 13,
    color: colors.textMuted,
    fontWeight: '500',
  },
});
