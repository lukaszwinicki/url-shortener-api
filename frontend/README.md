# URL Shortener Frontend

Minimalistic frontend for URL shortening service.

**Stack**: Next.js 15, React 19, TypeScript, Tailwind CSS, SWR, Zustand

## Development

### Local
```bash
npm install
npm run dev
```

### Or using Docker Compose
From the project root:
```bash
docker-compose up -d frontend
```

Open http://localhost:3000

## Environment

```env
NEXT_PUBLIC_API_URL=http://localhost:8080
NEXT_PUBLIC_SHORT_URL_BASE=http://localhost:8080
```

## Build

```bash
npm run build
npm run start
```

Docker standalone build configured in `next.config.ts`.
