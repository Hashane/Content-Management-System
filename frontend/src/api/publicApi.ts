import { apiClient } from './client';
import type { MenuItemNode, PublicPage } from '../types';

export async function fetchMenu(): Promise<MenuItemNode[]> {
  const response = await apiClient.get<{ data: MenuItemNode[] }>('/public/menu');
  return response.data.data;
}

export async function fetchPageBySlug(slug: string): Promise<PublicPage> {
  const response = await apiClient.get<{ data: PublicPage }>(`/public/pages/${slug}`);
  return response.data.data;
}
