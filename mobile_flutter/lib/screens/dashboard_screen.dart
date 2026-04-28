import 'package:flutter/material.dart';
import '../api/client.dart';
import '../auth/auth_state.dart';
import '../theme/colors.dart';
import '../utils/format.dart';
import '../widgets/empty_state.dart';
import '../widgets/list_card.dart';
import '../widgets/status_pill.dart';

class DashboardScreen extends StatefulWidget {
  final AuthState auth;
  const DashboardScreen({super.key, required this.auth});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final res = await ApiClient().dio.get(Endpoints.dashboard);
      if (!mounted) return;
      setState(() {
        _data = res.data as Map<String, dynamic>;
        _loading = false;
        _error = null;
      });
    } catch (err) {
      if (!mounted) return;
      setState(() {
        _error = apiErrorMessage(err);
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: AppColors.primary));
    }
    if (_error != null) {
      return Center(child: Padding(padding: const EdgeInsets.all(20),
          child: Text(_error!, style: const TextStyle(color: AppColors.danger), textAlign: TextAlign.center)));
    }

    final stats = (_data?['stats'] as Map?) ?? {};
    final bankAccounts = (_data?['bank_accounts'] as List?) ?? [];
    final purchases = (_data?['latest_purchases'] as List?) ?? [];
    final invoices = (_data?['latest_invoices'] as List?) ?? [];
    final user = widget.auth.user ?? {};

    return RefreshIndicator(
      onRefresh: _load,
      color: AppColors.primary,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, 120),
        children: [
          // Topbar
          Padding(
            padding: const EdgeInsets.only(bottom: AppSpacing.lg),
            child: Row(
              children: [
                Container(
                  width: 38, height: 38,
                  decoration: BoxDecoration(color: AppColors.primary, borderRadius: BorderRadius.circular(AppRadii.md)),
                  clipBehavior: Clip.antiAlias,
                  child: Image.asset('assets/icon.png', fit: BoxFit.cover),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      const Text('مرحبًا', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                      const SizedBox(height: 2),
                      Text(user['name']?.toString() ?? 'مستخدم',
                          style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: AppColors.text)),
                    ],
                  ),
                ),
                const SizedBox(width: AppSpacing.md),
                CircleAvatar(
                  radius: 20,
                  backgroundColor: AppColors.primary,
                  child: Text(
                    (user['name']?.toString() ?? 'م').characters.first,
                    style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w700),
                  ),
                ),
              ],
            ),
          ),

          // Balance card
          Container(
            padding: const EdgeInsets.all(AppSpacing.xxl),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(AppRadii.xxl),
              boxShadow: AppShadows.card,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text('إجمالي الأرصدة',
                    textAlign: TextAlign.right,
                    style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
                const SizedBox(height: 8),
                Text(
                  formatMoney(stats['total_balance'] as num? ?? 0),
                  textAlign: TextAlign.right,
                  style: const TextStyle(fontSize: 30, fontWeight: FontWeight.w800, color: AppColors.text, letterSpacing: -1),
                ),
                const SizedBox(height: AppSpacing.xl),
                Container(
                  padding: const EdgeInsets.only(top: AppSpacing.lg),
                  decoration: const BoxDecoration(
                    border: Border(top: BorderSide(color: AppColors.borderLight)),
                  ),
                  child: Row(
                    children: [
                      _miniStat('مبيعات', shortMoney(stats['sales_invoices_total'] as num?), AppColors.success),
                      _divider(),
                      _miniStat('مشتريات', shortMoney(stats['purchase_invoices_total'] as num?), AppColors.danger),
                      _divider(),
                      _miniStat('مشاريع', '${stats['active_projects'] ?? 0}', AppColors.info),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: AppSpacing.xl),

          // Bank accounts
          const Padding(
            padding: EdgeInsets.only(bottom: AppSpacing.md, right: 4),
            child: Align(alignment: Alignment.centerRight, child: StatusPill(label: 'الحسابات', tone: StatusTone.success)),
          ),
          if (bankAccounts.isEmpty)
            const EmptyState(title: 'لا توجد حسابات', icon: '🏦')
          else
            ...bankAccounts.map((b) => Padding(
                  padding: const EdgeInsets.only(bottom: AppSpacing.sm),
                  child: ListCard(
                    title: b['name']?.toString() ?? '',
                    topRight: 'متاح',
                    subtitleA: b['type_label']?.toString(),
                    subtitleB: b['currency']?.toString(),
                    amount: formatMoney(b['current_balance'] as num? ?? 0),
                  ),
                )),

          // Latest purchases
          if (purchases.isNotEmpty) ...[
            const SizedBox(height: AppSpacing.xl),
            const Padding(
              padding: EdgeInsets.only(bottom: AppSpacing.md, right: 4),
              child: Align(alignment: Alignment.centerRight, child: StatusPill(label: 'آخر المشتريات', tone: StatusTone.warning)),
            ),
            ...purchases.map((p) => Padding(
                  padding: const EdgeInsets.only(bottom: AppSpacing.sm),
                  child: ListCard(
                    title: p['supplier_name']?.toString() ?? '',
                    topRight: formatDateShort(p['purchase_date']?.toString()),
                    subtitleA: p['number']?.toString(),
                    subtitleB: (p['category'] ?? p['project'] ?? '—').toString(),
                    amount: formatMoney(p['amount'] as num? ?? 0),
                    amountColor: AppColors.danger,
                  ),
                )),
          ],

          // Latest invoices
          if (invoices.isNotEmpty) ...[
            const SizedBox(height: AppSpacing.xl),
            const Padding(
              padding: EdgeInsets.only(bottom: AppSpacing.md, right: 4),
              child: Align(alignment: Alignment.centerRight, child: StatusPill(label: 'آخر الفواتير', tone: StatusTone.info)),
            ),
            ...invoices.map((inv) => Padding(
                  padding: const EdgeInsets.only(bottom: AppSpacing.sm),
                  child: ListCard(
                    title: inv['party_name']?.toString() ?? '',
                    topRight: inv['status_label']?.toString(),
                    subtitleA: inv['number']?.toString(),
                    subtitleB: inv['type_label']?.toString(),
                    amount: formatMoney(inv['amount'] as num? ?? 0),
                    amountColor: inv['type'] == 'sales' ? AppColors.success : AppColors.danger,
                  ),
                )),
          ],
        ],
      ),
    );
  }

  Widget _miniStat(String label, String value, Color color) {
    return Expanded(
      child: Column(
        children: [
          Text(label, style: const TextStyle(fontSize: 10, color: AppColors.textMuted)),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: color)),
        ],
      ),
    );
  }

  Widget _divider() => Container(width: 1, height: 28, color: AppColors.borderLight);
}
