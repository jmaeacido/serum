import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://www.serum72.com',
  output: 'static',
  redirects: {
    '/shop/day-cream': '/shop/fortify-day-cream',
    '/shop/serum': '/shop/radiance-serum',
    '/shop/dual-action-cleanser': '/shop/premium-cleanser',
    '/shop/mineral-sunscreen': '/shop'
  },
  integrations: [sitemap()]
});
