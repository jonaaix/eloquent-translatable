import { defineConfig } from 'vitepress';

const isDeploy = process.env.DEPLOY === 'true';
const base = isDeploy ? '/eloquent-translatable/' : '/';

export default defineConfig({
   title: 'Laravel Eloquent Translatable',
   description: 'High performance translations for Laravel models',
   base,
   cleanUrls: true,
   lastUpdated: true,
   srcExclude: ['README.md'],

   head: [
      ['link', { rel: 'icon', type: 'image/png', href: `${base}img/logo2.png` }],
      ['link', { rel: 'apple-touch-icon', href: `${base}img/logo2.png` }],
      ['meta', { name: 'theme-color', content: '#3f3bff' }],
   ],

   themeConfig: {
      logo: '/img/logo2.png',

      nav: [
         { text: 'Documentation', link: '/docs/10-getting-started' },
      ],

      sidebar: {
         '/docs/': [
            {
               text: 'Documentation',
               items: [
                  { text: 'Getting Started', link: '/docs/10-getting-started' },
                  { text: 'Core Concepts', link: '/docs/20-core-concepts' },
                  { text: 'Access Translations', link: '/docs/30-access-translations' },
                  { text: 'Store Translations', link: '/docs/40-store-translations' },
                  { text: 'Delete Translations', link: '/docs/50-delete-translations' },
                  { text: 'Querying Translations', link: '/docs/55-querying-translations' },
                  { text: 'Locale Enum', link: '/docs/60-locale-enum' },
                  { text: 'Customization', link: '/docs/70-customization' },
                  { text: 'Separate Translation Database', link: '/docs/75-custom-database-connection' },
                  { text: 'Eloquent Relationship', link: '/docs/80-eloquent-relationship' },
                  { text: 'Troubleshooting', link: '/docs/90-troubleshooting' },
               ],
            },
         ],
      },

      socialLinks: [
         { icon: 'github', link: 'https://github.com/jonaaix/eloquent-translatable' },
      ],

      footer: {
         copyright: `Copyright © ${new Date().getFullYear()} Laravel Eloquent Translatable`,
      },

      search: {
         provider: 'local',
      },

      outline: {
         level: [2, 3],
      },
   },

   markdown: {
      theme: {
         light: 'github-light',
         dark: 'one-dark-pro',
      },
   },
});
