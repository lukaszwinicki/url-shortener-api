'use client';

import { useState } from 'react';
import { urlsApi } from '@/lib/api/urls';
import { handleApiError } from '@/lib/api/client';
import type { UrlOutput } from '@/types/api';

interface CreateUrlFormProps {
  onSuccess?: () => void;
}

export function CreateUrlForm({ onSuccess }: CreateUrlFormProps) {
  const [originalUrl, setOriginalUrl] = useState('');
  const [customAlias, setCustomAlias] = useState('');
  const [isPrivate, setIsPrivate] = useState(false);
  const [hasExpiration, setHasExpiration] = useState(false);
  const [expiration, setExpiration] = useState<'1h' | '1d' | '1w'>('1d');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [createdUrl, setCreatedUrl] = useState<UrlOutput | null>(null);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setCreatedUrl(null);
    setIsSubmitting(true);

    try {
      const response = await urlsApi.create({
        originalUrl,
        customAlias: customAlias || undefined,
        visibility: isPrivate ? 'private' : 'public',
        expiration: hasExpiration ? expiration : undefined
      });

      setCreatedUrl(response.data);
      setOriginalUrl('');
      setCustomAlias('');
      onSuccess?.();
    } catch (err) {
      const result = handleApiError(err);
      setError(result?.message || 'Failed to create short URL');
    } finally {
      setIsSubmitting(false);
    }
  };

  const shortUrl = createdUrl?.shortCode
    ? `${process.env.NEXT_PUBLIC_SHORT_URL_BASE}/${createdUrl.shortCode}`
    : '';

  return (
    <div>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label htmlFor="url" className="block text-sm font-medium text-gray-300 mb-1">
            URL
          </label>
          <input
            id="url"
            type="url"
            value={originalUrl}
            onChange={(e) => setOriginalUrl(e.target.value)}
            placeholder="https://example.com/very-long-url"
            required
            className="w-full px-3 py-2 bg-[#222] border border-[#333] text-white rounded focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-500"
          />
        </div>

        <div>
          <label htmlFor="alias" className="block text-sm font-medium text-gray-300 mb-1">
            Custom alias (optional, 6-8 chars)
          </label>
          <input
            id="alias"
            type="text"
            value={customAlias}
            onChange={(e) => setCustomAlias(e.target.value)}
            placeholder="mylink"
            maxLength={8}
            className="w-full px-3 py-2 bg-[#222] border border-[#333] text-white rounded focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-500"
          />
        </div>

        <div className="flex items-center">
          <input
            id="private"
            type="checkbox"
            checked={isPrivate}
            onChange={(e) => setIsPrivate(e.target.checked)}
            className="w-4 h-4 bg-[#222] border border-[#333] rounded cursor-pointer accent-purple-500"
          />
          <label htmlFor="private" className="ml-2 text-sm font-medium text-gray-300 cursor-pointer">
            {isPrivate ? 'Private' : 'Public'}
          </label>
        </div>

        <div className="flex items-center">
          <input
            id="expiration"
            type="checkbox"
            checked={hasExpiration}
            onChange={(e) => setHasExpiration(e.target.checked)}
            className="w-4 h-4 bg-[#222] border border-[#333] rounded cursor-pointer accent-purple-500"
          />
          <label htmlFor="expiration" className="ml-2 text-sm font-medium text-gray-300 cursor-pointer">
            Set expiration
          </label>
        </div>

        {hasExpiration && (
          <div>
            <label htmlFor="expirationType" className="block text-sm font-medium text-gray-300 mb-1">
              Expires in
            </label>
            <select
              id="expirationType"
              value={expiration}
              onChange={(e) => setExpiration(e.target.value as '1h' | '1d' | '1w')}
              className="w-full px-3 py-2 bg-[#222] border border-[#333] text-white rounded focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
              <option value="1h">1 hour</option>
              <option value="1d">1 day</option>
              <option value="1w">1 week</option>
            </select>
          </div>
        )}

        <button
          type="submit"
          disabled={isSubmitting}
          className="w-full px-4 py-2 bg-gradient-to-r from-[#667eea] to-[#764ba2] text-white rounded hover:opacity-90 disabled:from-gray-700 disabled:to-gray-700 disabled:cursor-not-allowed transition-all"
        >
          {isSubmitting ? 'Creating...' : 'Shorten URL'}
        </button>
      </form>

      {error && (
        <div className="mt-4 p-3 bg-red-950/50 border border-red-800 rounded text-red-300 text-sm">
          {error}
        </div>
      )}

      {createdUrl && (
        <div className="mt-4 p-4 bg-[#222] border border-[#333] rounded">
          <p className="text-sm font-medium text-green-400 mb-2">Short URL created!</p>
          <a
            href={shortUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="text-[#667eea] hover:text-[#764ba2] hover:underline font-mono break-all"
          >
            {shortUrl}
          </a>
        </div>
      )}
    </div>
  );
}
