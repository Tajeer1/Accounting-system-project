import React, { useCallback, useState } from 'react';
import { View, Text, ScrollView, StyleSheet, TouchableOpacity, Alert, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect } from 'expo-router';
import { api, endpoints } from '../../src/api/client';
import { useAuth } from '../../src/context/AuthContext';
import StatusPill from '../../src/components/StatusPill';
import ListCard from '../../src/components/ListCard';
import Button from '../../src/components/Button';
import EmptyState from '../../src/components/EmptyState';
import { colors, radii, spacing, shadows } from '../../src/theme/colors';
import { formatMoney } from '../../src/utils/format';

export default function MoreScreen() {
  const { user, logout } = useAuth();
  const [accounts, setAccounts] = useState([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    try {
      const res = await api.get(endpoints.bankAccounts);
      setAccounts(res.data || []);
    } catch {} finally { setLoading(false); }
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  function confirmLogout() {
    Alert.alert('تسجيل الخروج', 'هل أنت متأكد؟', [
      { text: 'إلغاء', style: 'cancel' },
      { text: 'تسجيل الخروج', style: 'destructive', onPress: logout },
    ]);
  }

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.bg }} edges={['top']}>
      <View style={styles.topbar}>
        <Text style={styles.title}>المزيد</Text>
      </View>

      <ScrollView style={{ flex: 1 }} contentContainerStyle={{ padding: spacing.lg, paddingBottom: 120 }}>
        {/* Profile card */}
        <View style={styles.profileCard}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{user?.name?.charAt(0) || 'م'}</Text>
          </View>
          <View style={{ flex: 1 }}>
            <Text style={styles.profileName}>{user?.name}</Text>
            <Text style={styles.profileEmail}>{user?.email}</Text>
          </View>
        </View>

        {/* Bank accounts */}
        <View style={styles.sectionHead}>
          <StatusPill label="الحسابات البنكية" tone="success" />
        </View>
        <View style={styles.list}>
          {loading ? (
            <ActivityIndicator color={colors.primary} />
          ) : accounts.length === 0 ? (
            <EmptyState title="لا توجد حسابات" icon="🏦" />
          ) : (
            accounts.map((a) => (
              <ListCard
                key={a.id}
                title={a.name}
                topRight={a.is_active ? 'نشط' : 'موقف'}
                subtitleA={a.type_label}
                subtitleB={a.account_number || a.currency}
                amount={formatMoney(a.current_balance)}
              />
            ))
          )}
        </View>

        {/* Menu */}
        <View style={styles.sectionHead}>
          <StatusPill label="القائمة" tone="info" />
        </View>
        <View style={styles.menuCard}>
          <MenuItem icon="🌳" label="شجرة الحسابات" />
          <MenuItem icon="📖" label="القيود اليومية" />
          <MenuItem icon="⚙️" label="الإعدادات" />
          <MenuItem icon="ℹ️" label="حول التطبيق" last />
        </View>

        <View style={{ marginTop: spacing.xl }}>
          <Button title="تسجيل الخروج" variant="secondary" onPress={confirmLogout} />
        </View>

        <Text style={styles.version}>Event Plus · 1.0</Text>
      </ScrollView>
    </SafeAreaView>
  );
}

function MenuItem({ icon, label, last }) {
  return (
    <TouchableOpacity activeOpacity={0.7} style={[styles.menuItem, last && { borderBottomWidth: 0 }]}>
      <Text style={styles.menuIcon}>{icon}</Text>
      <Text style={styles.menuLabel}>{label}</Text>
      <Text style={styles.menuArrow}>‹</Text>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  topbar: { paddingHorizontal: spacing.xl, paddingTop: spacing.md, paddingBottom: spacing.md },
  title: { fontSize: 24, fontWeight: '800', color: colors.text, letterSpacing: -0.5 },

  profileCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.card,
    padding: spacing.lg,
    borderRadius: radii.xl,
    ...shadows.card,
    gap: spacing.md,
  },
  avatar: {
    width: 54, height: 54, borderRadius: 27,
    backgroundColor: colors.primary,
    alignItems: 'center', justifyContent: 'center',
  },
  avatarText: { color: '#fff', fontSize: 20, fontWeight: '700' },
  profileName: { fontSize: 15, fontWeight: '800', color: colors.text, textAlign: 'right', letterSpacing: -0.2 },
  profileEmail: { fontSize: 11, color: colors.textMuted, marginTop: 2, textAlign: 'right' },

  sectionHead: { marginTop: spacing.xl, marginBottom: spacing.md, paddingHorizontal: 4 },
  list: { gap: spacing.sm },

  menuCard: {
    backgroundColor: colors.card,
    borderRadius: radii.xl,
    ...shadows.soft,
    overflow: 'hidden',
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: spacing.lg,
    paddingHorizontal: spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: colors.borderLight,
    gap: spacing.md,
  },
  menuIcon: { fontSize: 18, width: 28, textAlign: 'center' },
  menuLabel: { fontSize: 14, color: colors.text, flex: 1, textAlign: 'right', fontWeight: '500' },
  menuArrow: { fontSize: 20, color: colors.textMuted },

  version: { fontSize: 11, color: colors.textMuted, textAlign: 'center', marginTop: spacing.xl },
});
