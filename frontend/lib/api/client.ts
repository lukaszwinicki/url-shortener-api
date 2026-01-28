import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';
import { SessionManager } from '@/lib/auth/session-manager';
import { toast } from 'sonner';
import type { ApiError } from '@/types/api';

export const apiClient = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8080',
  headers: {
    'Content-Type': 'application/json'
  }
});

let isRefreshing = false;
let failedQueue: Array<{
  resolve: (value: unknown) => void;
  reject: (reason?: any) => void;
}> = [];

const processQueue = (error: Error | null = null) => {
  failedQueue.forEach((prom) => {
    if (error) {
      prom.reject(error);
    } else {
      prom.resolve(null);
    }
  });

  failedQueue = [];
};

apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const publicEndpoints = ['/api/session', '/api/public'];
    const isPublicEndpoint = publicEndpoints.some(endpoint =>
      config.url?.includes(endpoint)
    );

    const isRedirectEndpoint = /^\/[a-zA-Z0-9]{6,8}$/.test(config.url || '');

    if (!isPublicEndpoint && !isRedirectEndpoint) {
      const token = SessionManager.getToken();
      if (token && config.headers) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

apiClient.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error: AxiosError<ApiError>) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & {
      _retry?: boolean;
    };

    if (error.response?.status === 401 && !originalRequest._retry) {
      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject });
        })
          .then(() => {
            return apiClient(originalRequest);
          })
          .catch((err) => {
            return Promise.reject(err);
          });
      }

      originalRequest._retry = true;
      isRefreshing = true;

      try {
        const session = await SessionManager.createSession();

        if (session && originalRequest.headers) {
          originalRequest.headers.Authorization = `Bearer ${session.jwtToken}`;
        }

        processQueue(null);
        return apiClient(originalRequest);
      } catch (refreshError) {
        processQueue(refreshError as Error);
        SessionManager.clearSession();
        toast.error('Session expired. Please refresh the page.');
        return Promise.reject(refreshError);
      } finally {
        isRefreshing = false;
      }
    }

    if (error.response?.status === 429) {
      const retryAfter = error.response.headers['retry-after'] || '30';
      toast.error(`Rate limit exceeded. Please try again in ${retryAfter} seconds.`);
    }

    if (error.response?.status === 400) {
      const errorData = error.response.data;
      if (errorData?.detail) {
        toast.error(errorData.detail);
      } else if (errorData?.title) {
        toast.error(errorData.title);
      } else {
        toast.error('Validation error. Please check your input.');
      }
    }

    if (error.response?.status === 404) {
      toast.error('Resource not found.');
    }

    if (error.response?.status === 500) {
      toast.error('Server error. Please try again later.');
    }

    return Promise.reject(error);
  }
);

export function handleApiError(error: unknown) {
  if (axios.isAxiosError(error)) {
    const axiosError = error as AxiosError<ApiError>;

    if (axiosError.response?.status === 429) {
      const retryAfter = axiosError.response.headers['retry-after'] || '30';
      toast.error(`Rate limit exceeded. Try again in ${retryAfter}s`);
      return { retryAfter: parseInt(retryAfter), message: `Rate limit exceeded. Try again in ${retryAfter}s` };
    }

    if (axiosError.response?.status === 400) {
      const errorData = axiosError.response.data;
      const message = errorData?.detail || errorData?.title || 'Validation error';
      toast.error(message);
      return { message };
    }

    if (axiosError.response?.data?.title) {
      const message = axiosError.response.data.detail || axiosError.response.data.title;
      toast.error(message);
      return { message };
    }
  } else {
    toast.error('An unexpected error occurred');
    return { message: 'An unexpected error occurred' };
  }

  return null;
}
