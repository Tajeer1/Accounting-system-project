import 'package:flutter/material.dart';
import '../api/client.dart';
import '../theme/colors.dart';
import '../utils/format.dart';
import '../widgets/empty_state.dart';
import '../widgets/status_pill.dart';

class ProjectsScreen extends StatefulWidget {
  const ProjectsScreen({super.key});
  @override
  State<ProjectsScreen> createState() => _ProjectsScreenState();
}

class _ProjectsScreenState extends State<ProjectsScreen> {
  List _items = [];
  bool _loading = true;

  static const _statusTones = {
    'planned': StatusTone.info,
    'in_progress': StatusTone.warning,
    'completed': StatusTone.success,
    'cancelled': StatusTone.neutral,
  };
  static const _statusLabels = {
    'planned': 'مخططة',
    'in_progress': 'قيد التنفيذ',
    'completed': 'مكتملة',
    'cancelled': 'ملغاة',
  };
  static const _order = ['in_progress', 'planned', 'completed', 'cancelled'];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final res = await ApiClient().dio.get(Endpoints.projects);
      if (!mounted) return;
      setState(() {
        _items = res.data as List;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final groups = <String, List>{};
    for (final p in _items) {
      final key = p['status']?.toString() ?? 'planned';
      (groups[key] ??= []).add(p);
    }

    return Scaffold(
      backgroundColor: AppColors.bg,
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(AppSpacing.xl, AppSpacing.md, AppSpacing.xl, AppSpacing.md),
              child: Row(
                children: [
                  const Expanded(
                    child: Text('المشاريع',
                        textAlign: TextAlign.right,
                        style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.text, letterSpacing: -0.5)),
                  ),
                  Text('${_items.length}',
                      style: const TextStyle(fontSize: 14, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
                ],
              ),
            ),
            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                  : RefreshIndicator(
                      onRefresh: _load,
                      color: AppColors.primary,
                      child: _items.isEmpty
                          ? ListView(children: const [EmptyState(title: 'لا توجد مشاريع', icon: '💼')])
                          : ListView(
                              padding: const EdgeInsets.fromLTRB(AppSpacing.lg, 0, AppSpacing.lg, 120),
                              children: _order
                                  .where((s) => groups[s]?.isNotEmpty == true)
                                  .expand((s) sync* {
                                    yield Padding(
                                      padding: const EdgeInsets.only(bottom: AppSpacing.md, right: 4),
                                      child: Align(
                                        alignment: Alignment.centerRight,
                                        child: StatusPill(label: _statusLabels[s]!, tone: _statusTones[s]!),
                                      ),
                                    );
                                    yield* groups[s]!.map((p) => Padding(
                                          padding: const EdgeInsets.only(bottom: AppSpacing.md),
                                          child: _projectCard(p as Map),
                                        ));
                                    yield const SizedBox(height: AppSpacing.xl);
                                  })
                                  .toList(),
                            ),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _projectCard(Map p) {
    final contractValue = (p['contract_value'] as num?)?.toDouble() ?? 0;
    final revenue = (p['total_revenue'] as num?)?.toDouble() ?? 0;
    final progress = contractValue > 0 ? (revenue / contractValue).clamp(0.0, 1.0) : 0.0;
    final profit = (p['profit'] as num?)?.toDouble() ?? 0;
    final cost = (p['total_cost'] as num?)?.toDouble() ?? 0;
    final margin = (p['profit_margin'] as num?)?.toDouble() ?? 0;

    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(AppRadii.xl),
        boxShadow: AppShadows.soft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(p['code']?.toString() ?? '',
                        style: const TextStyle(fontSize: 10, color: AppColors.textMuted)),
                    const SizedBox(height: 2),
                    Text(p['name']?.toString() ?? '',
                        textAlign: TextAlign.right,
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.text, letterSpacing: -0.2)),
                    if (p['client_name'] != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 2),
                        child: Text(p['client_name']!.toString(),
                            style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                      ),
                  ],
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Text(shortMoney(contractValue),
                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.text)),
            ],
          ),
          const SizedBox(height: AppSpacing.lg),
          Container(
            padding: const EdgeInsets.only(top: AppSpacing.md),
            decoration: const BoxDecoration(border: Border(top: BorderSide(color: AppColors.borderLight))),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    const Text('الإيراد', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                    const Spacer(),
                    Text('${shortMoney(revenue)} / ${shortMoney(contractValue)}',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.text)),
                  ],
                ),
                const SizedBox(height: 6),
                ClipRRect(
                  borderRadius: BorderRadius.circular(2),
                  child: LinearProgressIndicator(
                    value: progress,
                    minHeight: 4,
                    backgroundColor: AppColors.borderLight,
                    color: AppColors.primary,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.lg),
          Row(
            children: [
              _stat('الربح', shortMoney(profit), profit >= 0 ? AppColors.success : AppColors.danger),
              _statDivider(),
              _stat('التكلفة', shortMoney(cost), AppColors.danger),
              _statDivider(),
              _stat('الهامش', '${margin.toStringAsFixed(1)}%', AppColors.text),
            ],
          ),
        ],
      ),
    );
  }

  Widget _stat(String label, String value, Color color) => Expanded(
        child: Column(
          children: [
            Text(label, style: const TextStyle(fontSize: 10, color: AppColors.textMuted)),
            const SizedBox(height: 2),
            Text(value, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: color)),
          ],
        ),
      );

  Widget _statDivider() => Container(width: 1, height: 24, color: AppColors.borderLight);
}
