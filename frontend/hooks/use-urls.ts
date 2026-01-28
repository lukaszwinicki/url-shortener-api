import useSWR from 'swr';
import { urlsApi, publicApi } from '@/lib/api/urls';
import type { UrlOutput } from '@/types/api';

export function useUrls() {
  const { data, error, mutate, isLoading } = useSWR<UrlOutput[]>(
    '/api/urls',
    () => urlsApi.list().then((res) => res.data),
    {
      refreshInterval: 30000,
      revalidateOnFocus: true,
      revalidateOnReconnect: true
    }
  );

  return {
    urls: data,
    isLoading,
    isError: error,
    mutate,
    refresh: mutate
  };
}

export function usePublicUrls() {
  const { data, error, mutate, isLoading } = useSWR<UrlOutput[]>(
    '/api/public',
    () => publicApi.listPublicUrls().then((res) => res.data),
    {
      refreshInterval: 60000,
      revalidateOnFocus: true
    }
  );

  return {
    urls: data,
    isLoading,
    isError: error,
    refresh: mutate
  };
}

export function useUrl(id: number | null) {
  const { data, error, mutate, isLoading } = useSWR<UrlOutput>(
    id ? `/api/urls/${id}` : null,
    id ? () => urlsApi.getById(id).then((res) => res.data) : null
  );

  return {
    url: data,
    isLoading,
    isError: error,
    refresh: mutate
  };
}
