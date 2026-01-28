'use client';

import { usePublicUrls } from '@/hooks/use-urls';

export function PublicUrlsList() {
  const { urls, isLoading } = usePublicUrls();

  const getShortUrl = (shortCode: string | null) =>
    shortCode ? `${process.env.NEXT_PUBLIC_SHORT_URL_BASE}/${shortCode}` : '';

  if (isLoading) {
    return <p className="text-gray-400">Loading...</p>;
  }

  if (!urls || urls.length === 0) {
    return <p className="text-gray-400">No public URLs yet.</p>;
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full border-collapse">
        <thead>
          <tr>
            <th className="text-left py-2 px-2 text-gray-300">Short URL</th>
            <th className="text-left py-2 px-2 text-gray-300">Original URL</th>
            <th className="text-right py-2 px-2 text-gray-300">Clicks</th>
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
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
