import React, { useState } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
  ScrollView,
  Alert,
} from 'react-native';
import { useRoute, useNavigation } from '@react-navigation/native';
import { apiClient } from '../api/client';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';

export default function DisposisiTanggapanScreen() {
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const route = useRoute<any>();
  const navigation = useNavigation();
  const disposisiId = route.params?.id;

  const [tanggapan, setTanggapan] = useState('');
  const [statusTanggapan, setStatusTanggapan] = useState<'approved' | 'rejected'>('approved');
  const [submitting, setSubmitting] = useState(false);

  const handleSubmit = async () => {
    if (!tanggapan.trim()) {
      Alert.alert('Peringatan', 'Silakan isi catatan tanggapan.');
      return;
    }

    try {
      setSubmitting(true);
      const payload = {
        tanggapan: tanggapan.trim(),
        status_tanggapan: statusTanggapan,
      };

      await apiClient.post(`/disposisi/${disposisiId}/tanggapi`, payload);
      Alert.alert('Sukses', `Laporan berhasil ditanggapi (${statusTanggapan === 'approved' ? 'Disetujui' : 'Ditolak'}).`);
      navigation.goBack();
    } catch (error: any) {
      console.error(error);
      const msg = error.response?.data?.message || 'Gagal menyimpan tanggapan.';
      Alert.alert('Error', msg);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.headerBackBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Review Laporan</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">
        {/* Status Selection */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Tindakan Evaluasi</Text>
          <View style={styles.statusButtonsRow}>
            <TouchableOpacity
              style={[
                styles.statusBtn,
                styles.btnApprove,
                statusTanggapan === 'approved' && styles.btnApproveActive,
              ]}
              onPress={() => setStatusTanggapan('approved')}
            >
              <Text
                style={[
                  styles.statusBtnText,
                  styles.textApprove,
                  statusTanggapan === 'approved' && styles.textActive,
                ]}
              >
                Disetujui (Approved) ✅
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={[
                styles.statusBtn,
                styles.btnReject,
                statusTanggapan === 'rejected' && styles.btnRejectActive,
              ]}
              onPress={() => setStatusTanggapan('rejected')}
            >
              <Text
                style={[
                  styles.statusBtnText,
                  styles.textReject,
                  statusTanggapan === 'rejected' && styles.textActive,
                ]}
              >
                Ditolak (Rejected) ❌
              </Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Tanggapan Notes */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Catatan Evaluasi / Komentar</Text>
          <TextInput
            style={styles.textArea}
            multiline
            numberOfLines={5}
            placeholder="Masukkan catatan evaluasi atau alasan persetujuan/penolakan..."
            placeholderTextColor={colors.textMuted}
            value={tanggapan}
            onChangeText={setTanggapan}
            textAlignVertical="top"
          />
        </View>

        {/* Submit Button */}
        <TouchableOpacity
          style={[
            styles.submitButton,
            statusTanggapan === 'approved' ? styles.submitBtnApproved : styles.submitBtnRejected,
          ]}
          onPress={handleSubmit}
          disabled={submitting}
        >
          {submitting ? (
            <ActivityIndicator size="small" color={colors.white} />
          ) : (
            <Text style={styles.submitButtonText}>Kirim Tanggapan 🚀</Text>
          )}
        </TouchableOpacity>
      </ScrollView>
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
  formGroup: {
    marginBottom: SPACING.lg,
  },
  label: {
    fontSize: 13,
    fontWeight: '700',
    color: colors.text,
    marginBottom: SPACING.xs,
  },
  statusButtonsRow: {
    flexDirection: 'row',
    gap: SPACING.md,
  },
  statusBtn: {
    flex: 1,
    borderRadius: SIZES.radiusSm,
    paddingVertical: 14,
    alignItems: 'center',
    borderWidth: 1.5,
    ...SHADOWS.sm,
  },
  btnApprove: {
    borderColor: colors.successLight,
    backgroundColor: colors.white,
  },
  btnApproveActive: {
    backgroundColor: colors.successLight,
    borderColor: colors.successLight,
  },
  btnReject: {
    borderColor: colors.danger,
    backgroundColor: colors.white,
  },
  btnRejectActive: {
    backgroundColor: colors.danger,
    borderColor: colors.danger,
  },
  statusBtnText: {
    fontSize: 13,
    fontWeight: '700',
  },
  textApprove: {
    color: colors.successLight,
  },
  textReject: {
    color: colors.danger,
  },
  textActive: {
    color: colors.white,
  },
  textArea: {
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.md,
    fontSize: 14,
    color: colors.text,
    minHeight: 120,
    ...SHADOWS.sm,
  },
  submitButton: {
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: SPACING.xl,
    marginBottom: SPACING.xxl,
    ...SHADOWS.sm,
  },
  submitBtnApproved: {
    backgroundColor: colors.successLight,
  },
  submitBtnRejected: {
    backgroundColor: colors.danger,
  },
  submitButtonText: {
    color: colors.white,
    fontSize: 15,
    fontWeight: '700',
  },
});
