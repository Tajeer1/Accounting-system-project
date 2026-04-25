import React, { useCallback, useState } from 'react';
import { View, Text, ScrollView, StyleSheet, ActivityIndicator, Alert } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useLocalSearchParams, useFocusEffect, useRouter } from 'expo-router';
import { api, endpoints, apiErrorMessage } from '../../src/api/client';
import Card from '../../src/components/Card';
import Badge from '../../src/components/Badge';
import Button from '../../src/components/Button';
import { colors, radii, spacing } from '../../src/theme/colors';
import { formatMoney, formatDate } from '../../src/utils/format';

const STATUS_COLOR = { pending: 'warning', paid: 'success', cancelled: 'slate' };

export default function PurchaseDetail() {
  const { id } = useLocalSearchParams();
  const router = useRouter();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    try {
      const res = await api.get(endpoints.purchase(id));
      setData(res.data);
    } catch (err) {
      Alert.alert('خطأ', apiErrorMessage(err));
    } finally {
      setLoading(false);
    }
  }, [id]);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  async function onDelete() {
    Alert.alert('حذف العملية', 'تأكيد حذف العملية والقيد المرتبط؟', [
      { text: 'إلغاء', style: 'cancel' },
      {
        text: 'حذف', style: 'destructive',
        onPress: async () => {
          try {
            await api.delete(endpoints.purchase(id));
            router.back();
          } catch (err) {
            Alert.alert('خطأ', apiErrorMessage(err));
          }
        }
      },
    ]);
  }

  if (loading) {
    return <SafeAreaView style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}><ActivityIndicator color={colors.primary} /></SafeAreaView>;
  }

  if (!data) return null;
  const p = data.purchase;
  const je = data.journal_entry;

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.bg }} edges={['bottom']}>
      <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.md }}>
        <View style={styles.amountCard}>
          <Text style={styles.amountLabel}>المبلغ</Text>
          <Text style={styles.amount}>{formatMoney(p.amount)}</Text>
          <View style={{ marginTop: spacing.sm }}>
            <Badge color={STATUS_COLOR[p.status]}>{p.status_label}</Badge>
          </View>
        </View>

        <Card title="تفاصيل العملية">
          <Row label="رقم العملية" value={p.number} mono />
          <Row label="التاريخ" value={formatDate(p.purchase_date)} />
          <Row label="المورد" value={p.supplier_name} />
          {p.category && <Row label="التصنيف" value={p.category.name} />}
          {p.bank_account && <Row label="الحساب البنكي" value={p.bank_account.name} />}
          {p.project && <Row label="المشروع" value={`${p.project.code} — ${p.project.name}`} />}
          {p.description && <Row label="الوصف" value={p.description} multiline />}
        </Card>

        {je && (
          <Card title="القيد المحاسبي" subtitle={`${je.number} · ${je.status === 'posted' ? 'منشور' : 'مسودة'}`}>
            {je.lines.map((line, i) => (
              <View key={i} style={styles.jeLine}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.jeAccount}>{line.account}</Text>
                  <Text style={styles.jeCode}>{line.code}</Text>
                </View>
                <View style={{ alignItems: 'flex-end' }}>
                  {line.debit > 0 && <Text style={styles.jeDebit}>مدين: {formatMoney(line.debit)}</Text>}
                  {line.credit > 0 && <Text style={styles.jeCredit}>دائن: {formatMoney(line.credit)}</Text>}
                </View>
              </View>
            ))}
          </Card>
        )}

        <Button title="حذف العملية" variant="danger" onPress={onDelete} />
      </ScrollView>
    </SafeAreaView>
  );
}

function Row({ label, value, mono, multiline }) {
  return (
    <View style={styles.row}>
      <Text style={styles.rowLabel}>{label}</Text>
      <Text style={[styles.rowValue, mono && { fontFamily: 'monospace' }, multiline && { flex: 2 }]}>{value}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  amountCard: {
    backgroundColor: colors.dangerSoft,
    borderRadius: radii.xl,
    padding: spacing.xl,
    alignItems: 'flex-end',
  },
  amountLabel: { fontSize: 11, color: colors.textMuted, textAlign: 'right' },
  amount: { fontSize: 28, fontWeight: '800', color: colors.danger, marginTop: 4, textAlign: 'right' },

  row: { flexDirection: 'row', paddingVertical: 8, alignItems: 'flex-start' },
  rowLabel: { fontSize: 12, color: colors.textMuted, width: 100, textAlign: 'right' },
  rowValue: { fontSize: 13, color: colors.text, fontWeight: '600', flex: 1, textAlign: 'right' },

  jeLine: { flexDirection: 'row', paddingVertical: spacing.sm, borderBottomWidth: 1, borderBottomColor: colors.borderLight, gap: spacing.md },
  jeAccount: { fontSize: 13, color: colors.text, fontWeight: '600', textAlign: 'right' },
  jeCode: { fontSize: 10, color: colors.textMuted, fontFamily: 'monospace', marginTop: 2, textAlign: 'right' },
  jeDebit: { fontSize: 12, color: colors.success, fontWeight: '700' },
  jeCredit: { fontSize: 12, color: colors.danger, fontWeight: '700', marginTop: 2 },
});
