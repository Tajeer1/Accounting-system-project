import 'package:flutter/material.dart';
import '../api/client.dart';
import '../auth/auth_state.dart';
import '../theme/colors.dart';
import '../utils/format.dart';
import '../widgets/app_button.dart';
import '../widgets/empty_state.dart';
import '../widgets/list_card.dart';
import '../widgets/status_pill.dart';

class MoreScreen extends StatefulWidget {
  final AuthState auth;
  const MoreScreen({super.key, required this.auth});
  @override
  State<MoreScreen> createState() => _MoreScreenState();
}

class _MoreScreenState extends State<MoreScreen> {
  List _accounts = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final res = await ApiClient().dio.get(Endpoints.bankAccounts);
      if (!mounted) return;
      setState(() {
        _accounts = res.data as List;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.card,
        title: const Text('تسجيل الخروج', textAlign: TextAlign.right),
        content: const Text('هل أنت متأكد؟', textAlign: TextAlign.right),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              widget.auth.logout();
            },
            child: const Text('خروج', style: TextStyle(color: AppColors.danger)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = widget.auth.user ?? {};
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, 120),
          children: [
            const Padding(
              padding: EdgeInsets.only(bottom: AppSpacing.lg, right: 4),
              child: Align(
                alignment: Alignment.centerRight,
                child: Text('المزيد',
                    style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.text, letterSpacing: -0.5)),
              ),
            ),

            // Profile card
            Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(AppRadii.xl),
                boxShadow: AppShadows.card,
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 27,
                    backgroundColor: AppColors.primary,
                    child: Text(
                      (user['name']?.toString() ?? 'م').characters.first,
                      style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w700),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(user['name']?.toString() ?? '',
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.text, letterSpacing: -0.2)),
                        const SizedBox(height: 2),
                        Text(user['email']?.toString() ?? '',
                            style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: AppSpacing.xl),
            const Padding(
              padding: EdgeInsets.only(bottom: AppSpacing.md, right: 4),
              child: Align(alignment: Alignment.centerRight, child: StatusPill(label: 'الحسابات البنكية', tone: StatusTone.success)),
            ),
            if (_loading)
              const Center(child: CircularProgressIndicator(color: AppColors.primary))
            else if (_accounts.isEmpty)
              const EmptyState(title: 'لا توجد حسابات', icon: '🏦')
            else
              ..._accounts.map((a) => Padding(
                    padding: const EdgeInsets.only(bottom: AppSpacing.sm),
                    child: ListCard(
                      title: a['name']?.toString() ?? '',
                      topRight: a['is_active'] == true ? 'نشط' : 'موقف',
                      subtitleA: a['type_label']?.toString(),
                      subtitleB: a['account_number']?.toString() ?? a['currency']?.toString(),
                      amount: formatMoney(a['current_balance'] as num? ?? 0),
                    ),
                  )),

            const SizedBox(height: AppSpacing.xl),
            AppButton(
              label: 'تسجيل الخروج',
              variant: ButtonVariant.secondary,
              onPressed: _confirmLogout,
              fullWidth: true,
            ),

            const SizedBox(height: AppSpacing.xl),
            const Center(
              child: Text('Event Plus · 1.0',
                  style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
            ),
          ],
        ),
      ),
    );
  }
}
