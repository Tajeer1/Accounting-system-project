import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, spacing } from '../theme/colors';

export default function EmptyState({ title = 'لا توجد بيانات', subtitle, icon = '📭' }) {
  return (
    <View style={styles.container}>
      <Text style={styles.icon}>{icon}</Text>
      <Text style={styles.title}>{title}</Text>
      {subtitle && <Text style={styles.subtitle}>{subtitle}</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: spacing.xxxl,
    alignItems: 'center',
    justifyContent: 'center',
  },
  icon: { fontSize: 40, marginBottom: spacing.md },
  title: { fontSize: 14, fontWeight: '600', color: colors.text, textAlign: 'center' },
  subtitle: { fontSize: 12, color: colors.textMuted, marginTop: 4, textAlign: 'center' },
});
