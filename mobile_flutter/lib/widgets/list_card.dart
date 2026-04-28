import 'package:flutter/material.dart';
import '../theme/colors.dart';

class ListCard extends StatelessWidget {
  final String title;
  final String? topRight;
  final String? subtitleA;
  final String? subtitleB;
  final String? amount;
  final Color? amountColor;
  final VoidCallback? onTap;

  const ListCard({
    super.key,
    required this.title,
    this.topRight,
    this.subtitleA,
    this.subtitleB,
    this.amount,
    this.amountColor,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final body = Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(AppRadii.lg),
        boxShadow: AppShadows.soft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    color: AppColors.text,
                    letterSpacing: -0.2,
                  ),
                  textAlign: TextAlign.right,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              if (topRight != null) ...[
                const SizedBox(width: AppSpacing.md),
                Text(
                  topRight!,
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textSecondary,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    if (subtitleA != null)
                      Text(subtitleA!,
                          textAlign: TextAlign.right,
                          style: const TextStyle(fontSize: 12, color: AppColors.textMuted, height: 1.5)),
                    if (subtitleB != null)
                      Text(subtitleB!,
                          textAlign: TextAlign.right,
                          style: const TextStyle(fontSize: 12, color: AppColors.textMuted, height: 1.5)),
                  ],
                ),
              ),
              if (amount != null) ...[
                const SizedBox(width: AppSpacing.md),
                Text(
                  amount!,
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    color: amountColor ?? AppColors.text,
                    letterSpacing: -0.2,
                  ),
                ),
              ],
            ],
          ),
        ],
      ),
    );

    if (onTap == null) return body;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppRadii.lg),
        child: body,
      ),
    );
  }
}
