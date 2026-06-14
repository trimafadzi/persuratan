import React, { useState, useEffect } from 'react';
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
  Modal,
  FlatList,
  Alert,
} from 'react-native';
import { useRoute, useNavigation } from '@react-navigation/native';
import { apiClient } from '../api/client';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

interface UserItem {
  id: number;
  nama_lengkap: string;
  jabatan: string;
  initials: string;
}

export default function DisposisiForwardScreen() {
  const route = useRoute<any>();
  const navigation = useNavigation();
  const parentId = route.params?.id;

  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  // Parent info state
  const [parentPerihal, setParentPerihal] = useState('');

  // Form states
  const [isiDisposisi, setIsiDisposisi] = useState('');
  const [selectedPenerima, setSelectedPenerima] = useState<UserItem[]>([]);
  const [tanggalDeadline, setTanggalDeadline] = useState('');

  // Picker data states
  const [userList, setUserList] = useState<UserItem[]>([]);

  // Modal visibilities
  const [penerimaModalVisible, setPenerimaModalVisible] = useState(false);
  const [calendarModalVisible, setCalendarModalVisible] = useState(false);

  // Search state
  const [userSearch, setUserSearch] = useState('');

  // Calendar states
  const [currentYear, setCurrentYear] = useState(new Date().getFullYear());
  const [currentMonth, setCurrentMonth] = useState(new Date().getMonth());

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        // Load parent disposisi to show what we are forwarding
        const parentRes = await apiClient.get(`/disposisi/${parentId}`);
        setParentPerihal(parentRes.data.data.surat_masuk?.perihal || 'Surat Rujukan');

        // Load users list
        const userRes = await apiClient.get('/users');
        setUserList(userRes.data.data);
      } catch (e) {
        console.error(e);
        Alert.alert('Error', 'Gagal memuat data.');
      } finally {
        setLoading(false);
      }
    };

    if (parentId) loadData();
  }, [parentId]);

  const handleSubmit = async () => {
    if (!isiDisposisi.trim()) {
      Alert.alert('Peringatan', 'Silakan isi instruksi penerusan.');
      return;
    }
    if (selectedPenerima.length === 0) {
      Alert.alert('Peringatan', 'Silakan pilih minimal 1 penerima.');
      return;
    }

    try {
      setSubmitting(true);
      const payload = {
        isi_disposisi: isiDisposisi,
        penerima_ids: selectedPenerima.map((p) => p.id),
        tanggal_deadline: tanggalDeadline || null,
      };

      await apiClient.post(`/disposisi/${parentId}/teruskan`, payload);
      Alert.alert('Sukses', 'Disposisi berhasil diteruskan.');
      navigation.goBack();
    } catch (error: any) {
      console.error(error);
      const msg = error.response?.data?.message || 'Gagal meneruskan disposisi.';
      Alert.alert('Error', msg);
    } finally {
      setSubmitting(false);
    }
  };

  const handleTogglePenerima = (user: UserItem) => {
    if (selectedPenerima.some((p) => p.id === user.id)) {
      setSelectedPenerima(selectedPenerima.filter((p) => p.id !== user.id));
    } else {
      setSelectedPenerima([...selectedPenerima, user]);
    }
  };

  // Custom Calendar logic
  const getDaysInMonth = (year: number, month: number) => {
    return new Date(year, month + 1, 0).getDate();
  };

  const getFirstDayOfMonth = (year: number, month: number) => {
    return new Date(year, month, 1).getDay();
  };

  const renderCalendar = () => {
    const daysInMonth = getDaysInMonth(currentYear, currentMonth);
    const firstDay = getFirstDayOfMonth(currentYear, currentMonth);

    const monthNames = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    const days = [];
    for (let i = 0; i < firstDay; i++) {
      days.push(<View key={`empty-${i}`} style={styles.calendarDayEmpty} />);
    }

    for (let day = 1; day <= daysInMonth; day++) {
      const dateString = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      const isSelected = tanggalDeadline === dateString;

      days.push(
        <TouchableOpacity
          key={`day-${day}`}
          style={[styles.calendarDay, isSelected && styles.calendarDaySelected]}
          onPress={() => {
            setTanggalDeadline(dateString);
            setCalendarModalVisible(false);
          }}
        >
          <Text style={[styles.calendarDayText, isSelected && styles.calendarDayTextSelected]}>
            {day}
          </Text>
        </TouchableOpacity>
      );
    }

    return (
      <View style={styles.calendarWrapper}>
        <View style={styles.calendarHeader}>
          <TouchableOpacity
            onPress={() => {
              if (currentMonth === 0) {
                setCurrentMonth(11);
                setCurrentYear(currentYear - 1);
              } else {
                setCurrentMonth(currentMonth - 1);
              }
            }}
          >
            <Text style={styles.calendarNavText}>◀</Text>
          </TouchableOpacity>
          <Text style={styles.calendarTitle}>
            {monthNames[currentMonth]} {currentYear}
          </Text>
          <TouchableOpacity
            onPress={() => {
              if (currentMonth === 11) {
                setCurrentMonth(0);
                setCurrentYear(currentYear + 1);
              } else {
                setCurrentMonth(currentMonth + 1);
              }
            }}
          >
            <Text style={styles.calendarNavText}>▶</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.calendarWeekdays}>
          {['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'].map((d) => (
            <Text key={d} style={styles.weekdayText}>
              {d}
            </Text>
          ))}
        </View>

        <View style={styles.calendarGrid}>{days}</View>
      </View>
    );
  };

  const filteredUsers = userList.filter((u) =>
    u.nama_lengkap.toLowerCase().includes(userSearch.toLowerCase())
  );

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
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
        <Text style={styles.headerTitle}>Teruskan Disposisi</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">
        {/* Surat Masuk Info */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Dokumen Rujukan</Text>
          <View style={styles.readOnlyInput}>
            <Text style={styles.readOnlyText}>{parentPerihal}</Text>
          </View>
        </View>

        {/* Penerima Disposisi Picker */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Peneruskan Ke (Multi-select)</Text>
          <TouchableOpacity
            style={styles.pickerTrigger}
            onPress={() => setPenerimaModalVisible(true)}
          >
            <Text
              style={[
                styles.pickerTriggerText,
                selectedPenerima.length > 0 && styles.pickerTriggerTextActive,
              ]}
              numberOfLines={1}
            >
              {selectedPenerima.length > 0
                ? selectedPenerima.map((p) => p.nama_lengkap).join(', ')
                : 'Pilih Staf Penerima'}
            </Text>
            <Text style={styles.pickerArrow}>▼</Text>
          </TouchableOpacity>
        </View>

        {/* Isi Disposisi Input */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Instruksi Disposisi Baru</Text>
          <TextInput
            style={styles.textArea}
            multiline
            numberOfLines={4}
            placeholder="Masukkan instruksi pendelegasian baru..."
            placeholderTextColor={COLORS.textMuted}
            value={isiDisposisi}
            onChangeText={setIsiDisposisi}
            textAlignVertical="top"
          />
        </View>

        {/* Tanggal Deadline Picker */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Tenggat Waktu Baru (Opsional)</Text>
          <TouchableOpacity
            style={styles.pickerTrigger}
            onPress={() => setCalendarModalVisible(true)}
          >
            <Text style={[styles.pickerTriggerText, tanggalDeadline && styles.pickerTriggerTextActive]}>
              {tanggalDeadline || 'Pilih Tanggal Tenggat'}
            </Text>
            <Text style={styles.pickerArrow}>📅</Text>
          </TouchableOpacity>
        </View>

        {/* Submit Button */}
        <TouchableOpacity
          style={styles.submitButton}
          onPress={handleSubmit}
          disabled={submitting}
        >
          {submitting ? (
            <ActivityIndicator size="small" color={COLORS.white} />
          ) : (
            <Text style={styles.submitButtonText}>Teruskan Disposisi ➡️</Text>
          )}
        </TouchableOpacity>
      </ScrollView>

      {/* Penerima Selection Modal */}
      <Modal visible={penerimaModalVisible} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Pilih Penerima Disposisi</Text>
              <TouchableOpacity onPress={() => setPenerimaModalVisible(false)}>
                <Text style={styles.closeModalText}>Selesai</Text>
              </TouchableOpacity>
            </View>

            <TextInput
              style={styles.modalSearchInput}
              placeholder="Cari nama staf..."
              placeholderTextColor={COLORS.textMuted}
              value={userSearch}
              onChangeText={setUserSearch}
            />

            <FlatList
              data={filteredUsers}
              keyExtractor={(item) => item.id.toString()}
              renderItem={({ item }) => {
                const isSelected = selectedPenerima.some((p) => p.id === item.id);
                return (
                  <TouchableOpacity
                    style={[styles.listItem, isSelected && styles.listItemChecked]}
                    onPress={() => handleTogglePenerima(item)}
                  >
                    <View style={styles.userListItemContent}>
                      <Text style={[styles.listItemTitle, isSelected && styles.listItemCheckedText]}>
                        {item.nama_lengkap}
                      </Text>
                      <Text style={styles.listItemSubtitle}>{item.jabatan}</Text>
                    </View>
                    <Text style={styles.checkedIcon}>{isSelected ? '✅' : '⬜'}</Text>
                  </TouchableOpacity>
                );
              }}
              ListEmptyComponent={
                <Text style={styles.emptyModalText}>Tidak ada staf yang cocok.</Text>
              }
            />
          </View>
        </View>
      </Modal>

      {/* Calendar Modal */}
      <Modal visible={calendarModalVisible} animationType="fade" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContainer, { maxHeight: 400, justifyContent: 'center' }]}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Pilih Deadline Baru</Text>
              <TouchableOpacity onPress={() => setCalendarModalVisible(false)}>
                <Text style={styles.closeModalText}>Batal</Text>
              </TouchableOpacity>
            </View>
            {renderCalendar()}
          </View>
        </View>
      </Modal>
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
    justifyContent: 'center',
    alignItems: 'center',
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
  pickerTrigger: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: COLORS.white,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.md,
    ...SHADOWS.sm,
  },
  pickerTriggerText: {
    fontSize: 14,
    color: COLORS.textMuted,
    flex: 1,
    paddingRight: SPACING.sm,
  },
  pickerTriggerTextActive: {
    color: COLORS.text,
    fontWeight: '600',
  },
  pickerArrow: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  readOnlyInput: {
    backgroundColor: COLORS.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.md,
  },
  readOnlyText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
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
    minHeight: 100,
    ...SHADOWS.sm,
  },
  submitButton: {
    backgroundColor: COLORS.primary,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: SPACING.xl,
    marginBottom: SPACING.xxl,
    ...SHADOWS.sm,
  },
  submitButtonText: {
    color: COLORS.white,
    fontSize: 15,
    fontWeight: '700',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  modalContainer: {
    backgroundColor: COLORS.white,
    borderTopLeftRadius: SIZES.radiusLg,
    borderTopRightRadius: SIZES.radiusLg,
    maxHeight: '80%',
    padding: SPACING.xl,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.md,
    paddingBottom: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  modalTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.primary,
  },
  closeModalText: {
    color: COLORS.primaryLight,
    fontWeight: '700',
    fontSize: 14,
  },
  modalSearchInput: {
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: SIZES.radiusSm,
    paddingHorizontal: SPACING.md,
    paddingVertical: 8,
    fontSize: 13,
    color: COLORS.text,
    marginBottom: SPACING.md,
  },
  listItem: {
    paddingVertical: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  listItemChecked: {
    backgroundColor: 'rgba(37, 87, 167, 0.05)',
  },
  listItemTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  listItemCheckedText: {
    color: COLORS.primary,
  },
  listItemSubtitle: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  userListItemContent: {
    flex: 1,
    paddingRight: SPACING.md,
  },
  checkedIcon: {
    fontSize: 18,
  },
  emptyModalText: {
    textAlign: 'center',
    color: COLORS.textMuted,
    paddingVertical: SPACING.xl,
    fontStyle: 'italic',
  },
  calendarWrapper: {
    paddingBottom: SPACING.md,
  },
  calendarHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.md,
  },
  calendarTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
  },
  calendarNavText: {
    fontSize: 16,
    color: COLORS.primary,
    paddingHorizontal: SPACING.md,
  },
  calendarWeekdays: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: SPACING.sm,
  },
  weekdayText: {
    width: '14.2%',
    textAlign: 'center',
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  calendarGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
  },
  calendarDay: {
    width: '14.2%',
    height: 36,
    justifyContent: 'center',
    alignItems: 'center',
    marginVertical: 2,
    borderRadius: SIZES.radiusSm,
  },
  calendarDayEmpty: {
    width: '14.2%',
    height: 36,
  },
  calendarDaySelected: {
    backgroundColor: COLORS.primary,
  },
  calendarDayText: {
    fontSize: 13,
    color: COLORS.text,
    fontWeight: '500',
  },
  calendarDayTextSelected: {
    color: COLORS.white,
    fontWeight: '700',
  },
});
