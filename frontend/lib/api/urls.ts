import { apiClient } from './client';
import type { CreateUrlInput, UrlOutput, UrlStats, SessionResponse } from '@/types/api';

export const sessionApi = {
  create: () => apiClient.post<SessionResponse>('/api/session'),
  getCurrent: () => apiClient.get<SessionResponse>('/api/session')
};

export const urlsApi = {
  create: (data: CreateUrlInput) =>
    apiClient.post<UrlOutput>('/api/urls', data),
  list: () => apiClient.get<UrlOutput[]>('/api/urls'),
  getById: (id: number) => apiClient.get<UrlOutput>(`/api/urls/${id}`),
  getStats: (id: number) => apiClient.get<UrlStats>(`/api/urls/${id}/stats`),
  delete: (id: number) => apiClient.delete(`/api/urls/${id}`)
};

export const publicApi = {
  listPublicUrls: () => apiClient.get<UrlOutput[]>('/api/public')
};
