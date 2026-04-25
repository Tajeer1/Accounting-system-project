import React, { useState } from 'react';
import { View, Text, TouchableOpacity, Modal, FlatList, StyleSheet } from 'react-native';
import { colors, radii, spacing } from '../theme/colors';

export default function Picker({ label, value, onChange, options = [], placeholder = '— اختر —' }) {
  const [open, setOpen] = useState(false);
  const selected = options.find((o) => String(o.value) === String(value));

  return (
    <View style={{ marginBottom: spacing.md }}>
      {label && <Text style={styles.label}>{label}</Text>}
      <TouchableOpacity style={styles.input} onPress={() => setOpen(true)} activeOpacity={0.7}>
        <Text style={[styles.value, !selected && { color: colors.textMuted }]}>
          {selected ? selected.label : placeholder}
        </Text>
        <Text style={styles.arrow}>▼</Text>
      </TouchableOpacity>

      <Modal visible={open} transparent animationType="slide" onRequestClose={() => setOpen(false)}>
        <TouchableOpacity style={styles.backdrop} activeOpacity={1} onPress={() => setOpen(false)}>
          <TouchableOpacity activeOpacity={1} style={styles.sheet}>
            <View style={styles.handle} />
            <Text style={styles.sheetTitle}>{label || 'اختر'}</Text>
            <FlatList
              data={[{ value: '', label: placeholder }, ...options]}
              keyExtractor={(item, i) => String(item.value) + i}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.option}
                  onPress={() => { onChange(item.value); setOpen(false); }}
                >
                  <Text style={[styles.optionText, String(item.value) === String(value) && { color: colors.primary, fontWeight: '700' }]}>
                    {item.label}
                  </Text>
                </TouchableOpacity>
              )}
              style={{ maxHeight: 360 }}
            />
          </TouchableOpacity>
        </TouchableOpacity>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  label: { fontSize: 12, fontWeight: '600', color: colors.textSecondary, marginBottom: 6, textAlign: 'right' },
  input: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radii.md,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  value: { fontSize: 14, color: colors.text, flex: 1, textAlign: 'right' },
  arrow: { color: colors.textMuted, fontSize: 10, marginStart: spacing.sm },
  backdrop: { flex: 1, backgroundColor: 'rgba(15,23,42,0.5)', justifyContent: 'flex-end' },
  sheet: {
    backgroundColor: '#fff',
    borderTopLeftRadius: radii.xl,
    borderTopRightRadius: radii.xl,
    padding: spacing.xl,
    maxHeight: '75%',
  },
  handle: {
    width: 40, height: 4, backgroundColor: colors.border, borderRadius: 2, alignSelf: 'center', marginBottom: spacing.md,
  },
  sheetTitle: { fontSize: 15, fontWeight: '700', color: colors.text, marginBottom: spacing.md, textAlign: 'right' },
  option: { paddingVertical: spacing.md, borderBottomWidth: 1, borderBottomColor: colors.borderLight },
  optionText: { fontSize: 14, color: colors.text, textAlign: 'right' },
});
