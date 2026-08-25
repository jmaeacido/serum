import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://www.serum72.com',
  output: 'static',
  integrations: [sitemap()]
});
