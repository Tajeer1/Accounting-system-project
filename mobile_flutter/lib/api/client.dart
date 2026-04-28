import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String kApiUrl = 'http://localhost:8000/api';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late final Dio dio;

  ApiClient._internal() {
    dio = Dio(BaseOptions(
      baseUrl: kApiUrl,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      connectTimeout: const Duration(seconds: 20),
      receiveTimeout: const Duration(seconds: 20),
    ));

    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString('auth_token');
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (err, handler) async {
        if (err.response?.statusCode == 401) {
          final prefs = await SharedPreferences.getInstance();
          await prefs.remove('auth_token');
          await prefs.remove('auth_user');
        }
        return handler.next(err);
      },
    ));
  }
}

class Endpoints {
  static const login = '/login';
  static const logout = '/logout';
  static const me = '/me';
  static const dashboard = '/dashboard';
  static const purchases = '/purchases';
  static const invoices = '/invoices';
  static const bankAccounts = '/bank-accounts';
  static const projects = '/projects';
  static const categories = '/categories';
}

String apiErrorMessage(Object err) {
  if (err is DioException) {
    final data = err.response?.data;
    if (data is Map) {
      if (data['message'] is String) return data['message'];
      if (data['errors'] is Map) {
        final errors = data['errors'] as Map;
        if (errors.isNotEmpty) {
          final first = errors.values.first;
          if (first is List && first.isNotEmpty) return first.first.toString();
        }
      }
    }
    if (err.type == DioExceptionType.connectionTimeout ||
        err.type == DioExceptionType.receiveTimeout) {
      return 'انتهت مهلة الاتصال — تحقّق من السيرفر والشبكة';
    }
    if (err.type == DioExceptionType.connectionError) {
      return 'فشل الاتصال بالسيرفر';
    }
    return err.message ?? 'حدث خطأ في الاتصال';
  }
  return err.toString();
}
