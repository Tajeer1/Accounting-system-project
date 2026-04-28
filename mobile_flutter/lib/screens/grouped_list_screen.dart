import 'package:flutter/material.dart';
import '../api/client.dart';
import '../theme/colors.dart';
import '../widgets/empty_state.dart';
import '../widgets/list_card.dart';
import '../widgets/status_pill.dart';

/// Reusable grouped list (used by Purchases & Invoices) — group by status.
class GroupedListScreen extends StatefulWidget {
  final String title;
  final String emoji;
  final String endpoint;
  final List<MapEntry<String, ({String label, StatusTone tone})>> groupOrder;
  final ListCard Function(Map item) builder;
  final String Function(Map item) groupKey;
  final List<MapEntry<String, String>>? typeFilters; // [(value, label)]

  const GroupedListScreen({
    super.key,
    required this.title,
    required this.emoji,
    required this.endpoint,
    required this.groupOrder,
    required this.builder,
    required this.groupKey,
    this.typeFilters,
  });

  @override
  State<GroupedListScreen> createState() => _GroupedListScreenState();
}

class _GroupedListScreenState extends State<GroupedListScreen> {
  List _items = [];
  bool _loading = true;
  String _filter = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final res = await ApiClient().dio.get(
        widget.endpoint,
        queryParameters: _filter.isEmpty ? null : {'type': _filter},
      );
      final data = res.data;
      List items = [];
      if (data is Map) {
        // For invoices: data.data.data
        if (data['data'] is Map && data['data']['data'] is List) {
          items = data['data']['data'] as List;
        } else if (data['data'] is List) {
          items = data['data'] as List;
        }
      } else if (data is List) {
        items = data;
      }
      if (!mounted) return;
      setState(() {
        _items = items;
        _loading = false;
      });
    } catch (err) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  Map<String, List> _grouped() {
    final groups = <String, List>{};
    for (final item in _items) {
      final key = widget.groupKey(item as Map);
      (groups[key] ??= []).add(item);
    }
    return groups;
  }

  @override
  Widget build(BuildContext context) {
    final groups = _grouped();
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: SafeArea(
        child: Column(
          children: [
            // Top bar
            Padding(
              padding: const EdgeInsets.fromLTRB(AppSpacing.xl, AppSpacing.md, AppSpacing.xl, AppSpacing.md),
              child: Row(
                children: [
                  Expanded(
                    child: Text(widget.title,
                        textAlign: TextAlign.right,
                        style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.text, letterSpacing: -0.5)),
                  ),
                  Container(
                    width: 40, height: 40,
                    decoration: BoxDecoration(color: AppColors.primary, borderRadius: BorderRadius.circular(AppRadii.full)),
                    child: const Icon(Icons.add, color: Colors.white),
                  ),
                ],
              ),
            ),

            if (widget.typeFilters != null)
              Padding(
                padding: const EdgeInsets.fromLTRB(AppSpacing.xl, 0, AppSpacing.xl, AppSpacing.md),
                child: Row(
                  children: widget.typeFilters!
                      .map((f) => Padding(
                            padding: const EdgeInsets.only(left: AppSpacing.sm),
                            child: _filterChip(f.key, f.value),
                          ))
                      .toList(),
                ),
              ),

            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                  : RefreshIndicator(
                      onRefresh: _load,
                      color: AppColors.primary,
                      child: _items.isEmpty
                          ? ListView(children: [
                              EmptyState(title: 'لا توجد عناصر', subtitle: 'أضف أول عنصر', icon: widget.emoji),
                            ])
                          : ListView(
                              padding: const EdgeInsets.fromLTRB(AppSpacing.lg, 0, AppSpacing.lg, 120),
                              children: widget.groupOrder
                                  .where((g) => groups[g.key]?.isNotEmpty == true)
                                  .expand((group) sync* {
                                    yield Padding(
                                      padding: const EdgeInsets.only(bottom: AppSpacing.md, right: 4),
                                      child: Align(
                                        alignment: Alignment.centerRight,
                                        child: StatusPill(label: group.value.label, tone: group.value.tone),
                                      ),
                                    );
                                    yield* groups[group.key]!.map((item) => Padding(
                                          padding: const EdgeInsets.only(bottom: AppSpacing.sm),
                                          child: widget.builder(item as Map),
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

  Widget _filterChip(String value, String label) {
    final active = _filter == value;
    return GestureDetector(
      onTap: () {
        setState(() => _filter = value);
        _load();
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: 8),
        decoration: BoxDecoration(
          color: active ? AppColors.primary : AppColors.card,
          borderRadius: BorderRadius.circular(AppRadii.full),
        ),
        child: Text(label,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: active ? Colors.white : AppColors.textSecondary,
            )),
      ),
    );
  }
}
