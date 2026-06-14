import React from 'react';
import { StyleSheet, Text, View, TouchableOpacity, SafeAreaView, StatusBar } from 'react-native';
import { useAuthStore } from '../store/authStore';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

export default function DashboardScreen() {
  const { user, logout } = useAuthStore();

  const handleLogout = async () => {
    await logout();
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />
      <View style={styles.header}>
        <Text style={styles.brandTitle}>inOffice</Text>
        <Text style={styles.brandSubtitle}>RSU Universitas Kristen Indonesia</Text>
      </View>

      <View style={styles.content}>
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

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Username:</Text>
            <Text style={styles.infoValue}>{user?.username || '-'}</Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Email:</Text>
            <Text style={styles.infoValue}>{user?.email || '-'}</Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Unit Kerja:</Text>
            <Text style={styles.infoValue}>{user?.unit_kerja?.nama || '-'}</Text>
          </View>
        </View>

        <TouchableOpacity style={styles.logoutButton} onPress={handleLogout} activeOpacity={0.8}>
          <Text style={styles.logoutText}>Keluar</Text>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  header: {
    padding: SPACING.xxl,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    backgroundColor: COLORS.white,
  },
  brandTitle: {
    fontSize: 24,
    fontWeight: '800',
    color: COLORS.primary,
    letterSpacing: 0.5,
  },
  brandSubtitle: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  content: {
    flex: 1,
    padding: SPACING.xl,
    justifyContent: 'center',
    alignItems: 'center',
  },
  card: {
    width: '100%',
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xxl,
    alignItems: 'center',
    ...SHADOWS.md,
    marginBottom: SPACING.xxl,
  },
  avatar: {
    width: 70,
    height: 70,
    borderRadius: 35,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: SPACING.md,
  },
  avatarText: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.white,
  },
  userName: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
  },
  userRole: {
    fontSize: 14,
    color: COLORS.accent,
    fontWeight: '600',
    marginTop: 2,
  },
  divider: {
    width: '100%',
    height: 1,
    backgroundColor: COLORS.border,
    marginVertical: SPACING.xl,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    width: '100%',
    marginBottom: SPACING.sm,
  },
  infoLabel: {
    fontSize: 14,
    color: COLORS.textMuted,
    fontWeight: '500',
  },
  infoValue: {
    fontSize: 14,
    color: COLORS.text,
    fontWeight: '600',
    textAlign: 'right',
    flex: 1,
    marginLeft: SPACING.md,
  },
  logoutButton: {
    width: '100%',
    backgroundColor: COLORS.danger,
    borderRadius: SIZES.radiusMd,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    ...SHADOWS.sm,
  },
  logoutText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.white,
  },
});
