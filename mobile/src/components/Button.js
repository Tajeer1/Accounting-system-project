import React from 'react';
import { TouchableOpacity, Text, ActivityIndicator, StyleSheet, View } from 'react-native';
import { colors, radii, spacing } from '../theme/colors';

export default function Button({ title, onPress, variant = 'primary', size = 'md', loading, disabled, icon, style }) {
  const sizes = {
    sm: { paddingV: 10, paddingH: 14, fontSize: 12, radius: radii.full },
    md: { paddingV: 14, paddingH: 18, fontSize: 14, radius: radii.full },
    lg: { paddingV: 16, paddingH: 22, fontSize: 15, radius: radii.full },
  };
  const s = sizes[size] || sizes.md;

  const variants = {
    primary: { bg: colors.primary, text: '#fff', border: 'transparent' },
    secondary: { bg: colors.card, text: colors.text, border: colors.border },
    danger: { bg: colors.danger, text: '#fff', border: 'transparent' },
    ghost: { bg: 'transparent', text: colors.textSecondary, border: 'transparent' },
    subtle: { bg: colors.bgWarm, text: colors.text, border: 'transparent' },
  };
  const v = variants[variant] || variants.primary;

  const isDisabled = disabled || loading;

  return (
    <TouchableOpacity
      onPress={onPress}
      disabled={isDisabled}
      activeOpacity={0.85}
      style={[{
        backgroundColor: v.bg,
        borderColor: v.border,
        borderWidth: 1,
        paddingVertical: s.paddingV,
        paddingHorizontal: s.paddingH,
        borderRadius: s.radius,
        opacity: isDisabled ? 0.5 : 1,
      }, style]}
    >
      <View style={styles.content}>
        {loading ? (
          <ActivityIndicator color={v.text} size="small" />
        ) : (
          <>
            {icon && <Text style={[styles.icon, { color: v.text }]}>{icon}</Text>}
            <Text style={[styles.label, { color: v.text, fontSize: s.fontSize }]}>{title}</Text>
          </>
        )}
      </View>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  content: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.sm },
  label: { fontWeight: '600', textAlign: 'center', letterSpacing: -0.1 },
  icon: { fontSize: 14, fontWeight: '700' },
});
