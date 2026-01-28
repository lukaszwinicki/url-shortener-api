'use client';

import { useUrls } from '@/hooks/use-urls';
import { urlsApi } from '@/lib/api/urls';
import { handleApiError } from '@/lib/api/client';
import { useState } from 'react';

export function MyUrlsList() {
  const { urls, isLoading, refresh } = useUrls();
  const [deletingId, setDeletingId] = useState<number | null>(null);

  const handleDelete = async (id: number) => {
    if (!confirm('Delete this URL?')) return;

    setDeletingId(id);
    try {
      await urlsApi.delete(id);
      refresh();
    } catch (err) {
      handleApiError(err);
    } finally {
      setDeletingId(null);
    }
  };

  const getShortUrl = (shortCode: string | null) =>
    shortCode ? `${process.env.NEXT_PUBLIC_SHORT_URL_BASE}/${shortCode}` : '';

  if (isLoading) {
    return <p className="text-gray-400">Loading...</p>;
  }

  if (!urls || urls.length === 0) {
    return <p className="text-gray-400">No URLs yet. Create your first one above.</p>;
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full border-collapse">
        <thead>
          <tr>
            <th className="text-left py-2 px-2 text-gray-300">Short URL</th>
            <th className="text-left py-2 px-2 text-gray-300">Original URL</th>
            <th className="text-right py-2 px-2 text-gray-300">Clicks</th>
            <th className="text-right py-2 px-2 text-gray-300">Action</th>
          </tr>
        </thead>
        <tbody>
          {urls.map((url) => (
            <tr key={url.id} className="hover:bg-[#222]">
              <td className="py-2 px-2">
                <a
                  href={getShortUrl(url.shortCode)}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-[#667eea] hover:text-[#764ba2] hover:underline font-mono text-sm"
                >
                  {url.shortCode || 'N/A'}
                </a>
              </td>
              <td className="py-2 px-2">
                <a
                  href={url.originalUrl || '#'}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-gray-400 hover:text-gray-300 hover:underline text-sm truncate block max-w-md"
                >
                  {url.originalUrl || 'N/A'}
                </a>
              </td>
              <td className="py-2 px-2 text-right text-sm text-gray-300">{url.clickCount}</td>
              <td className="py-2 px-2 text-right">
                <button
                  onClick={() => url.id && handleDelete(url.id)}
                  disabled={deletingId === url.id || !url.id}
                  className="text-red-400 hover:text-red-300 text-sm disabled:opacity-50"
                >
                  {deletingId === url.id ? 'deleting...' : 'delete'}
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
