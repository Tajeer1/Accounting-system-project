import React, { useCallback, useMemo, useState } from 'react';
import { View, Text, ScrollView, StyleSheet, ActivityIndicator, RefreshControl, TouchableOpacity } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect } from 'expo-router';
import { api, endpoints, apiErrorMessage } from '../../src/api/client';
import EmptyState from '../../src/components/EmptyState';
import StatusPill from '../../src/components/StatusPill';
import { colors, radii, spacing, shadows } from '../../src/theme/colors';
import { shortMoney, formatMoney } from '../../src/utils/format';

const STATUS_TONE = { planned: 'info', in_progress: 'warning', completed: 'success', cancelled: 'neutral' };
const STATUS_LABEL = { planned: 'مخططة', in_progress: 'قيد التنفيذ', completed: 'مكتملة', cancelled: 'ملغاة' };
const GROUP_ORDER = ['in_progress', 'planned', 'completed', 'cancelled'];

export default function ProjectsScreen() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    try {
      const res = await api.get(endpoints.projects);
      setItems(res.data || []);
    } catch (err) {
      console.log('projects error', apiErrorMessage(err));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  const grouped = useMemo(() => {
    const g = {};
    items.forEach((p) => { (g[p.status] = g[p.status] || []).push(p); });
    return g;
  }, [items]);

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.bg }} edges={['top']}>
      <View style={styles.topbar}>
        <Text style={styles.title}>المشاريع</Text>
        <Text style={styles.count}>{items.length}</Text>
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
            <EmptyState title="لا توجد مشاريع" icon="💼" />
          ) : (
            GROUP_ORDER.filter((k) => grouped[k]?.length).map((status) => (
              <View key={status} style={{ marginBottom: spacing.xl }}>
                <View style={styles.sectionHead}>
                  <StatusPill label={STATUS_LABEL[status]} tone={STATUS_TONE[status]} />
                </View>
                <View style={styles.list}>
                  {grouped[status].map((p) => {
                    const progress = p.contract_value > 0 ? Math.min(100, (p.total_revenue / p.contract_value) * 100) : 0;
                    return (
                      <View key={p.id} style={styles.card}>
                        <View style={styles.cardHeader}>
                          <View style={{ flex: 1 }}>
                            <Text style={styles.code}>{p.code}</Text>
                            <Text style={styles.name}>{p.name}</Text>
                            {p.client_name ? <Text style={styles.client}>{p.client_name}</Text> : null}
                          </View>
                          <Text style={styles.amount}>{shortMoney(p.contract_value)}</Text>
                        </View>

                        <View style={styles.progressWrap}>
                          <View style={styles.progressRow}>
                            <Text style={styles.progressLabel}>الإيراد</Text>
                            <Text style={styles.progressValue}>{shortMoney(p.total_revenue)} / {shortMoney(p.contract_value)}</Text>
                          </View>
                          <View style={styles.progressBar}>
                            <View style={[styles.progressFill, { width: `${progress}%` }]} />
                          </View>
                        </View>

                        <View style={styles.stats}>
                          <View style={styles.stat}>
                            <Text style={styles.statLabel}>الربح</Text>
                            <Text style={[styles.statValue, { color: p.profit >= 0 ? colors.success : colors.danger }]}>
                              {shortMoney(p.profit)}
                            </Text>
                          </View>
                          <View style={styles.statDivider} />
                          <View style={styles.stat}>
                            <Text style={styles.statLabel}>التكلفة</Text>
                            <Text style={[styles.statValue, { color: colors.danger }]}>{shortMoney(p.total_cost)}</Text>
                          </View>
                          <View style={styles.statDivider} />
                          <View style={styles.stat}>
                            <Text style={styles.statLabel}>الهامش</Text>
                            <Text style={styles.statValue}>{p.profit_margin}%</Text>
                          </View>
                        </View>
                      </View>
                    );
                  })}
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
  count: { fontSize: 14, color: colors.textMuted, fontWeight: '600' },

  sectionHead: { marginBottom: spacing.md, paddingHorizontal: 4 },
  list: { gap: spacing.md },

  card: {
    backgroundColor: colors.card,
    borderRadius: radii.xl,
    padding: spacing.lg,
    ...shadows.soft,
  },
  cardHeader: { flexDirection: 'row', alignItems: 'flex-start', gap: spacing.md },
  code: { fontSize: 10, color: colors.textMuted, textAlign: 'right', fontWeight: '500' },
  name: { fontSize: 15, fontWeight: '800', color: colors.text, marginTop: 2, textAlign: 'right', letterSpacing: -0.2 },
  client: { fontSize: 11, color: colors.textMuted, marginTop: 2, textAlign: 'right' },
  amount: { fontSize: 15, fontWeight: '700', color: colors.text },

  progressWrap: { marginTop: spacing.lg, paddingTop: spacing.md, borderTopWidth: 1, borderTopColor: colors.borderLight },
  progressRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 },
  progressLabel: { fontSize: 11, color: colors.textMuted },
  progressValue: { fontSize: 11, fontWeight: '700', color: colors.text },
  progressBar: { height: 4, backgroundColor: colors.borderLight, borderRadius: 2, overflow: 'hidden' },
  progressFill: { height: '100%', backgroundColor: colors.primary },

  stats: { flexDirection: 'row', alignItems: 'center', marginTop: spacing.lg },
  stat: { flex: 1, alignItems: 'center' },
  statLabel: { fontSize: 10, color: colors.textMuted },
  statValue: { fontSize: 13, fontWeight: '700', color: colors.text, marginTop: 2 },
  statDivider: { width: 1, height: 24, backgroundColor: colors.borderLight },
});
