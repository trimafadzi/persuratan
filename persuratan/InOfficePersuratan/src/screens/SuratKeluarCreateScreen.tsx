import React, { useState, useEffect } from 'react';
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
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useNavigation } from '@react-navigation/native';
import DocumentPicker from 'react-native-document-picker';
import { launchCamera } from 'react-native-image-picker';
import { apiClient } from '../api/client';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';

const DRAFT_KEY = '@draft_surat_keluar';

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
  const { colors } = useTheme();
  const styles = getStyles(colors);

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

  // Draft states
  const [hasDraft, setHasDraft] = useState(false);

  // Check for existing draft on mount
  useEffect(() => {
    const checkDraft = async () => {
      try {
        const draftJson = await AsyncStorage.getItem(DRAFT_KEY);
        if (draftJson) {
          setHasDraft(true);
        }
      } catch (e) {
        console.error('[Draft] Gagal memeriksa draf:', e);
      }
    };
    checkDraft();
  }, []);

  // Restore draft
  const handleRestoreDraft = async () => {
    try {
      const draftJson = await AsyncStorage.getItem(DRAFT_KEY);
      if (draftJson) {
        const draft = JSON.parse(draftJson);
        if (draft.penerima) setPenerima(draft.penerima);
        if (draft.perihal) setPerihal(draft.perihal);
        if (draft.sifat) setSifat(draft.sifat);
        if (draft.isi) setIsi(draft.isi);
        setHasDraft(false);
        Alert.alert('Dipulihkan', 'Draf berhasil dipulihkan.');
      }
    } catch (e) {
      console.error('[Draft] Gagal memulihkan draf:', e);
    }
  };

  // Dismiss draft
  const handleDismissDraft = async () => {
    try {
      await AsyncStorage.removeItem(DRAFT_KEY);
      setHasDraft(false);
    } catch (e) {
      console.error('[Draft] Gagal menghapus draf:', e);
    }
  };

  // Save draft
  const handleSaveDraft = async () => {
    try {
      const draftData = { penerima, perihal, sifat, isi };
      await AsyncStorage.setItem(DRAFT_KEY, JSON.stringify(draftData));
      setHasDraft(false);
      Alert.alert('Tersimpan', 'Draf surat keluar berhasil disimpan.');
    } catch (e) {
      console.error('[Draft] Gagal menyimpan draf:', e);
      Alert.alert('Error', 'Gagal menyimpan draf.');
    }
  };

  // Clear draft
  const clearDraft = async () => {
    try {
      await AsyncStorage.removeItem(DRAFT_KEY);
    } catch (e) {
      console.error('[Draft] Gagal menghapus draf:', e);
    }
  };

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

  // Take Photo with Camera
  const handleTakePhoto = () => {
    launchCamera(
      {
        mediaType: 'photo',
        quality: 0.8,
      },
      (response) => {
        if (response.didCancel) {
          console.log('[Camera] User cancelled');
        } else if (response.errorCode) {
          console.error('[Camera] Error:', response.errorMessage);
        } else if (response.assets && response.assets.length > 0) {
          const asset = response.assets[0];
          setFile({
            uri: asset.uri || '',
            name: asset.fileName || 'photo.jpg',
            type: asset.type || 'image/jpeg',
          });
          setErrorMsg('');
        }
      }
    );
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

      // Clear draft on success
      await clearDraft();

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
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
      
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
          {/* Draft Restore Banner */}
          {hasDraft && (
            <View style={styles.draftBanner}>
              <Text style={styles.draftBannerIcon}>💾</Text>
              <View style={styles.draftBannerContent}>
                <Text style={styles.draftBannerTitle}>Draf Tersedia</Text>
                <Text style={styles.draftBannerText}>
                  Ada draf surat keluar yang belum dikirim. Pulihkan atau hapus?
                </Text>
              </View>
              <View style={styles.draftBannerActions}>
                <TouchableOpacity style={styles.draftRestoreBtn} onPress={handleRestoreDraft}>
                  <Text style={styles.draftRestoreText}>Pulihkan</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.draftDismissBtn} onPress={handleDismissDraft}>
                  <Text style={styles.draftDismissText}>Hapus</Text>
                </TouchableOpacity>
              </View>
            </View>
          )}

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
                placeholderTextColor={colors.textMuted}
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
                placeholderTextColor={colors.textMuted}
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
                        isSelected && opt.value === 'segera' && { backgroundColor: '#fee2e2', borderColor: colors.danger },
                        isSelected && opt.value === 'penting' && { backgroundColor: '#fef3c7', borderColor: colors.warningDark },
                        isSelected && opt.value === 'rahasia' && { backgroundColor: '#f3e8ff', borderColor: '#7c3aed' },
                      ]}
                      onPress={() => setSifat(opt.value)}
                    >
                      <Text
                        style={[
                          styles.sifatOptText,
                          isSelected && styles.sifatOptTextActive,
                          isSelected && opt.value === 'segera' && { color: colors.danger },
                          isSelected && opt.value === 'penting' && { color: colors.warningDark },
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
                placeholderTextColor={colors.textMuted}
                multiline
                numberOfLines={4}
                value={isi}
                onChangeText={setIsi}
              />
            </View>

            {/* File Upload */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Berkas Lampiran (.pdf, .doc, .docx, image)</Text>
              <View style={styles.attachmentButtonRow}>
                <TouchableOpacity style={styles.fileButton} onPress={handlePickDocument}>
                  <Text style={styles.fileButtonText}>📁 Dokumen</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.fileButton} onPress={handleTakePhoto}>
                  <Text style={styles.fileButtonText}>📷 Kamera</Text>
                </TouchableOpacity>
              </View>

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
              <ActivityIndicator size="small" color={colors.white} />
            ) : (
              <Text style={styles.saveButtonText}>Simpan & Buat Nomor</Text>
            )}
          </TouchableOpacity>

          {/* Save Draft Button */}
          <TouchableOpacity
            style={styles.draftButton}
            onPress={handleSaveDraft}
            activeOpacity={0.8}
          >
            <Text style={styles.draftButtonText}>💾 Simpan Draf</Text>
          </TouchableOpacity>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const getStyles = (colors: ThemeColors) => StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.background,
  },
  keyboardView: {
    flex: 1,
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
    backgroundColor: colors.white,
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
    color: colors.text,
    marginBottom: SPACING.xs,
  },
  inputField: {
    backgroundColor: colors.background,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: Platform.OS === 'ios' ? SPACING.md : 8,
    fontSize: 13,
    color: colors.text,
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
    backgroundColor: colors.background,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.sm,
    alignItems: 'center',
  },
  sifatOptButtonActive: {
    backgroundColor: 'rgba(37, 87, 167, 0.1)',
    borderColor: colors.primary,
  },
  sifatOptText: {
    fontSize: 11,
    fontWeight: '600',
    color: colors.textMuted,
  },
  sifatOptTextActive: {
    color: colors.primary,
    fontWeight: '700',
  },
  attachmentButtonRow: {
    flexDirection: 'row',
    gap: SPACING.sm,
  },
  fileButton: {
    backgroundColor: colors.background,
    borderWidth: 1,
    borderStyle: 'dashed',
    borderColor: colors.textMuted,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
  },
  fileButtonText: {
    fontSize: 12,
    fontWeight: '600',
    color: colors.text,
  },
  selectedFileBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(37, 87, 167, 0.05)',
    borderColor: colors.border,
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
    color: colors.text,
  },
  removeFile: {
    padding: SPACING.xs,
  },
  removeFileText: {
    fontSize: 20,
    color: colors.danger,
    fontWeight: '700',
  },
  saveButton: {
    backgroundColor: colors.primary,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    marginTop: SPACING.lg,
    ...SHADOWS.sm,
  },
  saveButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '700',
  },
  draftButton: {
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: colors.primary,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    marginTop: SPACING.sm,
  },
  draftButtonText: {
    color: colors.primary,
    fontSize: 14,
    fontWeight: '700',
  },
  draftBanner: {
    backgroundColor: '#fffbeb',
    borderWidth: 1,
    borderColor: '#fcd34d',
    borderRadius: SIZES.radiusSm,
    padding: SPACING.md,
    marginBottom: SPACING.md,
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
  },
  draftBannerIcon: {
    fontSize: 22,
    marginRight: SPACING.sm,
  },
  draftBannerContent: {
    flex: 1,
    minWidth: 150,
  },
  draftBannerTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#92400e',
    marginBottom: 2,
  },
  draftBannerText: {
    fontSize: 11,
    color: '#a16207',
    lineHeight: 15,
  },
  draftBannerActions: {
    flexDirection: 'row',
    gap: SPACING.sm,
    marginTop: SPACING.sm,
  },
  draftRestoreBtn: {
    backgroundColor: colors.primary,
    paddingHorizontal: SPACING.md,
    paddingVertical: 6,
    borderRadius: SIZES.radiusSm,
  },
  draftRestoreText: {
    color: colors.white,
    fontSize: 11,
    fontWeight: '700',
  },
  draftDismissBtn: {
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: colors.border,
    paddingHorizontal: SPACING.md,
    paddingVertical: 6,
    borderRadius: SIZES.radiusSm,
  },
  draftDismissText: {
    color: colors.textMuted,
    fontSize: 11,
    fontWeight: '600',
  },
});
