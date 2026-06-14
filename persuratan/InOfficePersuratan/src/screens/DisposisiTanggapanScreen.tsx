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
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

export default function DisposisiTanggapanScreen() {
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
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />

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
            placeholderTextColor={COLORS.textMuted}
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
            <ActivityIndicator size="small" color={COLORS.white} />
          ) : (
            <Text style={styles.submitButtonText}>Kirim Tanggapan 🚀</Text>
          )}
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
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
  formGroup: {
    marginBottom: SPACING.lg,
  },
  label: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.text,
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
    borderColor: COLORS.successLight,
    backgroundColor: COLORS.white,
  },
  btnApproveActive: {
    backgroundColor: COLORS.successLight,
    borderColor: COLORS.successLight,
  },
  btnReject: {
    borderColor: COLORS.danger,
    backgroundColor: COLORS.white,
  },
  btnRejectActive: {
    backgroundColor: COLORS.danger,
    borderColor: COLORS.danger,
  },
  statusBtnText: {
    fontSize: 13,
    fontWeight: '700',
  },
  textApprove: {
    color: COLORS.successLight,
  },
  textReject: {
    color: COLORS.danger,
  },
  textActive: {
    color: COLORS.white,
  },
  textArea: {
    backgroundColor: COLORS.white,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.md,
    fontSize: 14,
    color: COLORS.text,
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
    backgroundColor: COLORS.successLight,
  },
  submitBtnRejected: {
    backgroundColor: COLORS.danger,
  },
  submitButtonText: {
    color: COLORS.white,
    fontSize: 15,
    fontWeight: '700',
  },
});
