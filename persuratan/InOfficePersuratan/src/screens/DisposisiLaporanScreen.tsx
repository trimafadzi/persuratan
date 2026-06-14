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
import { launchImageLibrary, launchCamera } from 'react-native-image-picker';
import DocumentPicker from 'react-native-document-picker';
import { apiClient } from '../api/client';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';

interface SelectedFile {
  uri: string;
  name: string;
  type: string;
}

export default function DisposisiLaporanScreen() {
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const route = useRoute<any>();
  const navigation = useNavigation();
  const disposisiId = route.params?.id;

  const [isiLaporan, setIsiLaporan] = useState('');
  const [selectedFiles, setSelectedFiles] = useState<SelectedFile[]>([]);
  const [submitting, setSubmitting] = useState(false);

  const handlePickDocument = async () => {
    try {
      const docs = await DocumentPicker.pick({
        type: [DocumentPicker.types.pdf, DocumentPicker.types.images, DocumentPicker.types.doc, DocumentPicker.types.docx],
        allowMultiSelection: true,
      });

      const formatted = docs.map((doc) => ({
        uri: doc.uri,
        name: doc.name || 'attachment.pdf',
        type: doc.type || 'application/pdf',
      }));

      setSelectedFiles((prev) => [...prev, ...formatted]);
    } catch (err) {
      if (DocumentPicker.isCancel(err)) {
        console.log('[Picker] User cancelled picker');
      } else {
        console.error('[Picker] Error:', err);
      }
    }
  };

  const handlePickImage = () => {
    launchImageLibrary(
      {
        mediaType: 'photo',
        quality: 0.8,
        selectionLimit: 5,
      },
      (response) => {
        if (response.didCancel) {
          console.log('[Gallery] User cancelled');
        } else if (response.errorCode) {
          console.error('[Gallery] Error:', response.errorMessage);
        } else if (response.assets) {
          const formatted = response.assets.map((asset) => ({
            uri: asset.uri || '',
            name: asset.fileName || 'image.jpg',
            type: asset.type || 'image/jpeg',
          }));
          setSelectedFiles((prev) => [...prev, ...formatted]);
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
          setSelectedFiles((prev) => [
            ...prev,
            {
              uri: asset.uri || '',
              name: asset.fileName || 'photo.jpg',
              type: asset.type || 'image/jpeg',
            },
          ]);
        }
      }
    );
  };

  const handleRemoveFile = (index: number) => {
    setSelectedFiles((prev) => prev.filter((_, i) => i !== index));
  };

  const handleSubmit = async () => {
    if (!isiLaporan.trim()) {
      Alert.alert('Peringatan', 'Silakan isi uraian laporan pelaksanaan.');
      return;
    }

    try {
      setSubmitting(true);
      const formData = new FormData();
      formData.append('isi_laporan', isiLaporan.trim());

      selectedFiles.forEach((file) => {
        formData.append('file_bukti[]', {
          uri: file.uri,
          name: file.name,
          type: file.type,
        } as any);
      });

      await apiClient.post(`/disposisi/${disposisiId}/laporan`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      Alert.alert('Sukses', 'Laporan pelaksanaan berhasil dikirim.');
      navigation.goBack();
    } catch (error: any) {
      console.error(error);
      const msg = error.response?.data?.message || 'Gagal mengirim laporan.';
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
        <Text style={styles.headerTitle}>Kirim Laporan</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">
        {/* Laporan Input */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Uraian Pelaksanaan Tugas</Text>
          <TextInput
            style={styles.textArea}
            multiline
            numberOfLines={6}
            placeholder="Jelaskan langkah-langkah penyelesaian tugas atau hasil yang telah dicapai..."
            placeholderTextColor={colors.textMuted}
            value={isiLaporan}
            onChangeText={setIsiLaporan}
            textAlignVertical="top"
          />
        </View>

        {/* File Attachments */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Berkas / Bukti Pendukung</Text>
          
          <View style={styles.pickerButtonsRow}>
            <TouchableOpacity style={styles.pickerBtn} onPress={handlePickDocument}>
              <Text style={styles.pickerBtnText}>📄 Berkas</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.pickerBtn} onPress={handlePickImage}>
              <Text style={styles.pickerBtnText}>🖼️ Galeri</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.pickerBtn} onPress={handleTakePhoto}>
              <Text style={styles.pickerBtnText}>📷 Kamera</Text>
            </TouchableOpacity>
          </View>

          {/* Selected Files list */}
          {selectedFiles.length > 0 && (
            <View style={styles.filesList}>
              {selectedFiles.map((file, index) => (
                <View key={index} style={styles.fileRow}>
                  <Text style={styles.fileIcon}>📎</Text>
                  <Text style={styles.fileName} numberOfLines={1}>
                    {file.name}
                  </Text>
                  <TouchableOpacity
                    style={styles.removeFileBtn}
                    onPress={() => handleRemoveFile(index)}
                  >
                    <Text style={styles.removeFileText}>×</Text>
                  </TouchableOpacity>
                </View>
              ))}
            </View>
          )}
        </View>

        {/* Submit Button */}
        <TouchableOpacity
          style={styles.submitButton}
          onPress={handleSubmit}
          disabled={submitting}
        >
          {submitting ? (
            <ActivityIndicator size="small" color={colors.white} />
          ) : (
            <Text style={styles.submitButtonText}>Kirim Laporan Pelaksanaan 🚀</Text>
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
  textArea: {
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.md,
    fontSize: 14,
    color: colors.text,
    minHeight: 150,
    ...SHADOWS.sm,
  },
  pickerButtonsRow: {
    flexDirection: 'row',
    gap: SPACING.md,
    marginBottom: SPACING.md,
  },
  pickerBtn: {
    flex: 1,
    backgroundColor: 'rgba(37, 87, 167, 0.1)',
    borderRadius: SIZES.radiusSm,
    paddingVertical: 12,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.primary,
  },
  pickerBtnText: {
    color: colors.primary,
    fontSize: 13,
    fontWeight: '700',
  },
  filesList: {
    backgroundColor: colors.white,
    borderRadius: SIZES.radiusSm,
    padding: SPACING.sm,
    borderWidth: 1,
    borderColor: colors.border,
    ...SHADOWS.sm,
  },
  fileRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: SPACING.xs,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  fileIcon: {
    fontSize: 16,
    marginRight: SPACING.sm,
  },
  fileName: {
    fontSize: 13,
    color: colors.text,
    flex: 1,
  },
  removeFileBtn: {
    padding: SPACING.xs,
  },
  removeFileText: {
    fontSize: 20,
    color: colors.danger,
    fontWeight: '700',
  },
  submitButton: {
    backgroundColor: colors.primary,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: SPACING.xl,
    marginBottom: SPACING.xxl,
    ...SHADOWS.sm,
  },
  submitButtonText: {
    color: colors.white,
    fontSize: 15,
    fontWeight: '700',
  },
});
