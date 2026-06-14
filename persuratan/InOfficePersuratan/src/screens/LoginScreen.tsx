import React, { useState } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TextInput,
  TouchableOpacity,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  SafeAreaView,
  ActivityIndicator,
  StatusBar,
} from 'react-native';
import { useAuthStore } from '../store/authStore';
import { SPACING, SIZES, SHADOWS, ThemeColors } from '../theme/theme';
import { useTheme } from '../theme/ThemeContext';

export default function LoginScreen() {
  const { colors } = useTheme();
  const styles = getStyles(colors);

  const [loginVal, setLoginVal] = useState('');
  const [passwordVal, setPasswordVal] = useState('');
  const [isPasswordVisible, setIsPasswordVisible] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const { login } = useAuthStore();

  const handleLogin = async () => {
    if (!loginVal.trim() || !passwordVal.trim()) {
      setErrorMsg('Username/email dan password wajib diisi.');
      return;
    }

    setErrorMsg('');
    setIsSubmitting(true);

    try {
      const result = await login(loginVal.trim(), passwordVal);
      if (!result.success) {
        setErrorMsg(result.message);
      }
    } catch {
      setErrorMsg('Terjadi kesalahan koneksi internet.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar barStyle="light-content" backgroundColor={colors.primaryDark} />
      
      {/* Background Graphic Header */}
      <View style={styles.headerBackground}>
        <View style={styles.circleGraphic1} />
        <View style={styles.circleGraphic2} />
      </View>

      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.keyboardView}
      >
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          showsVerticalScrollIndicator={false}
          keyboardShouldPersistTaps="handled"
        >
          {/* Logo & Brand Info */}
          <View style={styles.logoSection}>
            <View style={styles.logoBadge}>
              {/* Custom SVG-like envelope icon drawn with styles */}
              <View style={styles.envelopeIcon}>
                <View style={styles.envelopeTop} />
                <View style={styles.envelopeBody} />
              </View>
            </View>
            <Text style={styles.appName}>inOffice</Text>
            <Text style={styles.appTagline}>Sistem Persuratan & Disposisi Digital</Text>
            <Text style={styles.hospitalName}>RSU Universitas Kristen Indonesia</Text>
          </View>

          {/* Form Card */}
          <View style={styles.formCard}>
            <Text style={styles.welcomeText}>Selamat Datang</Text>
            <Text style={styles.subtitleText}>Silakan masuk ke akun Anda</Text>

            {errorMsg ? (
              <View style={styles.errorContainer}>
                <Text style={styles.errorIcon}>⚠️</Text>
                <Text style={styles.errorText}>{errorMsg}</Text>
              </View>
            ) : null}

            {/* Input Username/Email */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Username atau Email</Text>
              <TextInput
                style={styles.inputField}
                placeholder="Masukkan username atau email"
                placeholderTextColor={colors.textMuted}
                autoCapitalize="none"
                autoCorrect={false}
                value={loginVal}
                onChangeText={(text) => {
                  setLoginVal(text);
                  if (errorMsg) setErrorMsg('');
                }}
              />
            </View>

            {/* Input Password */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Password</Text>
              <View style={styles.passwordWrapper}>
                <TextInput
                  style={[styles.inputField, styles.passwordField]}
                  placeholder="Masukkan password"
                  placeholderTextColor={colors.textMuted}
                  secureTextEntry={!isPasswordVisible}
                  autoCapitalize="none"
                  autoCorrect={false}
                  value={passwordVal}
                  onChangeText={(text) => {
                    setPasswordVal(text);
                    if (errorMsg) setErrorMsg('');
                  }}
                />
                <TouchableOpacity
                  style={styles.eyeButton}
                  onPress={() => setIsPasswordVisible(!isPasswordVisible)}
                  activeOpacity={0.6}
                >
                  <Text style={styles.eyeButtonText}>
                    {isPasswordVisible ? 'Sembunyikan' : 'Lihat'}
                  </Text>
                </TouchableOpacity>
              </View>
            </View>

            {/* Login Button */}
            <TouchableOpacity
              style={styles.loginButton}
              onPress={handleLogin}
              disabled={isSubmitting}
              activeOpacity={0.8}
            >
              {isSubmitting ? (
                <ActivityIndicator size="small" color={colors.white} />
              ) : (
                <Text style={styles.loginButtonText}>Masuk</Text>
              )}
            </TouchableOpacity>
          </View>

          <View style={styles.footer}>
            <Text style={styles.footerText}>PT Integra Teknologi Solusi (SEVIMA Group)</Text>
            <Text style={styles.footerVersion}>Versi 1.2 (SaaS)</Text>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const getStyles = (colors: ThemeColors) => StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.primaryDark,
  },
  headerBackground: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: colors.primaryDark,
    overflow: 'hidden',
  },
  circleGraphic1: {
    position: 'absolute',
    top: -100,
    right: -100,
    width: 300,
    height: 300,
    borderRadius: 150,
    backgroundColor: 'rgba(37, 87, 167, 0.25)',
  },
  circleGraphic2: {
    position: 'absolute',
    top: -50,
    left: -150,
    width: 350,
    height: 350,
    borderRadius: 175,
    backgroundColor: 'rgba(230, 57, 70, 0.12)',
  },
  keyboardView: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: SPACING.xl,
    paddingBottom: SPACING.xxl,
    justifyContent: 'center',
  },
  logoSection: {
    alignItems: 'center',
    marginBottom: SPACING.xl,
    marginTop: SPACING.xl,
  },
  logoBadge: {
    width: 64,
    height: 64,
    borderRadius: SIZES.radiusLg,
    backgroundColor: colors.white,
    justifyContent: 'center',
    alignItems: 'center',
    ...SHADOWS.md,
    marginBottom: SPACING.md,
  },
  envelopeIcon: {
    width: 32,
    height: 22,
    borderWidth: 2,
    borderColor: colors.primary,
    borderRadius: 3,
    position: 'relative',
    overflow: 'hidden',
  },
  envelopeTop: {
    position: 'absolute',
    top: -8,
    left: 4,
    width: 20,
    height: 20,
    borderWidth: 2,
    borderColor: colors.primary,
    transform: [{ rotate: '45deg' }],
  },
  envelopeBody: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 2,
    backgroundColor: colors.primary,
  },
  appName: {
    fontSize: 28,
    fontWeight: '800',
    color: colors.white,
    letterSpacing: 0.5,
  },
  appTagline: {
    fontSize: 13,
    color: 'rgba(255, 255, 255, 0.7)',
    fontWeight: '500',
    marginTop: 2,
  },
  hospitalName: {
    fontSize: 11,
    color: colors.accentLight,
    fontWeight: '700',
    letterSpacing: 0.5,
    marginTop: 4,
    textTransform: 'uppercase',
  },
  formCard: {
    backgroundColor: colors.white,
    borderRadius: SIZES.radiusLg,
    padding: SPACING.xl,
    ...SHADOWS.lg,
  },
  welcomeText: {
    fontSize: 22,
    fontWeight: '800',
    color: colors.text,
    textAlign: 'center',
  },
  subtitleText: {
    fontSize: 13,
    color: colors.textMuted,
    textAlign: 'center',
    marginTop: 4,
    marginBottom: SPACING.lg,
  },
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff0f0',
    borderColor: '#fca5a5',
    borderWidth: 1,
    borderRadius: SIZES.radiusSm,
    padding: SPACING.md,
    marginBottom: SPACING.md,
  },
  errorIcon: {
    marginRight: SPACING.sm,
    fontSize: 16,
  },
  errorText: {
    flex: 1,
    color: '#991b1b',
    fontSize: 12,
    fontWeight: '600',
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
    paddingVertical: Platform.OS === 'ios' ? SPACING.md : SPACING.sm,
    fontSize: 14,
    color: colors.text,
  },
  passwordWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    position: 'relative',
  },
  passwordField: {
    flex: 1,
    paddingRight: 100,
  },
  eyeButton: {
    position: 'absolute',
    right: SPACING.md,
    padding: SPACING.xs,
  },
  eyeButtonText: {
    color: colors.primaryLight,
    fontSize: 12,
    fontWeight: '700',
  },
  loginButton: {
    backgroundColor: colors.primary,
    borderRadius: SIZES.radiusSm,
    paddingVertical: SPACING.md,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: SPACING.md,
    ...SHADOWS.sm,
  },
  loginButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '700',
  },
  footer: {
    alignItems: 'center',
    marginTop: SPACING.xl,
  },
  footerText: {
    fontSize: 11,
    color: 'rgba(255, 255, 255, 0.4)',
    fontWeight: '500',
  },
  footerVersion: {
    fontSize: 10,
    color: 'rgba(255, 255, 255, 0.3)',
    marginTop: 2,
  },
});
