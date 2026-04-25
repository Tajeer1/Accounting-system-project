import React, { useCallback, useState } from 'react';
import { View, Text, Image, ScrollView, RefreshControl, StyleSheet, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect, useRouter } from 'expo-router';
import { api, endpoints, apiErrorMessage } from '../../src/api/client';
import { useAuth } from '../../src/context/AuthContext';
import StatusPill from '../../src/components/StatusPill';
import ListCard from '../../src/components/ListCard';
import EmptyState from '../../src/components/EmptyState';
import { colors, radii, spacing, shadows } from '../../src/theme/colors';
import { shortMoney, formatMoney, formatDateShort } from '../../src/utils/format';

export default function DashboardScreen() {
  const { user } = useAuth();
  const router = useRouter();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState(null);

  const load = useCallback(async () => {
    try {
      setError(null);
      const res = await api.get(endpoints.dashboard);
      setData(res.data);
    } catch (err) {
      setError(apiErrorMessage(err));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(useCallback(() => {
    load();
    return undefined;
  }, [load]));

  if (loading) {
    return (
      <SafeAreaView style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: colors.bg }}>
        <ActivityIndicator size="large" color={colors.primary} />
      </SafeAreaView>
    );
  }

  if (error) {
    return (
      <SafeAreaView style={{ flex: 1, justifyContent: 'center', alignItems: 'center', padding: spacing.xl, backgroundColor: colors.bg }}>
        <Text style={{ color: colors.danger, textAlign: 'center' }}>{error}</Text>
      </SafeAreaView>
    );
  }

  const stats = data?.stats || {};

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.bg }} edges={['top']}>
      <View style={styles.topbar}>
        <View style={styles.logoMini}>
          <Image source={require('../../assets/icon.png')} style={styles.logoMiniImg} resizeMode="cover" />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={styles.greeting}>مرحبًا</Text>
          <Text style={styles.name}>{user?.name || 'مستخدم'}</Text>
        </View>
        <View style={styles.avatarCircle}>
          <Text style={styles.avatarText}>{user?.name?.charAt(0) || 'م'}</Text>
        </View>
      </View>

      <ScrollView
        style={{ flex: 1 }}
        contentContainerStyle={{ padding: spacing.lg, paddingBottom: 120 }}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} tintColor={colors.primary} />}
      >
        <View style={styles.balanceCard}>
          <Text style={styles.balanceLabel}>إجمالي الأرصدة</Text>
          <Text style={styles.balanceAmount}>{formatMoney(stats.total_balance || 0)}</Text>
          <View style={styles.balanceStatsRow}>
            <View style={styles.miniStat}>
              <Text style={styles.miniLabel}>مبيعات</Text>
              <Text style={[styles.miniValue, { color: colors.success }]}>{shortMoney(stats.sales_invoices_total)}</Text>
            </View>
            <View style={styles.miniDivider} />
            <View style={styles.miniStat}>
              <Text style={styles.miniLabel}>مشتريات</Text>
              <Text style={[styles.miniValue, { color: colors.danger }]}>{shortMoney(stats.purchase_invoices_total)}</Text>
            </View>
            <View style={styles.miniDivider} />
            <View style={styles.miniStat}>
              <Text style={styles.miniLabel}>مشاريع</Text>
              <Text style={[styles.miniValue, { color: colors.info }]}>{stats.active_projects || 0}</Text>
            </View>
          </View>
        </View>

        <View style={styles.sectionHead}>
          <StatusPill label="الحسابات" tone="success" />
        </View>
        <View style={styles.list}>
          {(data?.bank_accounts || []).length === 0 ? (
            <EmptyState title="لا توجد حسابات" icon="🏦" />
          ) : (
            data.bank_accounts.map((b) => (
              <ListCard
                key={b.id}
                title={b.name}
                topRight="متاح"
                subtitleA={b.type_label}
                subtitleB={b.currency}
                amount={formatMoney(b.current_balance)}
              />
            ))
          )}
        </View>

        {data?.latest_purchases?.length > 0 && (
          <>
            <View style={styles.sectionHead}>
              <StatusPill label="آخر المشتريات" tone="warning" />
            </View>
            <View style={styles.list}>
              {data.latest_purchases.map((p) => (
                <ListCard
                  key={p.id}
                  title={p.supplier_name}
                  topRight={formatDateShort(p.purchase_date)}
                  subtitleA={p.number}
                  subtitleB={p.category || p.project || '—'}
                  amount={formatMoney(p.amount)}
                  amountColor={colors.danger}
                  onPress={() => router.push(`/purchases/${p.id}`)}
                />
              ))}
            </View>
          </>
        )}

        {data?.latest_invoices?.length > 0 && (
          <>
            <View style={styles.sectionHead}>
              <StatusPill label="آخر الفواتير" tone="info" />
            </View>
            <View style={styles.list}>
              {data.latest_invoices.map((inv) => (
                <ListCard
                  key={inv.id}
                  title={inv.party_name}
                  topRight={inv.status_label}
                  subtitleA={inv.number}
                  subtitleB={inv.type_label}
                  amount={formatMoney(inv.amount)}
                  amountColor={inv.type === 'sales' ? colors.success : colors.danger}
                  onPress={() => router.push(`/invoices/${inv.id}`)}
                />
              ))}
            </View>
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  topbar: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    paddingHorizontal: spacing.xl,
    paddingTop: spacing.md,
    paddingBottom: spacing.sm,
  },
  logoMini: {
    width: 38, height: 38, borderRadius: radii.md,
    backgroundColor: colors.primary,
    overflow: 'hidden',
  },
  logoMiniImg: { width: 38, height: 38 },
  greeting: { fontSize: 12, color: colors.textMuted, textAlign: 'right' },
  name: { fontSize: 17, fontWeight: '700', color: colors.text, marginTop: 2, textAlign: 'right' },
  avatarCircle: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: colors.primary,
    alignItems: 'center', justifyContent: 'center',
  },
  avatarText: { color: '#fff', fontSize: 15, fontWeight: '700' },

  balanceCard: {
    backgroundColor: colors.card,
    borderRadius: radii.xxl,
    padding: spacing.xxl,
    ...shadows.card,
    marginBottom: spacing.xl,
  },
  balanceLabel: { fontSize: 13, color: colors.textMuted, textAlign: 'right' },
  balanceAmount: {
    fontSize: 34, fontWeight: '800', color: colors.text,
    marginTop: 8, textAlign: 'right', letterSpacing: -1,
  },
  balanceStatsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: spacing.xl,
    paddingTop: spacing.lg,
    borderTopWidth: 1,
    borderTopColor: colors.borderLight,
  },
  miniStat: { flex: 1, alignItems: 'center' },
  miniLabel: { fontSize: 10, color: colors.textMuted },
  miniValue: { fontSize: 14, fontWeight: '700', marginTop: 4 },
  miniDivider: { width: 1, height: 28, backgroundColor: colors.borderLight },

  sectionHead: { marginTop: spacing.xl, marginBottom: spacing.md, paddingHorizontal: 4 },
  list: { gap: spacing.sm },
});
