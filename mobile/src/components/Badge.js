import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, radii } from '../theme/colors';

const MAP = {
  slate: { bg: '#f1f5f9', text: '#475569' },
  primary: { bg: colors.primarySoft, text: colors.primary },
  success: { bg: colors.successSoft, text: colors.success },
  danger: { bg: colors.dangerSoft, text: colors.danger },
  warning: { bg: colors.warningSoft, text: colors.warning },
  info: { bg: colors.infoSoft, text: colors.info },
  violet: { bg: colors.violetSoft, text: colors.violet },
};

export default function Badge({ children, color = 'slate' }) {
  const c = MAP[color] || MAP.slate;
  return (
    <View style={[styles.badge, { backgroundColor: c.bg }]}>
      <Text style={[styles.text, { color: c.text }]}>{children}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: radii.sm,
    alignSelf: 'flex-start',
  },
  text: {
    fontSize: 10,
    fontWeight: '600',
  },
});
