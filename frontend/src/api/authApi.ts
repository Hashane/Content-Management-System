import { apiClient, authClient } from './client';
import type { AuthUser } from '../types';

export async function login(email: string, password: string): Promise<AuthUser> {
  await authClient.get('/sanctum/csrf-cookie');
  await apiClient.post('/login', { email, password });
  return fetchMe();
}

export async function logout(): Promise<void> {
  await apiClient.post('/logout');
}

export async function fetchMe(): Promise<AuthUser> {
  const response = await apiClient.get<{ data: AuthUser }>('/me');
  return response.data.data;
}
