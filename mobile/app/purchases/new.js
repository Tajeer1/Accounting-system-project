import React, { useEffect, useState } from 'react';
import { ScrollView, Alert, StyleSheet, View, Platform } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { api, endpoints, apiErrorMessage } from '../../src/api/client';
import Input from '../../src/components/Input';
import Picker from '../../src/components/Picker';
import Button from '../../src/components/Button';
import { colors, spacing } from '../../src/theme/colors';

export default function NewPurchase() {
  const router = useRouter();

  const [form, setForm] = useState({
    supplier_name: '',
    amount: '',
    purchase_date: new Date().toISOString().slice(0, 10),
    category_id: '',
    bank_account_id: '',
    project_id: '',
    description: '',
    status: 'paid',
  });

  const [resources, setResources] = useState({ categories: [], bankAccounts: [], projects: [] });
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    (async () => {
      try {
        const [cats, banks, projs] = await Promise.all([
          api.get(endpoints.categories),
          api.get(endpoints.bankAccounts),
          api.get(endpoints.projects),
        ]);
        setResources({
          categories: cats.data,
          bankAccounts: banks.data,
          projects: projs.data,
        });
      } catch (err) {
        Alert.alert('خطأ', apiErrorMessage(err));
      }
    })();
  }, []);

  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }));

  async function onSave() {
    if (!form.supplier_name || !form.amount) {
      Alert.alert('تنبيه', 'أدخل اسم المورد والمبلغ');
      return;
    }
    setSaving(true);
    try {
      const payload = {
        ...form,
        amount: parseFloat(form.amount),
        category_id: form.category_id || null,
        bank_account_id: form.bank_account_id || null,
        project_id: form.project_id || null,
      };
      await api.post(endpoints.purchases, payload);
      router.back();
    } catch (err) {
      Alert.alert('خطأ', apiErrorMessage(err));
    } finally {
      setSaving(false);
    }
  }

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.bg }} edges={['bottom']}>
      <ScrollView contentContainerStyle={{ padding: spacing.lg }} keyboardShouldPersistTaps="handled">
        <Input label="اسم المورد" value={form.supplier_name} onChangeText={(v) => set('supplier_name', v)} placeholder="مثال: شركة المعدات" />
        <Input label="المبلغ" value={form.amount} onChangeText={(v) => set('amount', v)} keyboardType="decimal-pad" placeholder="0.00" />
        <Input label="تاريخ العملية" value={form.purchase_date} onChangeText={(v) => set('purchase_date', v)} placeholder="YYYY-MM-DD" />

        <Picker
          label="الحالة"
          value={form.status}
          onChange={(v) => set('status', v)}
          options={[
            { value: 'paid', label: 'مدفوع' },
            { value: 'pending', label: 'معلق' },
            { value: 'cancelled', label: 'ملغي' },
          ]}
        />

        <Picker
          label="التصنيف"
          value={form.category_id}
          onChange={(v) => set('category_id', v)}
          options={resources.categories.map((c) => ({ value: c.id, label: c.name }))}
        />

        <Picker
          label="الحساب البنكي"
          value={form.bank_account_id}
          onChange={(v) => set('bank_account_id', v)}
          options={resources.bankAccounts.map((b) => ({ value: b.id, label: b.name }))}
        />

        <Picker
          label="المشروع"
          value={form.project_id}
          onChange={(v) => set('project_id', v)}
          options={resources.projects.map((p) => ({ value: p.id, label: `${p.code} — ${p.name}` }))}
        />

        <Input label="الوصف" value={form.description} onChangeText={(v) => set('description', v)} multiline rows={3} />

        <View style={{ marginTop: spacing.lg, gap: spacing.sm }}>
          <Button title="حفظ العملية" onPress={onSave} loading={saving} size="lg" />
          <Button title="إلغاء" variant="secondary" onPress={() => router.back()} />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
