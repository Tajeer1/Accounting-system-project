import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { colors, radii, spacing, shadows } from '../theme/colors';

/**
 * بطاقة موحدة بنمط التصميم الجديد
 *  ──────────────────────────────────────
 *  | title            topRight (small)  |
 *  |                                    |
 *  | subtitleA                          |
 *  | subtitleB                 amount   |
 *  ──────────────────────────────────────
 */
export default function ListCard({
  title,
  topRight,
  subtitleA,
  subtitleB,
  amount,
  amountColor,
  onPress,
  style,
}) {
  const Wrapper = onPress ? TouchableOpacity : View;
  return (
    <Wrapper activeOpacity={0.85} onPress={onPress} style={[styles.card, style]}>
      <View style={styles.row}>
        <Text style={styles.title} numberOfLines={1}>{title}</Text>
        {topRight ? <Text style={styles.topRight} numberOfLines={1}>{topRight}</Text> : null}
      </View>

      <View style={styles.bottomRow}>
        <View style={{ flex: 1 }}>
          {subtitleA ? <Text style={styles.subtitle}>{subtitleA}</Text> : null}
          {subtitleB ? <Text style={styles.subtitle}>{subtitleB}</Text> : null}
        </View>
        {amount != null && (
          <Text style={[styles.amount, amountColor && { color: amountColor }]}>{amount}</Text>
        )}
      </View>
    </Wrapper>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.card,
    borderRadius: radii.lg,
    padding: spacing.lg,
    ...shadows.soft,
  },
  row: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  title: {
    fontSize: 15,
    fontWeight: '700',
    color: colors.text,
    flex: 1,
    textAlign: 'right',
    letterSpacing: -0.2,
  },
  topRight: {
    fontSize: 12,
    color: colors.textSecondary,
    fontWeight: '500',
  },
  bottomRow: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    justifyContent: 'space-between',
    marginTop: spacing.md,
    gap: spacing.md,
  },
  subtitle: {
    fontSize: 12,
    color: colors.textMuted,
    textAlign: 'right',
    lineHeight: 18,
  },
  amount: {
    fontSize: 15,
    fontWeight: '700',
    color: colors.text,
    letterSpacing: -0.2,
  },
});
