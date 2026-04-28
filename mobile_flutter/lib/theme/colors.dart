import 'package:flutter/material.dart';

class AppColors {
  // Brand
  static const primary = Color(0xFF0A0A0A);
  static const primaryDark = Color(0xFF000000);
  static const primarySoft = Color(0xFFF2F2F2);

  // Status (soft pill style)
  static const success = Color(0xFF0F8A3C);
  static const successSoft = Color(0xFFD4F5DC);
  static const danger = Color(0xFFC53030);
  static const dangerSoft = Color(0xFFFDE8E8);
  static const warning = Color(0xFF9B7E00);
  static const warningSoft = Color(0xFFFCF0CD);
  static const info = Color(0xFF1E6FD9);
  static const infoSoft = Color(0xFFD9EBFF);
  static const violet = Color(0xFF6B46C1);
  static const violetSoft = Color(0xFFEADDFC);

  // Surfaces
  static const bg = Color(0xFFF8F6F1);
  static const bgWarm = Color(0xFFF3EFE7);
  static const card = Color(0xFFFFFFFF);
  static const cardAlt = Color(0xFFFAFAF7);

  // Borders
  static const border = Color(0xFFECEAE4);
  static const borderLight = Color(0xFFF2F0EB);

  // Text
  static const text = Color(0xFF0A0A0A);
  static const textSecondary = Color(0xFF6B7280);
  static const textMuted = Color(0xFF9CA3AF);
  static const textSubtle = Color(0xFFB8B8B8);
}

class AppSpacing {
  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double lg = 16;
  static const double xl = 20;
  static const double xxl = 24;
  static const double xxxl = 32;
}

class AppRadii {
  static const double sm = 10;
  static const double md = 14;
  static const double lg = 18;
  static const double xl = 22;
  static const double xxl = 28;
  static const double full = 9999;
}

class AppShadows {
  static List<BoxShadow> card = [
    BoxShadow(
      color: const Color(0xFF0A0A0A).withValues(alpha: 0.04),
      offset: const Offset(0, 2),
      blurRadius: 8,
    ),
  ];

  static List<BoxShadow> soft = [
    BoxShadow(
      color: const Color(0xFF0A0A0A).withValues(alpha: 0.03),
      offset: const Offset(0, 1),
      blurRadius: 4,
    ),
  ];
}
