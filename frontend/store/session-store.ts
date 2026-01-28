import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import { SessionManager } from '@/lib/auth/session-manager';
import type { SessionResponse } from '@/types/api';

interface SessionState {
  token: string | null;
  expiresAt: string | null;
  isInitialized: boolean;
  setSession: (data: SessionResponse) => void;
  clearSession: () => void;
  initialize: () => Promise<void>;
}

export const useSessionStore = create<SessionState>()(
  persist(
    (set, get) => ({
      token: null,
      expiresAt: null,
      isInitialized: false,

      initialize: async () => {
        try {
          const session = await SessionManager.initialize();

          if (session) {
            set({
              token: session.jwtToken,
              expiresAt: session.expiresAt,
              isInitialized: true
            });
          } else {
            set({ isInitialized: true });
          }
        } catch (error) {
          console.error('Failed to initialize session:', error);
          set({ isInitialized: true });
        }
      },

      setSession: (data: SessionResponse) => {
        if (!data.jwtToken || !data.expiresAt) {
          throw new Error('Session response missing jwtToken or expiresAt');
        }

        SessionManager.setToken(data.jwtToken);
        SessionManager.setExpiresAt(data.expiresAt);

        set({
          token: data.jwtToken,
          expiresAt: data.expiresAt,
          isInitialized: true
        });
      },

      clearSession: () => {
        SessionManager.clearSession();

        set({
          token: null,
          expiresAt: null
        });
      }
    }),
    {
      name: 'shorturl-session',
      storage: createJSONStorage(() => localStorage),
      partialize: (state) => ({
        token: state.token,
        expiresAt: state.expiresAt
      })
    }
  )
);
