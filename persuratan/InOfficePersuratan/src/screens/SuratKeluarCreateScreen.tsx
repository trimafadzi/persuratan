import React, { useState } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TextInput,
  TouchableOpacity,
  ScrollView,
  KeyboardAvoidingView,
  Platform,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
  Alert,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import DocumentPicker from 'react-native-document-picker';
import { apiClient } from '../api/client';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

interface SelectedFile {
  uri: string;
  name: string;
  type: string;
}

const SIFAT_OPTIONS = [
  { label: 'Biasa', value: 'biasa' },
  { label: 'Penting', value: 'penting' },
  { label: 'Rahasia', value: 'rahasia' },
  { label: 'Segera', value: 'segera' },
];

export default function SuratKeluarCreateScreen() {
  const navigation = useNavigation();

  // Form states
  const [penerima, setPenerima] = useState('');
  const [perihal, setPerihal] = useState('');
  const [sifat, setSifat] = useState('biasa');
  const [isi, setIsi] = useState('');

  // File state
  const [file, setFile] = useState<SelectedFile | null>(null);

  // Loading & error
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  // Pick Document (.pdf, .doc, .docx)
  const handlePickDocument = async () => {
    try {
      const doc = await DocumentPicker.pickSingle({
        type: [
          DocumentPicker.types.pdf,
          DocumentPicker.types.images,
          'application/msword',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
      });
      
      setFile({
        uri: doc.uri,
        name: doc.name || 'document.pdf',
        type: doc.type || 'application/pdf',
      });
      setErrorMsg('');
    } catch (err) {
      if (DocumentPicker.isCancel(err)) {
        console.log('[Picker] User cancelled picker');
      } else {
        console.error('[Picker] Error:', err);
      }
    }
  };

  const handleSave = async () => {
    if (!penerima.trim() || !perihal.trim() || !sifat) {
      setErrorMsg('Penerima, Perihal, dan Sifat wajib diisi.');
      return;
    }

    setErrorMsg('');
    setIsSubmitting(true);

    try {
      const formData = new FormData();
      formData.append('penerima', penerima.trim());
      formData.append('perihal', perihal.trim());
      formData.append('sifat', sifat);
      if (isi.trim()) formData.append('isi', isi.trim());

      if (file) {
        formData.append('file_surat', {
          uri: file.uri,
          name: file.name,
          type: file.type,
        } as any);
      }

      const response = await apiClient.post('/surat-keluar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      const generatedNum = response.data.data?.nomor_surat_otomatis || '';

      Alert.alert(
        'Sukses', 
        `Surat keluar berhasil dibuat.\nNomor Otomatis: ${generatedNum}`, 
        [
          { text: 'OK', onPress: () => navigation.goBack() },
        ]
      );
    } catch (e: any) {
      console.error('[CreateSuratKeluar] Gagal menyimpan:', e);
      const message = e.response?.data?.message || 'Gagal menyimpan surat keluar ke server.';
      setErrorMsg(message);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />
      
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.headerBackBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Buat Surat Keluar</Text>
        <View style={{ width: 40 }} />
      </View>

      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.keyboardView}
      >
        <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
          {errorMsg ? (
            <View style={styles.errorContainer}>
              <Text style={styles.errorText}>{errorMsg}</Text>
            </View>
          ) : null}

          {/* Form Card */}
          <View style={styles.formCard}>
            {/* Nomor Surat Info */}
            <View style={styles.infoBox}>
              <Text style={styles.infoBoxText}>
                💡 Nomor surat akan di-generate secara otomatis oleh sistem setelah disimpan.
              </Text>
            </View>

            {/* Penerima */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Penerima / Ditujukan Kepada *</Text>
              <TextInput
                style={styles.inputField}
                placeholder="Contoh: Direktur PT Integra / Unit Humas"
                placeholderTextColor={COLORS.textMuted}
                value={penerima}
                onChangeText={setPenerima}
              />
            </View>

            {/* Perihal */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Perihal Surat *</Text>
              <TextInput
                style={styles.inputField}
                placeholder="Contoh: Permohonan Kerja Sama Riset Medis"
                placeholderTextColor={COLORS.textMuted}
                value={perihal}
                onChangeText={setPerihal}
              />
            </View>

            {/* Sifat Surat Selector */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Sifat Surat *</Text>
              <View style={styles.sifatOptionRow}>
                {SIFAT_OPTIONS.map((opt) => {
                  const isSelected = sifat === opt.value;
                  return (
                    <TouchableOpacity
                      key={opt.value}
                      style={[
                        styles.sifatOptButton,
                        isSelected && styles.sifatOptButtonActive,
                        isSelected && opt.value === 'segera' && { backgroundColor: '#fee2e2', borderColor: COLORS.danger },
                        isSelected && opt.value === 'penting' && { backgroundColor: '#fef3c7', borderColor: COLORS.warningDark },
                        isSelected && opt.value === 'rahasia' && { backgroundColor: '#f3e8ff', borderColor: '#7c3aed' },
                      ]}
                      onPress={() => setSifat(opt.value)}
                    >
                      <Text
                        style={[
                          styles.sifatOptText,
                          isSelected && styles.sifatOptTextActive,
                          isSelected && opt.value === 'segera' && { color: COLORS.danger },
                          isSelected && opt.value === 'penting' && { color: COLORS.warningDark },
                          isSelected && opt.value === 'rahasia' && { color: '#7c3aed' },
                        ]}
                      >
                        {opt.label}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </View>
            </View>

            {/* Isi Ringkas */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Isi Ringkas Surat</Text>
              <TextInput
                style={[styles.inputField, styles.textArea]}
                placeholder="Masukkan isi ringkas atau keterangan surat..."
                placeholderTextColor={COLORS.textMuted}
                multiline
                numberOfLines={4}
                value={isi}
                onChangeText={setIsi}
              />
            </View>

            {/* File Upload */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Berkas Lampiran (.pdf, .doc, .docx, image)</Text>
              <TouchableOpacity style={styles.fileButton} onPress={handlePickDocument}>
                <Text style={styles.fileButtonText}>📁 Pilih Berkas Dokumen</Text>
              </TouchableOpacity>

              {file ? (
                <View style={styles.selectedFileBox}>
                  <Text style={styles.selectedFileIcon}>📄</Text>
                  <Text style={styles.selectedFileName} numberOfLines={1}>
                    {file.name}
                  </Text>
                  <TouchableOpacity style={styles.removeFile} onPress={() => setFile(null)}>
                    <Text style={styles.removeFileText}>×</Text>
                  </TouchableOpacity>
                </View>
              ) : null}
            </View>
          </View>

          {/* Submit */}
          <TouchableOpacity
            style={styles.saveButton}
            onPress={handleSave}
            disabled={isSubmitting}
            activeOpacity={0.8}
          >
            {isSubmitting ? (
              <ActivityIndicator size="small" color={COLORS.white} />
            ) : (
              <Text style={styles.saveButtonText}>Simpan & Buat Nomor</Text>
            )}
          </TouchableOpacity>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  keyboardView: {
    flex: 1,
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
    paddingBottom: SPACING.xxxl,
  },
  errorContainer: {
    backgroundColor: '#fff0f0',
    borderWidth: 1,
    borderColor: '#fca5a5',
    borderRadius: SIZES.radiusSm,
    padding: SPACING.md,
    marginBottom: SPACING.md,
  },
  errorText: {
    color: '#991b1b',
    fontWeight: '600',
    fontSize: 12,
  },
  formCard: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
  },
  infoBox: {
    backgroundColor: '#eff6ff',
    borderRadius: SIZES.radiusSm,
    padding: SPACING.md,
    marginBottom: SPACING.lg,
    borderWidth: 1,
    borderColor: '#bfdbfe',
  },
  infoBoxText: {
    color: '#1e40af',
    fontSize: 12,
    fontWeight: '600',
    lineHeight: 18,
  },
  inputGroup: {
    marginBottom: SPACING.md,
  },
  inputLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: SPACING.xs,
  },
  inputField: {
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: Platform.OS === 'ios' ? SPACING.md : 8,
    fontSize: 13,
    color: COLORS.text,
  },
  textArea: {
    textAlignVertical: 'top',
    height: 90,
  },
  sifatOptionRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 4,
  },
  sifatOptButton: {
    flex: 1,
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.sm,
    alignItems: 'center',
  },
  sifatOptButtonActive: {
    backgroundColor: 'rgba(37, 87, 167, 0.1)',
    borderColor: COLORS.primary,
  },
  sifatOptText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  sifatOptTextActive: {
    color: COLORS.primary,
    fontWeight: '700',
  },
  fileButton: {
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderStyle: 'dashed',
    borderColor: COLORS.textMuted,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
  },
  fileButtonText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.text,
  },
  selectedFileBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(37, 87, 167, 0.05)',
    borderColor: COLORS.border,
    borderWidth: 1,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    marginTop: SPACING.sm,
  },
  selectedFileIcon: {
    fontSize: 18,
    marginRight: SPACING.sm,
  },
  selectedFileName: {
    flex: 1,
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.text,
  },
  removeFile: {
    padding: SPACING.xs,
  },
  removeFileText: {
    fontSize: 20,
    color: COLORS.danger,
    fontWeight: '700',
  },
  saveButton: {
    backgroundColor: COLORS.primary,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    marginTop: SPACING.lg,
    ...SHADOWS.sm,
  },
  saveButtonText: {
    color: COLORS.white,
    fontSize: 16,
    fontWeight: '700',
  },
});
