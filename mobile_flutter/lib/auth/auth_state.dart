import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api/client.dart';

class AuthState extends ChangeNotifier {
  Map<String, dynamic>? _user;
  String? _token;
  bool _loading = true;

  Map<String, dynamic>? get user => _user;
  String? get token => _token;
  bool get loading => _loading;
  bool get isAuthenticated => _token != null && _token!.isNotEmpty;

  AuthState() {
    _hydrate();
  }

  Future<void> _hydrate() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    final userStr = prefs.getString('auth_user');
    if (userStr != null) {
      try {
        _user = jsonDecode(userStr) as Map<String, dynamic>;
      } catch (_) {}
    }
    _loading = false;
    notifyListeners();
  }

  Future<({bool ok, String? message})> login(String email, String password) async {
    try {
      final res = await ApiClient().dio.post(Endpoints.login, data: {
        'email': email,
        'password': password,
        'device_name': 'flutter',
      });
      final data = res.data as Map<String, dynamic>;
      _token = data['token'] as String;
      _user = data['user'] as Map<String, dynamic>;

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', _token!);
      await prefs.setString('auth_user', jsonEncode(_user));

      notifyListeners();
      return (ok: true, message: null);
    } catch (err) {
      return (ok: false, message: apiErrorMessage(err));
    }
  }

  Future<void> logout() async {
    try {
      await ApiClient().dio.post(Endpoints.logout);
    } catch (_) {}

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('auth_user');
    _token = null;
    _user = null;
    notifyListeners();
  }
}
