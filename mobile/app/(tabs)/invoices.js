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

const STATUS_TONE = {
  draft: 'neutral',
  sent: 'info',
  paid: 'success',
  overdue: 'danger',
  cancelled: 'neutral',
};

const STATUS_LABEL = {
  draft: 'مسودات',
  sent: 'مرسلة',
  paid: 'مدفوعة',
  overdue: 'متأخرة',
  cancelled: 'ملغاة',
};

const GROUP_ORDER = ['sent', 'paid', 'draft', 'overdue', 'cancelled'];

export default function InvoicesScreen() {
  const router = useRouter();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');
  const [filterType, setFilterType] = useState('');

  const load = useCallback(async () => {
    try {
      const res = await api.get(endpoints.invoices, { params: { q: search || undefined, type: filterType || undefined } });
      setItems(res.data?.data?.data || []);
    } catch (err) {
      console.log('invoices error', apiErrorMessage(err));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [search, filterType]);

  useFocusEffect(useCallback(() => { load(); }, [load]));

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
        <Text style={styles.title}>الفواتير</Text>
        <TouchableOpacity onPress={() => router.push('/invoices/new')} style={styles.addBtn}>
          <Text style={styles.addBtnText}>+</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.searchWrap}>
        <TextInput
          value={search}
          onChangeText={setSearch}
          placeholder="بحث..."
          placeholderTextColor={colors.textSubtle}
          style={styles.search}
          textAlign="right"
          returnKeyType="search"
          onSubmitEditing={load}
        />
      </View>

      <View style={styles.filters}>
        {[['', 'الكل'], ['sales', 'مبيعات'], ['purchase', 'مشتريات']].map(([val, label]) => (
          <TouchableOpacity
            key={val || 'all'}
            style={[styles.chip, filterType === val && styles.chipActive]}
            onPress={() => setFilterType(val)}
          >
            <Text style={[styles.chipText, filterType === val && styles.chipTextActive]}>{label}</Text>
          </TouchableOpacity>
        ))}
      </View>

      {loading ? (
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
          <ActivityIndicator color={colors.primary} />
        </View>
      ) : (
        <ScrollView
          style={{ flex: 1 }}
          contentContainerStyle={{ padding: spacing.lg, paddingBottom: 120 }}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} tintColor={colors.primary} />}
        >
          {items.length === 0 ? (
            <EmptyState title="لا توجد فواتير" subtitle="أضف أول فاتورة" icon="🧾" />
          ) : (
            GROUP_ORDER.filter((k) => grouped[k]?.length).map((status) => (
              <View key={status} style={{ marginBottom: spacing.xl }}>
                <View style={styles.sectionHead}>
                  <StatusPill label={STATUS_LABEL[status]} tone={STATUS_TONE[status]} />
                </View>
                <View style={styles.list}>
                  {grouped[status].map((inv) => (
                    <ListCard
                      key={inv.id}
                      title={inv.party_name}
                      topRight={formatDateShort(inv.issue_date)}
                      subtitleA={inv.number}
                      subtitleB={inv.type_label}
                      amount={formatMoney(inv.amount)}
                      amountColor={inv.type === 'sales' ? colors.success : colors.danger}
                      onPress={() => router.push(`/invoices/${inv.id}`)}
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

  filters: { flexDirection: 'row', gap: spacing.sm, paddingHorizontal: spacing.xl, paddingBottom: spacing.sm },
  chip: {
    paddingHorizontal: spacing.lg, paddingVertical: 8,
    borderRadius: radii.full,
    backgroundColor: colors.card,
  },
  chipActive: { backgroundColor: colors.primary },
  chipText: { fontSize: 12, color: colors.textSecondary, fontWeight: '600' },
  chipTextActive: { color: '#fff' },

  sectionHead: { marginBottom: spacing.md, paddingHorizontal: 4 },
  list: { gap: spacing.sm },
});
