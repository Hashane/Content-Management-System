import { apiClient } from './client';
import type { AdminRole } from '../types';

export async function fetchRoles(): Promise<AdminRole[]> {
  const response = await apiClient.get<{ data: AdminRole[] }>('/admin/roles');
  return response.data.data;
}

export async function syncRolePrivileges(roleId: number, privileges: string[]): Promise<AdminRole> {
  const response = await apiClient.patch<{ data: AdminRole }>(`/admin/roles/${roleId}/privileges`, { privileges });
  return response.data.data;
}
