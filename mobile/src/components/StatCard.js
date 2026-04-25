import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, radii, spacing } from '../theme/colors';

const COLOR_MAP = {
  primary: { bg: colors.primarySoft, text: colors.primary },
  success: { bg: colors.successSoft, text: colors.success },
  danger: { bg: colors.dangerSoft, text: colors.danger },
  warning: { bg: colors.warningSoft, text: colors.warning },
  info: { bg: colors.infoSoft, text: colors.info },
  violet: { bg: colors.violetSoft, text: colors.violet },
};

export default function StatCard({ label, value, subtitle, color = 'primary', icon }) {
  const c = COLOR_MAP[color] || COLOR_MAP.primary;
  return (
    <View style={styles.card}>
      <View style={styles.header}>
        <View style={{ flex: 1 }}>
          <Text style={styles.label}>{label}</Text>
          <Text style={styles.value} numberOfLines={1} adjustsFontSizeToFit>{value}</Text>
          {subtitle && <Text style={styles.subtitle}>{subtitle}</Text>}
        </View>
        {icon && (
          <View style={[styles.iconBox, { backgroundColor: c.bg }]}>
            <Text style={[styles.iconText, { color: c.text }]}>{icon}</Text>
          </View>
        )}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.card,
    borderRadius: radii.xl,
    borderWidth: 1,
    borderColor: colors.border,
    padding: spacing.lg,
    flex: 1,
    minWidth: 150,
  },
  header: { flexDirection: 'row', alignItems: 'flex-start' },
  label: {
    fontSize: 11,
    color: colors.textMuted,
    fontWeight: '500',
    textAlign: 'right',
  },
  value: {
    fontSize: 18,
    fontWeight: '800',
    color: colors.text,
    marginTop: 6,
    textAlign: 'right',
  },
  subtitle: {
    fontSize: 10,
    color: colors.textMuted,
    marginTop: 2,
    textAlign: 'right',
  },
  iconBox: {
    width: 36,
    height: 36,
    borderRadius: radii.md,
    alignItems: 'center',
    justifyContent: 'center',
    marginStart: spacing.sm,
  },
  iconText: { fontSize: 16 },
});
