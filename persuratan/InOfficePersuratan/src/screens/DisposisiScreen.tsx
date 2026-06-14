import React from 'react';
import { StyleSheet, Text, View, SafeAreaView, StatusBar } from 'react-native';
import { COLORS, SPACING, SIZES, SHADOWS } from '../theme/theme';

export default function DisposisiScreen() {
  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.white} />
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Disposisi</Text>
      </View>
      <View style={styles.content}>
        <View style={styles.card}>
          <Text style={styles.icon}>📋</Text>
          <Text style={styles.cardTitle}>Modul Disposisi</Text>
          <Text style={styles.cardText}>
            Fitur pelacakan, disposisi berjenjang, dan laporan pelaksanaan akan tersedia pada Fase 3B berikutnya.
          </Text>
        </View>
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
  content: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: SPACING.xl,
  },
  card: {
    backgroundColor: COLORS.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xxl,
    alignItems: 'center',
    textAlign: 'center',
    ...SHADOWS.md,
    width: '100%',
  },
  icon: {
    fontSize: 48,
    marginBottom: SPACING.md,
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: COLORS.primary,
    marginBottom: SPACING.xs,
  },
  cardText: {
    fontSize: 14,
    color: COLORS.textMuted,
    textAlign: 'center',
    lineHeight: 20,
    marginTop: SPACING.sm,
  },
});
