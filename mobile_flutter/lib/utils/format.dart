import 'package:intl/intl.dart';

const String _currencySymbol = 'ر.ع';
const int _currencyDecimals = 3;

const _arabicMonths = [
  'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
  'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
];

String formatMoney(num? amount) {
  final n = amount ?? 0;
  final fmt = NumberFormat('#,##0.${'0' * _currencyDecimals}', 'en');
  return '${fmt.format(n)} $_currencySymbol';
}

String shortMoney(num? amount) {
  final n = (amount ?? 0).toDouble();
  final abs = n.abs();
  if (abs >= 1000000) {
    return '${(n / 1000000).toStringAsFixed(1)}م $_currencySymbol';
  }
  if (abs >= 1000) {
    return '${(n / 1000).toStringAsFixed(1)}ك $_currencySymbol';
  }
  final fmt = NumberFormat('#,##0', 'en');
  return '${fmt.format(n.round())} $_currencySymbol';
}

String formatDate(String? dateStr) {
  if (dateStr == null || dateStr.isEmpty) return '—';
  try {
    final d = DateTime.parse(dateStr);
    return '${d.day} ${_arabicMonths[d.month - 1]} ${d.year}';
  } catch (_) {
    return '—';
  }
}

String formatDateShort(String? dateStr) {
  if (dateStr == null || dateStr.isEmpty) return '—';
  try {
    final d = DateTime.parse(dateStr);
    return '${d.day} ${_arabicMonths[d.month - 1]}';
  } catch (_) {
    return '—';
  }
}
