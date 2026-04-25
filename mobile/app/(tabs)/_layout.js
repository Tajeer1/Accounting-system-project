import React from 'react';
import { Tabs } from 'expo-router';
import { Text, View, StyleSheet, Platform, Pressable } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { colors, radii, spacing } from '../../src/theme/colors';

const TABS = [
  { name: 'index', label: 'الرئيسية', icon: 'home-outline', activeIcon: 'home' },
  { name: 'purchases', label: 'مشتريات', icon: 'cart-outline', activeIcon: 'cart' },
  { name: 'invoices', label: 'فواتير', icon: 'document-text-outline', activeIcon: 'document-text' },
  { name: 'projects', label: 'مشاريع', icon: 'briefcase-outline', activeIcon: 'briefcase' },
  { name: 'more', label: 'المزيد', icon: 'ellipsis-horizontal', activeIcon: 'ellipsis-horizontal' },
];

function CustomTabBar({ state, navigation }) {
  const insets = useSafeAreaInsets();
  const bottomPad = Math.max(insets.bottom, 10);

  return (
    <View style={[styles.container, { paddingBottom: bottomPad }]}>
      <View style={styles.bar}>
        {state.routes.map((route, index) => {
          const tab = TABS.find((t) => t.name === route.name);
          if (!tab) return null;

          const isFocused = state.index === index;
          const onPress = () => {
            const event = navigation.emit({
              type: 'tabPress',
              target: route.key,
              canPreventDefault: true,
            });
            if (!isFocused && !event.defaultPrevented) {
              navigation.navigate(route.name);
            }
          };
          const onLongPress = () => {
            navigation.emit({ type: 'tabLongPress', target: route.key });
          };

          if (isFocused) {
            return (
              <Pressable
                key={route.key}
                onPress={onPress}
                onLongPress={onLongPress}
                style={styles.activeTab}
              >
                <Ionicons name={tab.activeIcon} size={18} color="#fff" />
                <Text style={styles.activeLabel}>{tab.label}</Text>
              </Pressable>
            );
          }

          return (
            <Pressable
              key={route.key}
              onPress={onPress}
              onLongPress={onLongPress}
              android_ripple={{ color: colors.borderLight, borderless: true, radius: 24 }}
              style={styles.inactiveTab}
              hitSlop={10}
            >
              <Ionicons name={tab.icon} size={22} color={colors.textSecondary} />
            </Pressable>
          );
        })}
      </View>
    </View>
  );
}

export default function TabsLayout() {
  return (
    <Tabs
      tabBar={(props) => <CustomTabBar {...props} />}
      screenOptions={{ headerShown: false }}
    >
      {TABS.map((t) => (
        <Tabs.Screen key={t.name} name={t.name} />
      ))}
    </Tabs>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: 'transparent',
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.sm,
  },
  bar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-around',
    backgroundColor: colors.card,
    borderRadius: radii.full,
    paddingHorizontal: 8,
    paddingVertical: 8,
    ...Platform.select({
      ios: {
        shadowColor: '#0A0A0A',
        shadowOffset: { width: 0, height: 6 },
        shadowOpacity: 0.08,
        shadowRadius: 16,
      },
      android: { elevation: 8 },
    }),
  },
  activeTab: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: colors.primary,
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: radii.full,
    minHeight: 42,
  },
  activeLabel: {
    color: '#fff',
    fontSize: 13,
    fontWeight: '700',
    letterSpacing: -0.1,
  },
  inactiveTab: {
    width: 44,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radii.full,
  },
});
