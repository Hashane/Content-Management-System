import { apiClient } from './client';
import type { AdminMenuItemNode, MenuItemType } from '../types';

export interface MenuItemPayload {
  label: string;
  item_type: MenuItemType;
  page_id: number | null;
  parent_id?: number | null;
}

export async function fetchMenuTree(): Promise<AdminMenuItemNode[]> {
  const response = await apiClient.get<{ data: AdminMenuItemNode[] }>('/admin/menu/tree');
  return response.data.data;
}

export async function createMenuItem(payload: MenuItemPayload): Promise<AdminMenuItemNode> {
  const response = await apiClient.post<{ data: AdminMenuItemNode }>('/admin/menu/items', payload);
  return response.data.data;
}

export async function deleteMenuItem(id: number): Promise<void> {
  await apiClient.delete(`/admin/menu/items/${id}`);
}

export async function moveMenuItem(id: number, parentId: number | null, position: number): Promise<AdminMenuItemNode[]> {
  const response = await apiClient.patch<{ data: AdminMenuItemNode[] }>(`/admin/menu/items/${id}/move`, {
    parent_id: parentId,
    position,
  });
  return response.data.data;
}
