import 'package:flutter/material.dart';
import '../api/client.dart';
import '../theme/colors.dart';
import '../utils/format.dart';
import '../widgets/list_card.dart';
import '../widgets/status_pill.dart';
import 'grouped_list_screen.dart';

class PurchasesScreen extends StatelessWidget {
  const PurchasesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return GroupedListScreen(
      title: 'المشتريات',
      emoji: '🛒',
      endpoint: Endpoints.purchases,
      groupOrder: const [
        MapEntry('paid', (label: 'مدفوعة', tone: StatusTone.success)),
        MapEntry('pending', (label: 'معلقة', tone: StatusTone.warning)),
        MapEntry('cancelled', (label: 'ملغاة', tone: StatusTone.neutral)),
      ],
      groupKey: (item) => item['status']?.toString() ?? 'pending',
      builder: (p) {
        final cat = p['category'];
        final proj = p['project'];
        final subtitleB = (cat is Map ? cat['name'] : null) ??
            (proj is Map ? proj['name'] : null) ??
            '—';
        return ListCard(
          title: p['supplier_name']?.toString() ?? '',
          topRight: formatDateShort(p['purchase_date']?.toString()),
          subtitleA: p['number']?.toString(),
          subtitleB: subtitleB.toString(),
          amount: formatMoney(p['amount'] as num? ?? 0),
          amountColor: AppColors.danger,
        );
      },
    );
  }
}
