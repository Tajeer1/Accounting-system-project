import React, { useEffect } from 'react';
import { View, ActivityIndicator, I18nManager } from 'react-native';
import { Stack, useRouter, useSegments } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { AuthProvider, useAuth } from '../src/context/AuthContext';
import { colors } from '../src/theme/colors';

try { I18nManager.allowRTL(true); } catch {}

function RootNav() {
  const { isAuthenticated, loading } = useAuth();
  const segments = useSegments();
  const router = useRouter();

  useEffect(() => {
    if (loading) return;
    const inAuth = segments[0] === 'login';
    if (!isAuthenticated && !inAuth) {
      router.replace('/login');
    } else if (isAuthenticated && inAuth) {
      router.replace('/(tabs)');
    }
  }, [isAuthenticated, loading, segments]);

  if (loading) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: colors.bg }}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  const headerStyle = {
    headerStyle: { backgroundColor: colors.bg },
    headerShadowVisible: false,
    headerTitleStyle: { fontWeight: '700', fontSize: 16, color: colors.text },
    headerTintColor: colors.text,
  };

  return (
    <Stack screenOptions={{ headerShown: false, contentStyle: { backgroundColor: colors.bg } }}>
      <Stack.Screen name="login" />
      <Stack.Screen name="(tabs)" />
      <Stack.Screen name="purchases/[id]" options={{ headerShown: true, title: 'تفاصيل العملية', presentation: 'card', ...headerStyle }} />
      <Stack.Screen name="purchases/new" options={{ headerShown: true, title: 'عملية شراء جديدة', presentation: 'modal', ...headerStyle }} />
      <Stack.Screen name="invoices/[id]" options={{ headerShown: true, title: 'تفاصيل الفاتورة', presentation: 'card', ...headerStyle }} />
      <Stack.Screen name="invoices/new" options={{ headerShown: true, title: 'فاتورة جديدة', presentation: 'modal', ...headerStyle }} />
    </Stack>
  );
}

export default function RootLayout() {
  return (
    <SafeAreaProvider>
      <AuthProvider>
        <StatusBar style="dark" />
        <RootNav />
      </AuthProvider>
    </SafeAreaProvider>
  );
}
