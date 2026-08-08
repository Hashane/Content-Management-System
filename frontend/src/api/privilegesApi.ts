import { apiClient } from './client';
import type { Privilege } from '../types';

export async function fetchPrivileges(): Promise<Privilege[]> {
  const response = await apiClient.get<{ data: Privilege[] }>('/admin/privileges');
  return response.data.data;
}
