import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:google_fonts/google_fonts.dart';

import 'auth/auth_state.dart';
import 'screens/login_screen.dart';
import 'screens/main_shell.dart';
import 'theme/colors.dart';

void main() {
  runApp(const EventPlusApp());
}

class EventPlusApp extends StatefulWidget {
  const EventPlusApp({super.key});
  @override
  State<EventPlusApp> createState() => _EventPlusAppState();
}

class _EventPlusAppState extends State<EventPlusApp> {
  final AuthState _auth = AuthState();

  @override
  void initState() {
    super.initState();
    _auth.addListener(() => setState(() {}));
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Event Plus',
      debugShowCheckedModeBanner: false,

      // Localization
      locale: const Locale('ar'),
      supportedLocales: const [Locale('ar'), Locale('en')],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],

      // RTL by default
      builder: (context, child) => Directionality(
        textDirection: TextDirection.rtl,
        child: child ?? const SizedBox.shrink(),
      ),

      theme: ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: AppColors.bg,
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.primary,
          surface: AppColors.card,
        ),
        textTheme: GoogleFonts.cairoTextTheme().apply(
          bodyColor: AppColors.text,
          displayColor: AppColors.text,
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: AppColors.bg,
          elevation: 0,
          foregroundColor: AppColors.text,
        ),
      ),

      home: _root(),
    );
  }

  Widget _root() {
    if (_auth.loading) {
      return const Scaffold(
        backgroundColor: AppColors.bg,
        body: Center(child: CircularProgressIndicator(color: AppColors.primary)),
      );
    }
    return _auth.isAuthenticated ? MainShell(auth: _auth) : LoginScreen(auth: _auth);
  }
}
