import React from 'react';
import { StyleSheet, Text, View, TouchableOpacity, SafeAreaView, StatusBar, ScrollView } from 'react-native';
import { useAuthStore } from '../store/authStore';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

export default function ProfilScreen() {
  const { user, logout } = useAuthStore();

  const handleLogout = async () => {
    await logout();
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />
      
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Profil Saya</Text>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* User Card */}
        <View style={styles.card}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>
              {user?.nama_lengkap ? user.nama_lengkap.substring(0, 2).toUpperCase() : 'US'}
            </Text>
          </View>
          
          <Text style={styles.userName}>{user?.nama_lengkap || user?.name || 'User'}</Text>
          <Text style={styles.userRole}>
            {user?.roles?.[0]?.nama_role || 'Pegawai'}
          </Text>
        </View>

        {/* Account Details */}
        <View style={styles.detailsSection}>
          <Text style={styles.sectionTitle}>Detail Akun</Text>
          
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Username</Text>
            <Text style={styles.detailValue}>{user?.username || '-'}</Text>
          </View>

          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Email</Text>
            <Text style={styles.detailValue}>{user?.email || '-'}</Text>
          </View>

          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Unit Kerja</Text>
            <Text style={styles.detailValue}>{user?.unit_kerja?.nama || '-'}</Text>
          </View>
        </View>

        {/* Security Section */}
        <View style={styles.detailsSection}>
          <Text style={styles.sectionTitle}>Keamanan</Text>
          
          <View style={styles.detailRowColumn}>
            <Text style={styles.detailLabel}>Kata Sandi (Password)</Text>
            <Text style={styles.securityHelpText}>
              Untuk alasan keamanan, perubahan kata sandi dapat dilakukan secara mandiri melalui Portal Web inOffice RSU UKI.
            </Text>
          </View>
        </View>

        {/* App Info Section */}
        <View style={styles.detailsSection}>
          <Text style={styles.sectionTitle}>Tentang Aplikasi</Text>
          
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Nama Aplikasi</Text>
            <Text style={styles.detailValue}>inOffice Persuratan</Text>
          </View>

          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Versi</Text>
            <Text style={styles.detailValue}>1.2 (SaaS)</Text>
          </View>

          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Penyusun</Text>
            <Text style={styles.detailValue}>PT Integra Teknologi Solusi</Text>
          </View>
        </View>

        {/* Action Button */}
        <TouchableOpacity style={styles.logoutButton} onPress={handleLogout} activeOpacity={0.8}>
          <Text style={styles.logoutText}>Keluar dari Aplikasi</Text>
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
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.primary,
  },
  scrollContent: {
    padding: SPACING.xl,
  },
  card: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    alignItems: 'center',
    ...SHADOWS.md,
    marginBottom: SPACING.lg,
  },
  avatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: SPACING.md,
    borderWidth: 3,
    borderColor: COLORS.border,
  },
  avatarText: {
    fontSize: 28,
    fontWeight: '700',
    color: COLORS.white,
  },
  userName: {
    fontSize: 22,
    fontWeight: '800',
    color: COLORS.text,
  },
  userRole: {
    fontSize: 14,
    color: COLORS.accent,
    fontWeight: '600',
    marginTop: 4,
  },
  detailsSection: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
    marginBottom: SPACING.lg,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.primary,
    marginBottom: SPACING.md,
    borderBottomWidth: 2,
    borderBottomColor: COLORS.background,
    paddingBottom: SPACING.xs,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.background,
  },
  detailLabel: {
    fontSize: 14,
    color: COLORS.textMuted,
    fontWeight: '500',
  },
  detailValue: {
    fontSize: 14,
    color: COLORS.text,
    fontWeight: '600',
    textAlign: 'right',
    flex: 1,
    marginLeft: SPACING.md,
  },
  logoutButton: {
    backgroundColor: COLORS.danger,
    borderRadius: SIZES.radiusMd,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    ...SHADOWS.sm,
    marginTop: SPACING.sm,
    marginBottom: SPACING.xl,
  },
  logoutText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.white,
  },
  detailRowColumn: {
    paddingVertical: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.background,
  },
  securityHelpText: {
    fontSize: 12,
    color: COLORS.textMuted,
    lineHeight: 18,
    marginTop: 4,
  },
});
