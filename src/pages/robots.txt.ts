import type { APIRoute } from 'astro';
export const GET: APIRoute = () => new Response('User-agent: *\nAllow: /\nSitemap: https://www.serum72.com/sitemap-index.xml\n', { headers: { 'Content-Type':'text/plain' } });
