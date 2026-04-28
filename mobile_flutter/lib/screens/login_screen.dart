import 'package:flutter/material.dart';
import '../auth/auth_state.dart';
import '../theme/colors.dart';
import '../widgets/app_button.dart';
import '../widgets/app_input.dart';
import '../widgets/status_pill.dart';
import '../api/client.dart';

class LoginScreen extends StatefulWidget {
  final AuthState auth;
  const LoginScreen({super.key, required this.auth});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _email = TextEditingController(text: 'admin@eventplus.com');
  final _password = TextEditingController(text: 'password');
  bool _loading = false;

  Future<void> _submit() async {
    if (_email.text.isEmpty || _password.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('أدخل البريد وكلمة المرور')),
      );
      return;
    }
    setState(() => _loading = true);
    final res = await widget.auth.login(_email.text, _password.text);
    if (!mounted) return;
    setState(() => _loading = false);
    if (!res.ok) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res.message ?? 'فشل تسجيل الدخول')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            children: [
              const SizedBox(height: AppSpacing.xxxl),
              Container(
                width: 96, height: 96,
                decoration: BoxDecoration(
                  color: AppColors.primary,
                  borderRadius: BorderRadius.circular(AppRadii.xl),
                ),
                clipBehavior: Clip.antiAlias,
                child: Image.asset('assets/icon.png', fit: BoxFit.cover),
              ),
              const SizedBox(height: AppSpacing.md),
              const Text('Event Plus',
                  style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.text, letterSpacing: -0.5)),
              const SizedBox(height: 4),
              const Text('نظام إداري محاسبي',
                  style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
              const SizedBox(height: AppSpacing.md),
              const StatusPill(label: 'آمن ومحمي', tone: StatusTone.success),
              const SizedBox(height: AppSpacing.xxxl),
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
                    const Text('تسجيل الدخول',
                        textAlign: TextAlign.right,
                        style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.text, letterSpacing: -0.3)),
                    const SizedBox(height: 4),
                    const Text('أهلاً بك مجددًا 👋',
                        textAlign: TextAlign.right,
                        style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
                    const SizedBox(height: AppSpacing.xl),
                    AppInput(
                      label: 'البريد الإلكتروني',
                      controller: _email,
                      keyboardType: TextInputType.emailAddress,
                      hint: 'you@example.com',
                    ),
                    AppInput(
                      label: 'كلمة المرور',
                      controller: _password,
                      obscureText: true,
                      hint: '••••••••',
                    ),
                    const SizedBox(height: AppSpacing.sm),
                    AppButton(
                      label: 'دخول',
                      onPressed: _submit,
                      loading: _loading,
                      size: ButtonSize.lg,
                      fullWidth: true,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.lg),
              Text('API: $kApiUrl',
                  style: const TextStyle(fontSize: 10, color: AppColors.textSubtle, fontFamily: 'monospace')),
            ],
          ),
        ),
      ),
    );
  }
}
