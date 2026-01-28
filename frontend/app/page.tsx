'use client';

import { useState } from 'react';
import { CreateUrlForm } from '@/components/create-url-form';
import { MyUrlsList } from '@/components/my-urls-list';
import { PublicUrlsList } from '@/components/public-urls-list';

export default function Home() {
  const [refreshKey, setRefreshKey] = useState(0);

  const handleUrlCreated = () => {
    setRefreshKey(prev => prev + 1);
  };

  return (
    <div className="max-w-4xl mx-auto p-6">
      <header className="mb-16 pb-8 text-center">
        <h1 className="text-5xl font-bold gradient-text tracking-tight mb-3">ShortURL</h1>
        <p className="text-gray-400 text-lg">Create short links, track clicks</p>
      </header>

      <main>
        <CreateUrlForm onSuccess={handleUrlCreated} />

        <div className="mt-12">
          <h2 className="text-xl font-bold text-white mb-4">Your URLs</h2>
          <MyUrlsList key={refreshKey} />
        </div>

        <hr className="mt-12 mb-8 fade-border" />
        <div>
          <h2 className="text-xl font-bold text-white mb-4">Public URLs</h2>
          <PublicUrlsList />
        </div>
      </main>

      <hr className="mt-16 mb-8 fade-border" />
      <footer className="text-sm text-gray-500 text-center">
        <p>Free URL shortener with analytics • No registration required</p>
      </footer>
    </div>
  );
}
