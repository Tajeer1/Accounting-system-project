import React, { useState } from 'react';
import { View, Text, Image, StyleSheet, KeyboardAvoidingView, Platform, Alert, ScrollView } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import Constants from 'expo-constants';
import { useAuth } from '../src/context/AuthContext';
import Input from '../src/components/Input';
import Button from '../src/components/Button';
import StatusPill from '../src/components/StatusPill';
import { colors, radii, spacing, shadows } from '../src/theme/colors';

const API_URL = Constants.expoConfig?.extra?.apiUrl ?? 'not configured';

export default function LoginScreen() {
  const { login } = useAuth();
  const [email, setEmail] = useState('admin@eventplus.com');
  const [password, setPassword] = useState('password');
  const [loading, setLoading] = useState(false);

  async function onSubmit() {
    if (!email || !password) {
      Alert.alert('تنبيه', 'أدخل البريد وكلمة المرور');
      return;
    }
    setLoading(true);
    const res = await login(email, password);
    setLoading(false);
    if (!res.ok) {
      Alert.alert('خطأ', res.message || 'فشل تسجيل الدخول');
    }
  }

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.bg }}>
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
        <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
          <View style={styles.hero}>
            <View style={styles.logo}>
              <Image source={require('../assets/icon.png')} style={styles.logoImage} resizeMode="cover" />
            </View>
            <Text style={styles.brand}>Event Plus</Text>
            <Text style={styles.tagline}>نظام إداري محاسبي</Text>
            <View style={{ marginTop: spacing.md }}>
              <StatusPill label="آمن ومحمي" tone="success" />
            </View>
          </View>

          <View style={styles.card}>
            <Text style={styles.title}>تسجيل الدخول</Text>
            <Text style={styles.subtitle}>أهلاً بك مجددًا 👋</Text>

            <View style={{ marginTop: spacing.xl }}>
              <Input
                label="البريد الإلكتروني"
                value={email}
                onChangeText={setEmail}
                placeholder="you@example.com"
                keyboardType="email-address"
              />
              <Input
                label="كلمة المرور"
                value={password}
                onChangeText={setPassword}
                placeholder="••••••••"
                secureTextEntry
              />
              <View style={{ marginTop: spacing.sm }}>
                <Button title="دخول" onPress={onSubmit} loading={loading} size="lg" />
              </View>
            </View>
          </View>

          <Text style={styles.debugUrl}>API: {API_URL}</Text>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  scroll: { flexGrow: 1, justifyContent: 'center', padding: spacing.xxl },
  hero: { alignItems: 'center', marginBottom: spacing.xxxl },
  logo: {
    width: 96, height: 96, borderRadius: radii.xl,
    backgroundColor: colors.primary,
    alignItems: 'center', justifyContent: 'center',
    marginBottom: spacing.md,
    overflow: 'hidden',
  },
  logoImage: { width: 96, height: 96 },
  brand: { color: colors.text, fontSize: 24, fontWeight: '800', letterSpacing: -0.5 },
  tagline: { color: colors.textMuted, fontSize: 13, marginTop: 4 },

  card: {
    backgroundColor: colors.card,
    borderRadius: radii.xxl,
    padding: spacing.xxl,
    ...shadows.card,
  },
  title: { fontSize: 20, fontWeight: '800', color: colors.text, textAlign: 'right', letterSpacing: -0.3 },
  subtitle: { fontSize: 13, color: colors.textMuted, marginTop: 4, textAlign: 'right' },

  debugUrl: {
    fontSize: 10,
    color: colors.textSubtle,
    marginTop: spacing.lg,
    textAlign: 'center',
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
  },
});
