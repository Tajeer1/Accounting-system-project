import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, radii } from '../theme/colors';

const STATUS_STYLES = {
  success: { bg: colors.successSoft, text: colors.success },
  warning: { bg: colors.warningSoft, text: colors.warning },
  info: { bg: colors.infoSoft, text: colors.info },
  danger: { bg: colors.dangerSoft, text: colors.danger },
  violet: { bg: colors.violetSoft, text: colors.violet },
  neutral: { bg: '#EFEDE8', text: '#555' },
};

export default function StatusPill({ label, tone = 'success', checked = true, style }) {
  const s = STATUS_STYLES[tone] || STATUS_STYLES.success;
  return (
    <View style={[styles.pill, { backgroundColor: s.bg }, style]}>
      {checked && <Text style={[styles.check, { color: s.text }]}>✓</Text>}
      <Text style={[styles.label, { color: s.text }]}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  pill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: radii.full,
    alignSelf: 'flex-start',
  },
  check: { fontSize: 11, fontWeight: '700' },
  label: { fontSize: 12, fontWeight: '600' },
});
