import 'package:flutter/material.dart';
import '../theme/colors.dart';

enum StatusTone { success, warning, info, danger, violet, neutral }

class StatusPill extends StatelessWidget {
  final String label;
  final StatusTone tone;
  final bool checked;

  const StatusPill({
    super.key,
    required this.label,
    this.tone = StatusTone.success,
    this.checked = true,
  });

  ({Color bg, Color text}) _colors() {
    switch (tone) {
      case StatusTone.success:
        return (bg: AppColors.successSoft, text: AppColors.success);
      case StatusTone.warning:
        return (bg: AppColors.warningSoft, text: AppColors.warning);
      case StatusTone.info:
        return (bg: AppColors.infoSoft, text: AppColors.info);
      case StatusTone.danger:
        return (bg: AppColors.dangerSoft, text: AppColors.danger);
      case StatusTone.violet:
        return (bg: AppColors.violetSoft, text: AppColors.violet);
      case StatusTone.neutral:
        return (bg: const Color(0xFFEFEDE8), text: const Color(0xFF555555));
    }
  }

  @override
  Widget build(BuildContext context) {
    final c = _colors();
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: c.bg,
        borderRadius: BorderRadius.circular(AppRadii.full),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (checked) ...[
            Text('✓', style: TextStyle(color: c.text, fontSize: 11, fontWeight: FontWeight.w700)),
            const SizedBox(width: 5),
          ],
          Text(
            label,
            style: TextStyle(color: c.text, fontSize: 12, fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }
}
