import 'package:flutter/material.dart';
import '../auth/auth_state.dart';
import '../theme/colors.dart';
import 'dashboard_screen.dart';
import 'purchases_screen.dart';
import 'invoices_screen.dart';
import 'projects_screen.dart';
import 'more_screen.dart';

class MainShell extends StatefulWidget {
  final AuthState auth;
  const MainShell({super.key, required this.auth});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _index = 0;

  late final List<Widget> _pages = [
    DashboardScreen(auth: widget.auth),
    const PurchasesScreen(),
    const InvoicesScreen(),
    const ProjectsScreen(),
    MoreScreen(auth: widget.auth),
  ];

  static const _tabs = [
    (label: 'الرئيسية', icon: Icons.home_outlined, activeIcon: Icons.home),
    (label: 'مشتريات', icon: Icons.shopping_cart_outlined, activeIcon: Icons.shopping_cart),
    (label: 'فواتير', icon: Icons.receipt_long_outlined, activeIcon: Icons.receipt_long),
    (label: 'مشاريع', icon: Icons.work_outline, activeIcon: Icons.work),
    (label: 'المزيد', icon: Icons.more_horiz, activeIcon: Icons.more_horiz),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      extendBody: true,
      body: SafeArea(
        bottom: false,
        child: IndexedStack(index: _index, children: _pages),
      ),
      bottomNavigationBar: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, AppSpacing.sm),
          child: Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(AppRadii.full),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0A0A0A).withValues(alpha: 0.08),
                  offset: const Offset(0, 6),
                  blurRadius: 16,
                ),
              ],
            ),
            child: Row(
              children: List.generate(_tabs.length, (i) {
                final tab = _tabs[i];
                final isActive = _index == i;
                return Expanded(
                  child: _TabItem(
                    label: tab.label,
                    icon: isActive ? tab.activeIcon : tab.icon,
                    isActive: isActive,
                    onTap: () => setState(() => _index = i),
                  ),
                );
              }),
            ),
          ),
        ),
      ),
    );
  }
}

class _TabItem extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool isActive;
  final VoidCallback onTap;

  const _TabItem({
    required this.label,
    required this.icon,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppRadii.full),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          height: 42,
          padding: EdgeInsets.symmetric(horizontal: isActive ? 16 : 0),
          decoration: BoxDecoration(
            color: isActive ? AppColors.primary : Colors.transparent,
            borderRadius: BorderRadius.circular(AppRadii.full),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: isActive ? 18 : 22, color: isActive ? Colors.white : AppColors.textSecondary),
              if (isActive) ...[
                const SizedBox(width: 6),
                Text(label, style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w700)),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
