# API Requests - Bruno Collection

Bruno API client collection for testing the URL Shortener API.

## Setup

1. Install Bruno from https://www.usebruno.com/
2. Open Collection → Select `api-requests/shorturl-api` folder
3. Select "local" environment

## Features

- Automatic JWT token management
- Auto-saved variables: `jwtToken`, `lastShortCode`, `lastUrlId`
- Environment-based configuration

## Available Requests

**Session**
- `1. Create Session` - Generate JWT token
- `2. Get Session` - View current session info

**URLs**
- `1. Create Short URL` - Basic URL creation
- `2. Create with Custom Alias` - URL with custom alias and expiration
- `3. List My URLs` - Paginated list of session URLs
- `4. Get URL Stats` - Click analytics
- `5. Delete URL` - Soft delete
- `6. Create Private URL` - Private visibility URL

**Public**
- `1. List Public URLs` - Browse all public URLs
- `2. Test Redirect` - Test short URL redirect
