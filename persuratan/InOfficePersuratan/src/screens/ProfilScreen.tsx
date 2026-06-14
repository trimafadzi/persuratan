import React from 'react';
import { StyleSheet, Text, View, TouchableOpacity, SafeAreaView, StatusBar, ScrollView, Switch } from 'react-native';
import { useAuthStore } from '../store/authStore';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';

export default function ProfilScreen() {
  const { user, logout } = useAuthStore();
  const { colors, isDark, toggleTheme } = useTheme();
  const styles = getStyles(colors);

  const handleLogout = async () => {
    await logout();
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle={isDark ? 'light-content' : 'dark-content'} backgroundColor={colors.white} />
      
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

        {/* Appearance Section */}
        <View style={styles.detailsSection}>
          <Text style={styles.sectionTitle}>Tampilan</Text>
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Mode Gelap</Text>
            <Switch
              value={isDark}
              onValueChange={toggleTheme}
              trackColor={{ false: colors.border, true: colors.primaryLight }}
              thumbColor={isDark ? colors.primary : '#f4f3f4'}
            />
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
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: colors.primary,
  },
  scrollContent: {
    padding: SPACING.xl,
  },
  card: {
    backgroundColor: colors.white,
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
    backgroundColor: colors.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: SPACING.md,
    borderWidth: 3,
    borderColor: colors.border,
  },
  avatarText: {
    fontSize: 28,
    fontWeight: '700',
    color: colors.white,
  },
  userName: {
    fontSize: 22,
    fontWeight: '800',
    color: colors.text,
  },
  userRole: {
    fontSize: 14,
    color: colors.accent,
    fontWeight: '600',
    marginTop: 4,
  },
  detailsSection: {
    backgroundColor: colors.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.sm,
    marginBottom: SPACING.lg,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: colors.primary,
    marginBottom: SPACING.md,
    borderBottomWidth: 2,
    borderBottomColor: colors.background,
    paddingBottom: SPACING.xs,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: colors.background,
  },
  detailLabel: {
    fontSize: 14,
    color: colors.textMuted,
    fontWeight: '500',
  },
  detailValue: {
    fontSize: 14,
    color: colors.text,
    fontWeight: '600',
    textAlign: 'right',
    flex: 1,
    marginLeft: SPACING.md,
  },
  logoutButton: {
    backgroundColor: colors.danger,
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
    color: colors.white,
  },
  detailRowColumn: {
    paddingVertical: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: colors.background,
  },
  securityHelpText: {
    fontSize: 12,
    color: colors.textMuted,
    lineHeight: 18,
    marginTop: 4,
  },
});
