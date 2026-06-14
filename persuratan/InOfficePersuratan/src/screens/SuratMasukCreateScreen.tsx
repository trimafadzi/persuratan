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
  Modal,
  FlatList,
  Alert,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useNavigation } from '@react-navigation/native';
import { launchImageLibrary, launchCamera } from 'react-native-image-picker';
import DocumentPicker from 'react-native-document-picker';
import { apiClient } from '../api/client';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';

const DRAFT_KEY = '@draft_surat_masuk';

interface UnitKerja {
  id: number;
  nama: string;
}

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

export default function SuratMasukCreateScreen() {
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const navigation = useNavigation();

  // Form states
  const [nomorSurat, setNomorSurat] = useState('');
  const [pengirim, setPengirim] = useState('');
  const [perihal, setPerihal] = useState('');
  const [tanggalSurat, setTanggalSurat] = useState('');
  const [tanggalTerima, setTanggalTerima] = useState('');
  const [sifat, setSifat] = useState('biasa');
  const [ringkasan, setRingkasan] = useState('');
  const [unitKerjaId, setUnitKerjaId] = useState<number | null>(null);
  const [selectedUnitName, setSelectedUnitName] = useState('Pilih Unit Kerja');

  // File state
  const [file, setFile] = useState<SelectedFile | null>(null);

  // Helper lists & modals
  const [unitKerjaList, setUnitKerjaList] = useState<UnitKerja[]>([]);
  const [showUnitModal, setShowUnitModal] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  // Draft states
  const [hasDraft, setHasDraft] = useState(false);

  // Default dates helper
  useEffect(() => {
    const todayStr = new Date().toISOString().split('T')[0];
    setTanggalSurat(todayStr);
    setTanggalTerima(todayStr);

    // Fetch Unit Kerja
    const fetchUnitKerja = async () => {
      try {
        const response = await apiClient.get('/unit-kerja');
        setUnitKerjaList(response.data.data);
      } catch (e) {
        console.error('[CreateSurat] Gagal memuat unit kerja:', e);
      }
    };

    // Check for existing draft
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

    fetchUnitKerja();
    checkDraft();
  }, []);

  // Restore draft from AsyncStorage
  const handleRestoreDraft = async () => {
    try {
      const draftJson = await AsyncStorage.getItem(DRAFT_KEY);
      if (draftJson) {
        const draft = JSON.parse(draftJson);
        if (draft.nomorSurat) setNomorSurat(draft.nomorSurat);
        if (draft.pengirim) setPengirim(draft.pengirim);
        if (draft.perihal) setPerihal(draft.perihal);
        if (draft.tanggalSurat) setTanggalSurat(draft.tanggalSurat);
        if (draft.tanggalTerima) setTanggalTerima(draft.tanggalTerima);
        if (draft.sifat) setSifat(draft.sifat);
        if (draft.ringkasan) setRingkasan(draft.ringkasan);
        if (draft.unitKerjaId !== null && draft.unitKerjaId !== undefined) {
          setUnitKerjaId(draft.unitKerjaId);
          setSelectedUnitName(draft.selectedUnitName || 'Pilih Unit Kerja');
        }
        setHasDraft(false);
        Alert.alert('Dipulihkan', 'Draf berhasil dipulihkan.');
      }
    } catch (e) {
      console.error('[Draft] Gagal memulihkan draf:', e);
    }
  };

  // Dismiss draft banner
  const handleDismissDraft = async () => {
    try {
      await AsyncStorage.removeItem(DRAFT_KEY);
      setHasDraft(false);
    } catch (e) {
      console.error('[Draft] Gagal menghapus draf:', e);
    }
  };

  // Save current form as draft
  const handleSaveDraft = async () => {
    try {
      const draftData = {
        nomorSurat,
        pengirim,
        perihal,
        tanggalSurat,
        tanggalTerima,
        sifat,
        ringkasan,
        unitKerjaId,
        selectedUnitName,
      };
      await AsyncStorage.setItem(DRAFT_KEY, JSON.stringify(draftData));
      setHasDraft(false);
      Alert.alert('Tersimpan', 'Draf surat masuk berhasil disimpan.');
    } catch (e) {
      console.error('[Draft] Gagal menyimpan draf:', e);
      Alert.alert('Error', 'Gagal menyimpan draf.');
    }
  };

  // Clear draft after successful submit
  const clearDraft = async () => {
    try {
      await AsyncStorage.removeItem(DRAFT_KEY);
    } catch (e) {
      console.error('[Draft] Gagal menghapus draf:', e);
    }
  };

  // Pick PDF/Document
  const handlePickDocument = async () => {
    try {
      const doc = await DocumentPicker.pickSingle({
        type: [DocumentPicker.types.pdf, DocumentPicker.types.images],
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

  // Pick Image from Gallery
  const handlePickImage = () => {
    launchImageLibrary(
      {
        mediaType: 'photo',
        quality: 0.8,
      },
      (response) => {
        if (response.didCancel) {
          console.log('[Gallery] User cancelled');
        } else if (response.errorCode) {
          console.error('[Gallery] Error:', response.errorMessage);
        } else if (response.assets && response.assets.length > 0) {
          const asset = response.assets[0];
          setFile({
            uri: asset.uri || '',
            name: asset.fileName || 'image.jpg',
            type: asset.type || 'image/jpeg',
          });
          setErrorMsg('');
        }
      }
    );
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
    if (!nomorSurat.trim() || !pengirim.trim() || !perihal.trim() || !tanggalSurat || !tanggalTerima) {
      setErrorMsg('Semua kolom bertanda * wajib diisi.');
      return;
    }

    // Validasi format tanggal YYYY-MM-DD
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(tanggalSurat) || !dateRegex.test(tanggalTerima)) {
      setErrorMsg('Format tanggal harus YYYY-MM-DD (contoh: 2026-06-14).');
      return;
    }

    setErrorMsg('');
    setIsSubmitting(true);

    try {
      const formData = new FormData();
      formData.append('nomor_surat', nomorSurat.trim());
      formData.append('pengirim', pengirim.trim());
      formData.append('perihal', perihal.trim());
      formData.append('tanggal_surat', tanggalSurat);
      formData.append('tanggal_terima', tanggalTerima);
      formData.append('sifat', sifat);
      if (ringkasan.trim()) formData.append('ringkasan', ringkasan.trim());
      if (unitKerjaId) formData.append('unit_kerja_id', String(unitKerjaId));

      if (file) {
        formData.append('file_scan', {
          uri: file.uri,
          name: file.name,
          type: file.type,
        } as any);
      }

      await apiClient.post('/surat-masuk', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      // Clear draft on success
      await clearDraft();

      Alert.alert('Sukses', 'Surat masuk berhasil ditambahkan.', [
        { text: 'OK', onPress: () => navigation.goBack() },
      ]);
    } catch (e: any) {
      console.error('[CreateSurat] Gagal menyimpan:', e);
      const message = e.response?.data?.message || 'Gagal menyimpan surat ke server.';
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
        <Text style={styles.headerTitle}>Buat Surat Masuk</Text>
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
                  Ada draf surat masuk yang belum dikirim. Pulihkan atau hapus?
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
            {/* Nomor Surat */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Nomor Surat *</Text>
              <TextInput
                style={styles.inputField}
                placeholder="Contoh: 015/UKI-RSU/IV/2026"
                placeholderTextColor={colors.textMuted}
                value={nomorSurat}
                onChangeText={setNomorSurat}
              />
            </View>

            {/* Pengirim */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Pengirim *</Text>
              <TextInput
                style={styles.inputField}
                placeholder="Contoh: Kemenkes RI"
                placeholderTextColor={colors.textMuted}
                value={pengirim}
                onChangeText={setPengirim}
              />
            </View>

            {/* Perihal */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Perihal *</Text>
              <TextInput
                style={styles.inputField}
                placeholder="Contoh: Undangan Rapat Koordinasi Medis"
                placeholderTextColor={colors.textMuted}
                value={perihal}
                onChangeText={setPerihal}
              />
            </View>

            {/* Tanggal Surat */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Tanggal Surat (YYYY-MM-DD) *</Text>
              <TextInput
                style={styles.inputField}
                placeholder="YYYY-MM-DD"
                placeholderTextColor={colors.textMuted}
                value={tanggalSurat}
                onChangeText={setTanggalSurat}
              />
            </View>

            {/* Tanggal Terima */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Tanggal Terima (YYYY-MM-DD) *</Text>
              <TextInput
                style={styles.inputField}
                placeholder="YYYY-MM-DD"
                placeholderTextColor={colors.textMuted}
                value={tanggalTerima}
                onChangeText={setTanggalTerima}
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

            {/* Unit Kerja (Picker) */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Ditujukan Ke Unit</Text>
              <TouchableOpacity
                style={styles.pickerTrigger}
                onPress={() => setShowUnitModal(true)}
              >
                <Text style={[styles.pickerTriggerText, unitKerjaId !== null && styles.pickerTriggerTextActive]}>
                  {selectedUnitName}
                </Text>
                <Text style={styles.pickerArrow}>▼</Text>
              </TouchableOpacity>
            </View>

            {/* Ringkasan */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Ringkasan Isi</Text>
              <TextInput
                style={[styles.inputField, styles.textArea]}
                placeholder="Masukkan ringkasan singkat isi surat (opsional)"
                placeholderTextColor={colors.textMuted}
                multiline
                numberOfLines={3}
                value={ringkasan}
                onChangeText={setRingkasan}
              />
            </View>

            {/* File Attachment Upload */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>File Lampiran Scan (Gambar/PDF)</Text>
              <View style={styles.attachmentButtonRow}>
                <TouchableOpacity style={styles.fileButton} onPress={handlePickDocument}>
                  <Text style={styles.fileButtonText}>📁 PDF/Gambar</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.fileButton} onPress={handlePickImage}>
                  <Text style={styles.fileButtonText}>📸 Galeri</Text>
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

          {/* Submit Button */}
          <TouchableOpacity
            style={styles.saveButton}
            onPress={handleSave}
            disabled={isSubmitting}
            activeOpacity={0.8}
          >
            {isSubmitting ? (
              <ActivityIndicator size="small" color={colors.white} />
            ) : (
              <Text style={styles.saveButtonText}>Simpan Surat</Text>
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

      {/* Unit Kerja Selection Modal */}
      <Modal visible={showUnitModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Pilih Unit Kerja</Text>
              <TouchableOpacity onPress={() => setShowUnitModal(false)}>
                <Text style={styles.modalClose}>Tutup</Text>
              </TouchableOpacity>
            </View>

            <FlatList
              data={[{ id: 0, nama: 'Batal Pilih (Kosongkan)' }, ...unitKerjaList]}
              keyExtractor={(item) => item.id.toString()}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.unitItem}
                  onPress={() => {
                    if (item.id === 0) {
                      setUnitKerjaId(null);
                      setSelectedUnitName('Pilih Unit Kerja');
                    } else {
                      setUnitKerjaId(item.id);
                      setSelectedUnitName(item.nama);
                    }
                    setShowUnitModal(false);
                  }}
                >
                  <Text style={[styles.unitItemText, item.id === 0 && { color: colors.danger, fontWeight: '700' }]}>
                    {item.nama}
                  </Text>
                </TouchableOpacity>
              )}
              ItemSeparatorComponent={() => <View style={styles.unitSeparator} />}
            />
          </View>
        </View>
      </Modal>
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
    height: 80,
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
  pickerTrigger: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: colors.background,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: Platform.OS === 'ios' ? SPACING.md : 10,
  },
  pickerTriggerText: {
    fontSize: 13,
    color: colors.textMuted,
  },
  pickerTriggerTextActive: {
    color: colors.text,
    fontWeight: '600',
  },
  pickerArrow: {
    fontSize: 10,
    color: colors.textMuted,
  },
  attachmentButtonRow: {
    flexDirection: 'row',
    gap: SPACING.sm,
  },
  fileButton: {
    flex: 1,
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
  modalOverlay: {
    flex: 1,
    backgroundColor: colors.overlay,
    justifyContent: 'flex-end',
  },
  modalContainer: {
    backgroundColor: colors.white,
    borderTopLeftRadius: SIZES.radiusLg,
    borderTopRightRadius: SIZES.radiusLg,
    maxHeight: '60%',
    paddingBottom: 24,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: SPACING.xl,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  modalTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: colors.primary,
  },
  modalClose: {
    fontSize: 14,
    color: colors.textMuted,
    fontWeight: '600',
  },
  unitItem: {
    paddingVertical: SPACING.lg,
    paddingHorizontal: SPACING.xl,
  },
  unitItemText: {
    fontSize: 14,
    color: colors.text,
    fontWeight: '500',
  },
  unitSeparator: {
    height: 1,
    backgroundColor: colors.border,
  },
});
