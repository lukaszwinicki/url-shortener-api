export interface SessionResponse {
  createdAt: string;
  expiresAt: string;
  jwtToken: string | null;
}

export interface UrlOutput {
  id: number | null;
  shortCode: string | null;
  originalUrl: string | null;
  visibility: 'public' | 'private';
  expiresAt: string | null;
  createdAt: string | null;
  clickCount: number;
}

export interface CreateUrlInput {
  originalUrl: string;
  visibility?: 'public' | 'private';
  customAlias?: string;
  expiration?: '1h' | '1d' | '1w';
}

export interface UrlStats {
  urlId: number;
  shortCode: string;
  originalUrl: string;
  totalClicks: number;
  createdAt: string;
}

export interface ApiError {
  title: string;
  detail?: string;
  status: number;
  type: string;
  trace?: any[];
}
