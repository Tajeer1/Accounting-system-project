import 'package:flutter/material.dart';
import '../theme/colors.dart';

enum ButtonVariant { primary, secondary, danger, ghost }
enum ButtonSize { sm, md, lg }

class AppButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final ButtonVariant variant;
  final ButtonSize size;
  final bool loading;
  final IconData? icon;
  final bool fullWidth;

  const AppButton({
    super.key,
    required this.label,
    this.onPressed,
    this.variant = ButtonVariant.primary,
    this.size = ButtonSize.md,
    this.loading = false,
    this.icon,
    this.fullWidth = false,
  });

  ({Color bg, Color text, Color border}) _colors() {
    switch (variant) {
      case ButtonVariant.primary:
        return (bg: AppColors.primary, text: Colors.white, border: Colors.transparent);
      case ButtonVariant.secondary:
        return (bg: AppColors.card, text: AppColors.text, border: AppColors.border);
      case ButtonVariant.danger:
        return (bg: AppColors.danger, text: Colors.white, border: Colors.transparent);
      case ButtonVariant.ghost:
        return (bg: Colors.transparent, text: AppColors.textSecondary, border: Colors.transparent);
    }
  }

  ({double v, double h, double fontSize}) _padding() {
    switch (size) {
      case ButtonSize.sm:
        return (v: 10, h: 14, fontSize: 12);
      case ButtonSize.md:
        return (v: 14, h: 18, fontSize: 14);
      case ButtonSize.lg:
        return (v: 16, h: 22, fontSize: 15);
    }
  }

  @override
  Widget build(BuildContext context) {
    final c = _colors();
    final p = _padding();
    final isDisabled = loading || onPressed == null;

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: isDisabled ? null : onPressed,
        borderRadius: BorderRadius.circular(AppRadii.full),
        child: Container(
          width: fullWidth ? double.infinity : null,
          padding: EdgeInsets.symmetric(vertical: p.v, horizontal: p.h),
          decoration: BoxDecoration(
            color: c.bg,
            borderRadius: BorderRadius.circular(AppRadii.full),
            border: Border.all(color: c.border, width: 1),
          ),
          child: Opacity(
            opacity: isDisabled ? 0.5 : 1,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: fullWidth ? MainAxisSize.max : MainAxisSize.min,
              children: [
                if (loading)
                  SizedBox(
                    width: 16, height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2, color: c.text),
                  )
                else ...[
                  if (icon != null) ...[
                    Icon(icon, size: 16, color: c.text),
                    const SizedBox(width: 8),
                  ],
                  Text(label, style: TextStyle(color: c.text, fontSize: p.fontSize, fontWeight: FontWeight.w600, letterSpacing: -0.1)),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
