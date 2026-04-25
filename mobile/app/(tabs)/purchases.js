import React, { useCallback, useMemo, useState } from 'react';
import { View, Text, ScrollView, TouchableOpacity, StyleSheet, ActivityIndicator, RefreshControl, TextInput } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter, useFocusEffect } from 'expo-router';
import { api, endpoints, apiErrorMessage } from '../../src/api/client';
import EmptyState from '../../src/components/EmptyState';
import StatusPill from '../../src/components/StatusPill';
import ListCard from '../../src/components/ListCard';
import { colors, radii, spacing } from '../../src/theme/colors';
import { formatMoney, formatDateShort } from '../../src/utils/format';

const STATUS_TONE = { paid: 'success', pending: 'warning', cancelled: 'neutral' };
const STATUS_LABEL = { paid: 'مدفوعة', pending: 'معلقة', cancelled: 'ملغاة' };
const GROUP_ORDER = ['paid', 'pending', 'cancelled'];

export default function PurchasesScreen() {
  const router = useRouter();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');

  const load = useCallback(async (q = '') => {
    try {
      const res = await api.get(endpoints.purchases, { params: q ? { q } : {} });
      setItems(res.data?.data || []);
    } catch (err) {
      console.log('purchases error', apiErrorMessage(err));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { load(search); }, [load, search]));

  const grouped = useMemo(() => {
    const groups = {};
    items.forEach((i) => {
      if (!groups[i.status]) groups[i.status] = [];
      groups[i.status].push(i);
    });
    return groups;
  }, [items]);

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.bg }} edges={['top']}>
      <View style={styles.topbar}>
        <Text style={styles.title}>المشتريات</Text>
        <TouchableOpacity onPress={() => router.push('/purchases/new')} style={styles.addBtn}>
          <Text style={styles.addBtnText}>+</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.searchWrap}>
        <TextInput
          value={search}
          onChangeText={setSearch}
          placeholder="بحث عن مورد..."
          placeholderTextColor={colors.textSubtle}
          style={styles.search}
          textAlign="right"
          returnKeyType="search"
          onSubmitEditing={() => load(search)}
        />
      </View>

      {loading ? (
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
          <ActivityIndicator color={colors.primary} />
        </View>
      ) : (
        <ScrollView
          style={{ flex: 1 }}
          contentContainerStyle={{ padding: spacing.lg, paddingBottom: 120 }}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(search); }} tintColor={colors.primary} />}
        >
          {items.length === 0 ? (
            <EmptyState title="لا توجد مشتريات" subtitle="أضف أول عملية" icon="🛒" />
          ) : (
            GROUP_ORDER.filter((k) => grouped[k]?.length).map((status) => (
              <View key={status} style={{ marginBottom: spacing.xl }}>
                <View style={styles.sectionHead}>
                  <StatusPill label={STATUS_LABEL[status]} tone={STATUS_TONE[status]} />
                </View>
                <View style={styles.list}>
                  {grouped[status].map((p) => (
                    <ListCard
                      key={p.id}
                      title={p.supplier_name}
                      topRight={formatDateShort(p.purchase_date)}
                      subtitleA={p.number}
                      subtitleB={p.category?.name || p.project?.name || '—'}
                      amount={formatMoney(p.amount)}
                      amountColor={colors.danger}
                      onPress={() => router.push(`/purchases/${p.id}`)}
                    />
                  ))}
                </View>
              </View>
            ))
          )}
        </ScrollView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  topbar: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.xl, paddingTop: spacing.md, paddingBottom: spacing.md,
  },
  title: { fontSize: 24, fontWeight: '800', color: colors.text, letterSpacing: -0.5 },
  addBtn: {
    width: 40, height: 40, borderRadius: radii.full,
    backgroundColor: colors.primary,
    alignItems: 'center', justifyContent: 'center',
  },
  addBtnText: { color: '#fff', fontSize: 22, fontWeight: '500', lineHeight: 24 },

  searchWrap: { paddingHorizontal: spacing.xl, paddingBottom: spacing.md },
  search: {
    backgroundColor: colors.card,
    borderRadius: radii.md,
    paddingHorizontal: spacing.lg,
    paddingVertical: 12,
    fontSize: 13,
    color: colors.text,
  },

  sectionHead: { marginBottom: spacing.md, paddingHorizontal: 4 },
  list: { gap: spacing.sm },
});
