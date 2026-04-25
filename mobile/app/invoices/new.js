import React, { useEffect, useState } from 'react';
import { ScrollView, Alert, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { api, endpoints, apiErrorMessage } from '../../src/api/client';
import Input from '../../src/components/Input';
import Picker from '../../src/components/Picker';
import Button from '../../src/components/Button';
import { colors, spacing } from '../../src/theme/colors';

export default function NewInvoice() {
  const router = useRouter();

  const [form, setForm] = useState({
    type: 'sales',
    party_name: '',
    amount: '',
    issue_date: new Date().toISOString().slice(0, 10),
    due_date: '',
    status: 'draft',
    project_id: '',
    bank_account_id: '',
    description: '',
  });

  const [resources, setResources] = useState({ projects: [], bankAccounts: [] });
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    (async () => {
      try {
        const [projs, banks] = await Promise.all([
          api.get(endpoints.projects),
          api.get(endpoints.bankAccounts),
        ]);
        setResources({ projects: projs.data, bankAccounts: banks.data });
      } catch (err) { Alert.alert('خطأ', apiErrorMessage(err)); }
    })();
  }, []);

  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }));

  async function onSave() {
    if (!form.party_name || !form.amount) {
      Alert.alert('تنبيه', 'أدخل الطرف والمبلغ');
      return;
    }
    setSaving(true);
    try {
      await api.post(endpoints.invoices, {
        ...form,
        amount: parseFloat(form.amount),
        project_id: form.project_id || null,
        bank_account_id: form.bank_account_id || null,
        due_date: form.due_date || null,
      });
      router.back();
    } catch (err) { Alert.alert('خطأ', apiErrorMessage(err)); } finally { setSaving(false); }
  }

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.bg }} edges={['bottom']}>
      <ScrollView contentContainerStyle={{ padding: spacing.lg }} keyboardShouldPersistTaps="handled">
        <Picker
          label="نوع الفاتورة"
          value={form.type}
          onChange={(v) => set('type', v)}
          options={[{ value: 'sales', label: 'مبيعات' }, { value: 'purchase', label: 'مشتريات' }]}
        />

        <Input label="اسم العميل / المورد" value={form.party_name} onChangeText={(v) => set('party_name', v)} />
        <Input label="المبلغ" value={form.amount} onChangeText={(v) => set('amount', v)} keyboardType="decimal-pad" placeholder="0.00" />
        <Input label="تاريخ الإصدار" value={form.issue_date} onChangeText={(v) => set('issue_date', v)} placeholder="YYYY-MM-DD" />
        <Input label="تاريخ الاستحقاق" value={form.due_date} onChangeText={(v) => set('due_date', v)} placeholder="YYYY-MM-DD (اختياري)" />

        <Picker
          label="الحالة"
          value={form.status}
          onChange={(v) => set('status', v)}
          options={[
            { value: 'draft', label: 'مسودة' },
            { value: 'sent', label: 'مرسلة' },
            { value: 'paid', label: 'مدفوعة' },
            { value: 'overdue', label: 'متأخرة' },
            { value: 'cancelled', label: 'ملغاة' },
          ]}
        />

        <Picker
          label="المشروع"
          value={form.project_id}
          onChange={(v) => set('project_id', v)}
          options={resources.projects.map((p) => ({ value: p.id, label: `${p.code} — ${p.name}` }))}
        />

        <Picker
          label="الحساب البنكي"
          value={form.bank_account_id}
          onChange={(v) => set('bank_account_id', v)}
          options={resources.bankAccounts.map((b) => ({ value: b.id, label: b.name }))}
        />

        <Input label="الوصف" value={form.description} onChangeText={(v) => set('description', v)} multiline rows={3} />

        <View style={{ marginTop: spacing.lg, gap: spacing.sm }}>
          <Button title="حفظ الفاتورة" onPress={onSave} loading={saving} size="lg" />
          <Button title="إلغاء" variant="secondary" onPress={() => router.back()} />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
