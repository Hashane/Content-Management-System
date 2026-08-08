import { apiClient } from './client';
import type { AdminPage, PageStatus, PaginatedResponse } from '../types';

export interface PageListParams {
  search?: string;
  status?: PageStatus;
  page?: number;
}

export interface PagePayload {
  title: string;
  body_html: string;
  status: PageStatus;
  published_at: string | null;
  cover_image?: File | null;
}

function toFormData(payload: PagePayload): FormData {
  const formData = new FormData();
  formData.append('title', payload.title);
  formData.append('body_html', payload.body_html);
  formData.append('status', payload.status);

  if (payload.published_at) {
    formData.append('published_at', payload.published_at);
  }

  if (payload.cover_image) {
    formData.append('cover_image', payload.cover_image);
  }

  return formData;
}

export async function fetchPages(params: PageListParams = {}): Promise<PaginatedResponse<AdminPage>> {
  const response = await apiClient.get<{ data: PaginatedResponse<AdminPage> }>('/admin/pages', { params });
  return response.data.data;
}

export async function fetchPage(id: number): Promise<AdminPage> {
  const response = await apiClient.get<{ data: AdminPage }>(`/admin/pages/${id}`);
  return response.data.data;
}

export async function createPage(payload: PagePayload): Promise<AdminPage> {
  const response = await apiClient.post<{ data: AdminPage }>('/admin/pages', toFormData(payload));
  return response.data.data;
}

export async function updatePage(id: number, payload: PagePayload): Promise<AdminPage> {
  const formData = toFormData(payload);
  formData.append('_method', 'PUT');
  const response = await apiClient.post<{ data: AdminPage }>(`/admin/pages/${id}`, formData);
  return response.data.data;
}

export async function deletePage(id: number): Promise<void> {
  await apiClient.delete(`/admin/pages/${id}`);
}
