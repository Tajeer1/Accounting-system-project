import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Constants from 'expo-constants';

const API_URL = Constants.expoConfig?.extra?.apiUrl ?? 'http://192.168.1.10:8000/api';

export const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 20000,
});

api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (r) => r,
  async (error) => {
    if (error.response?.status === 401) {
      await AsyncStorage.multiRemove(['auth_token', 'auth_user']);
    }
    return Promise.reject(error);
  }
);

export const endpoints = {
  login: '/login',
  logout: '/logout',
  me: '/me',
  dashboard: '/dashboard',
  purchases: '/purchases',
  purchase: (id) => `/purchases/${id}`,
  invoices: '/invoices',
  invoice: (id) => `/invoices/${id}`,
  invoiceMarkPaid: (id) => `/invoices/${id}/mark-paid`,
  bankAccounts: '/bank-accounts',
  projects: '/projects',
  categories: '/categories',
  chartOfAccounts: '/chart-of-accounts',
};

export function apiErrorMessage(err) {
  if (err.response?.data?.message) return err.response.data.message;
  if (err.response?.data?.errors) {
    const firstKey = Object.keys(err.response.data.errors)[0];
    return err.response.data.errors[firstKey]?.[0] ?? 'حدث خطأ';
  }
  if (err.message) return err.message;
  return 'حدث خطأ في الاتصال';
}
