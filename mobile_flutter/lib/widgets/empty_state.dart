import 'package:flutter/material.dart';
import '../theme/colors.dart';

class EmptyState extends StatelessWidget {
  final String title;
  final String? subtitle;
  final String icon;

  const EmptyState({
    super.key,
    this.title = 'لا توجد بيانات',
    this.subtitle,
    this.icon = '📭',
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(AppSpacing.xxxl),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(icon, style: const TextStyle(fontSize: 40)),
          const SizedBox(height: AppSpacing.md),
          Text(title,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.text)),
          if (subtitle != null) ...[
            const SizedBox(height: 4),
            Text(subtitle!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
          ],
        ],
      ),
    );
  }
}
