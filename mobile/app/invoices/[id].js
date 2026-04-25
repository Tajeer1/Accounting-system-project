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

const STATUS_COLOR = { draft: 'slate', sent: 'info', paid: 'success', overdue: 'danger', cancelled: 'slate' };

export default function InvoiceDetail() {
  const { id } = useLocalSearchParams();
  const router = useRouter();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [acting, setActing] = useState(false);

  const load = useCallback(async () => {
    try {
      const res = await api.get(endpoints.invoice(id));
      setData(res.data);
    } catch (err) {
      Alert.alert('خطأ', apiErrorMessage(err));
    } finally { setLoading(false); }
  }, [id]);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  async function markPaid() {
    setActing(true);
    try {
      await api.post(endpoints.invoiceMarkPaid(id));
      load();
    } catch (err) { Alert.alert('خطأ', apiErrorMessage(err)); } finally { setActing(false); }
  }

  async function onDelete() {
    Alert.alert('حذف الفاتورة', 'تأكيد الحذف؟', [
      { text: 'إلغاء', style: 'cancel' },
      { text: 'حذف', style: 'destructive', onPress: async () => {
        try { await api.delete(endpoints.invoice(id)); router.back(); } catch (err) { Alert.alert('خطأ', apiErrorMessage(err)); }
      }},
    ]);
  }

  if (loading) {
    return <SafeAreaView style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}><ActivityIndicator color={colors.primary} /></SafeAreaView>;
  }

  if (!data) return null;
  const inv = data.invoice;
  const je = data.journal_entry;
  const isSales = inv.type === 'sales';

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.bg }} edges={['bottom']}>
      <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.md }}>
        <View style={[styles.amountCard, { backgroundColor: isSales ? colors.successSoft : colors.dangerSoft }]}>
          <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
            <Badge color={isSales ? 'success' : 'danger'}>{inv.type_label}</Badge>
            <Badge color={STATUS_COLOR[inv.status]}>{inv.status_label}</Badge>
          </View>
          <Text style={styles.amountLabel}>المبلغ</Text>
          <Text style={[styles.amount, { color: isSales ? colors.success : colors.danger }]}>{formatMoney(inv.amount)}</Text>
          <Text style={styles.number}>{inv.number}</Text>
        </View>

        <Card title="تفاصيل الفاتورة">
          <Row label="الطرف" value={inv.party_name} />
          <Row label="تاريخ الإصدار" value={formatDate(inv.issue_date)} />
          {inv.due_date && <Row label="تاريخ الاستحقاق" value={formatDate(inv.due_date)} />}
          {inv.project && <Row label="المشروع" value={inv.project.name} />}
          {inv.bank_account && <Row label="الحساب" value={inv.bank_account.name} />}
          {inv.description && <Row label="الوصف" value={inv.description} multiline />}
        </Card>

        {je && (
          <Card title="القيد المحاسبي" subtitle={je.number}>
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

        <View style={{ gap: spacing.sm }}>
          {inv.status !== 'paid' && <Button title="تحديد كمدفوعة" onPress={markPaid} loading={acting} />}
          <Button title="حذف الفاتورة" variant="danger" onPress={onDelete} />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

function Row({ label, value, multiline }) {
  return (
    <View style={styles.row}>
      <Text style={styles.rowLabel}>{label}</Text>
      <Text style={[styles.rowValue, multiline && { flex: 2 }]}>{value}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  amountCard: {
    borderRadius: radii.xl,
    padding: spacing.xl,
    alignItems: 'flex-end',
  },
  amountLabel: { fontSize: 11, color: colors.textMuted, marginTop: spacing.md, textAlign: 'right' },
  amount: { fontSize: 28, fontWeight: '800', marginTop: 4, textAlign: 'right' },
  number: { fontSize: 11, color: colors.textMuted, fontFamily: 'monospace', marginTop: 4, textAlign: 'right' },

  row: { flexDirection: 'row', paddingVertical: 8, alignItems: 'flex-start' },
  rowLabel: { fontSize: 12, color: colors.textMuted, width: 110, textAlign: 'right' },
  rowValue: { fontSize: 13, color: colors.text, fontWeight: '600', flex: 1, textAlign: 'right' },

  jeLine: { flexDirection: 'row', paddingVertical: spacing.sm, borderBottomWidth: 1, borderBottomColor: colors.borderLight, gap: spacing.md },
  jeAccount: { fontSize: 13, color: colors.text, fontWeight: '600', textAlign: 'right' },
  jeCode: { fontSize: 10, color: colors.textMuted, fontFamily: 'monospace', marginTop: 2, textAlign: 'right' },
  jeDebit: { fontSize: 12, color: colors.success, fontWeight: '700' },
  jeCredit: { fontSize: 12, color: colors.danger, fontWeight: '700', marginTop: 2 },
});
