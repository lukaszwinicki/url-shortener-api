import axios from 'axios';
import type { SessionResponse } from '@/types/api';

const TOKEN_KEY = 'shorturl_jwt_token';
const EXPIRES_AT_KEY = 'shorturl_expires_at';

export class SessionManager {
  static async initialize(): Promise<SessionResponse | null> {
    if (typeof window === 'undefined') {
      return null;
    }

    const token = this.getToken();
    console.log('[SessionManager] Initialize - existing token:', token ? token.substring(0, 20) + '...' : 'none');

    if (!token || this.isTokenExpired()) {
      console.log('[SessionManager] Creating new session...');
      return await this.createSession();
    }

    console.log('[SessionManager] Using existing session');
    return {
      jwtToken: token,
      expiresAt: this.getExpiresAt() || '',
      createdAt: ''
    };
  }

  static async createSession(): Promise<SessionResponse> {
    console.log('[SessionManager] POST /api/session');
    const response = await axios.post<SessionResponse>(
      `${process.env.NEXT_PUBLIC_API_URL}/api/session`
    );

    console.log('[SessionManager] Full API response:', response.data);

    const session = response.data;

    console.log('[SessionManager] Session created:', {
      expiresAt: session.expiresAt,
      hasJwt: !!session.jwtToken
    });

    if (!session.jwtToken || !session.expiresAt) {
      throw new Error('Session response missing jwtToken or expiresAt');
    }

    this.setToken(session.jwtToken);
    this.setExpiresAt(session.expiresAt);

    return session;
  }

  static getToken(): string | null {
    if (typeof window === 'undefined') {
      return null;
    }
    const token = localStorage.getItem(TOKEN_KEY);
    if (!token || token === 'undefined' || token === 'null') {
      return null;
    }
    return token;
  }

  static setToken(token: string): void {
    if (typeof window === 'undefined') {
      return;
    }
    localStorage.setItem(TOKEN_KEY, token);
  }

  static getExpiresAt(): string | null {
    if (typeof window === 'undefined') {
      return null;
    }
    const expiresAt = localStorage.getItem(EXPIRES_AT_KEY);
    if (!expiresAt || expiresAt === 'undefined' || expiresAt === 'null') {
      return null;
    }
    return expiresAt;
  }

  static setExpiresAt(expiresAt: string): void {
    if (typeof window === 'undefined') {
      return;
    }
    localStorage.setItem(EXPIRES_AT_KEY, expiresAt);
  }

  static isTokenExpired(): boolean {
    if (typeof window === 'undefined') {
      return true;
    }

    const expiresAt = this.getExpiresAt();
    if (!expiresAt) {
      return true;
    }

    const expirationDate = new Date(expiresAt);
    const now = new Date();

    return now >= expirationDate;
  }

  static clearSession(): void {
    if (typeof window === 'undefined') {
      return;
    }

    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(EXPIRES_AT_KEY);
  }

  static getSessionInfo(): {
    token: string | null;
    expiresAt: string | null;
    isExpired: boolean;
  } {
    return {
      token: this.getToken(),
      expiresAt: this.getExpiresAt(),
      isExpired: this.isTokenExpired()
    };
  }
}
