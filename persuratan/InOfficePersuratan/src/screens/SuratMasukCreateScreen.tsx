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
import { useNavigation } from '@react-navigation/native';
import { launchImageLibrary } from 'react-native-image-picker';
import DocumentPicker from 'react-native-document-picker';
import { apiClient } from '../api/client';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

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

    fetchUnitKerja();
  }, []);

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
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />
      
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
                placeholderTextColor={COLORS.textMuted}
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
                placeholderTextColor={COLORS.textMuted}
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
                placeholderTextColor={COLORS.textMuted}
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
                placeholderTextColor={COLORS.textMuted}
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
                placeholderTextColor={COLORS.textMuted}
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
                placeholderTextColor={COLORS.textMuted}
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
                  <Text style={styles.fileButtonText}>📁 Pilih PDF/Gambar</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.fileButton} onPress={handlePickImage}>
                  <Text style={styles.fileButtonText}>📸 Pilih dari Galeri</Text>
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
              <ActivityIndicator size="small" color={COLORS.white} />
            ) : (
              <Text style={styles.saveButtonText}>Simpan Surat</Text>
            )}
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
                  <Text style={[styles.unitItemText, item.id === 0 && { color: COLORS.danger, fontWeight: '700' }]}>
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
    height: 80,
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
  pickerTrigger: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: Platform.OS === 'ios' ? SPACING.md : 10,
  },
  pickerTriggerText: {
    fontSize: 13,
    color: COLORS.textMuted,
  },
  pickerTriggerTextActive: {
    color: COLORS.text,
    fontWeight: '600',
  },
  pickerArrow: {
    fontSize: 10,
    color: COLORS.textMuted,
  },
  attachmentButtonRow: {
    flexDirection: 'row',
    gap: SPACING.sm,
  },
  fileButton: {
    flex: 1,
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
  modalOverlay: {
    flex: 1,
    backgroundColor: COLORS.overlay,
    justifyContent: 'flex-end',
  },
  modalContainer: {
    backgroundColor: COLORS.white,
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
    borderBottomColor: COLORS.border,
  },
  modalTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: COLORS.primary,
  },
  modalClose: {
    fontSize: 14,
    color: COLORS.textMuted,
    fontWeight: '600',
  },
  unitItem: {
    paddingVertical: SPACING.lg,
    paddingHorizontal: SPACING.xl,
  },
  unitItemText: {
    fontSize: 14,
    color: COLORS.text,
    fontWeight: '500',
  },
  unitSeparator: {
    height: 1,
    backgroundColor: COLORS.border,
  },
});
