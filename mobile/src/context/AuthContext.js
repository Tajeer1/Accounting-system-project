import React, { createContext, useContext, useEffect, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { api, endpoints, apiErrorMessage } from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      try {
        const [[, savedToken], [, savedUser]] = await AsyncStorage.multiGet(['auth_token', 'auth_user']);
        if (savedToken) {
          setToken(savedToken);
          setUser(savedUser ? JSON.parse(savedUser) : null);
        }
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  async function login(email, password) {
    try {
      console.log('[login] posting to:', api.defaults.baseURL + endpoints.login);
      const { data } = await api.post(endpoints.login, {
        email,
        password,
        device_name: 'mobile',
      });
      console.log('[login] success:', data.user?.email);
      await AsyncStorage.multiSet([
        ['auth_token', data.token],
        ['auth_user', JSON.stringify(data.user)],
      ]);
      setToken(data.token);
      setUser(data.user);
      return { ok: true };
    } catch (err) {
      console.log('[login] FAIL');
      console.log('  status:', err.response?.status);
      console.log('  data:', JSON.stringify(err.response?.data));
      console.log('  message:', err.message);
      console.log('  code:', err.code);
      return { ok: false, message: apiErrorMessage(err) };
    }
  }

  async function logout() {
    try { await api.post(endpoints.logout); } catch {}
    await AsyncStorage.multiRemove(['auth_token', 'auth_user']);
    setToken(null);
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{ user, token, loading, login, logout, isAuthenticated: !!token }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
