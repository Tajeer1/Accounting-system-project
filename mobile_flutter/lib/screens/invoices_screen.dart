import 'package:flutter/material.dart';
import '../api/client.dart';
import '../theme/colors.dart';
import '../utils/format.dart';
import '../widgets/list_card.dart';
import '../widgets/status_pill.dart';
import 'grouped_list_screen.dart';

class InvoicesScreen extends StatelessWidget {
  const InvoicesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return GroupedListScreen(
      title: 'الفواتير',
      emoji: '🧾',
      endpoint: Endpoints.invoices,
      typeFilters: const [
        MapEntry('', 'الكل'),
        MapEntry('sales', 'مبيعات'),
        MapEntry('purchase', 'مشتريات'),
      ],
      groupOrder: const [
        MapEntry('sent', (label: 'مرسلة', tone: StatusTone.info)),
        MapEntry('paid', (label: 'مدفوعة', tone: StatusTone.success)),
        MapEntry('draft', (label: 'مسودات', tone: StatusTone.neutral)),
        MapEntry('overdue', (label: 'متأخرة', tone: StatusTone.danger)),
        MapEntry('cancelled', (label: 'ملغاة', tone: StatusTone.neutral)),
      ],
      groupKey: (item) => item['status']?.toString() ?? 'draft',
      builder: (inv) {
        return ListCard(
          title: inv['party_name']?.toString() ?? '',
          topRight: formatDateShort(inv['issue_date']?.toString()),
          subtitleA: inv['number']?.toString(),
          subtitleB: inv['type_label']?.toString(),
          amount: formatMoney(inv['amount'] as num? ?? 0),
          amountColor: inv['type'] == 'sales' ? AppColors.success : AppColors.danger,
        );
      },
    );
  }
}
