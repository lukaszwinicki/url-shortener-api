'use client';

import { useEffect } from 'react';
import { SWRConfig } from 'swr';
import { useSessionStore } from '@/store/session-store';

export function Providers({ children }: { children: React.ReactNode }) {
  const initialize = useSessionStore((state) => state.initialize);
  const isInitialized = useSessionStore((state) => state.isInitialized);

  useEffect(() => {
    initialize();
  }, [initialize]);

  if (!isInitialized) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-[#0f0f0f]">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto mb-4"></div>
          <p className="text-gray-400">Initializing...</p>
        </div>
      </div>
    );
  }

  return (
    <SWRConfig
      value={{
        revalidateOnFocus: true,
        revalidateOnReconnect: true,
        shouldRetryOnError: false,
        dedupingInterval: 2000
      }}
    >
      {children}
    </SWRConfig>
  );
}
