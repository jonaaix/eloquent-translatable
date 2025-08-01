// @ts-check
// `@type` JSDoc annotations allow editor autocompletion and type checking
// (when paired with `@ts-check`).
// There are various equivalent ways to declare your Docusaurus config.
// See: https://docusaurus.io/docs/api/docusaurus-config

import { themes as prismThemes } from 'prism-react-renderer';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

const isDeploy = process.env.NODE_ENV === 'production';

/** @type {import('@docusaurus/types').Config} */
const config = {
   title: 'Laravel Eloquent Translatable',
   tagline: 'High performance translations for Laravel models',
   favicon: 'img/logo2.png',

   // Future flags, see https://docusaurus.io/docs/api/docusaurus-config#future
   future: {
      v4: true, // Improve compatibility with the upcoming Docusaurus v4
   },

   // Set the production url of your site here
   url: 'https://jonaaix.github.io',
   // Set the /<baseUrl>/ pathname under which your site is served
   // For GitHub pages deployment, it is often '/<projectName>/'
   baseUrl: isDeploy ? '/eloquent-translatable/' : '/',

   // GitHub pages deployment config.
   // If you aren't using GitHub pages, you don't need these.
   organizationName: 'jonaaix', // Usually your GitHub org/user name.
   projectName: 'eloquent-translatable', // Usually your repo name.

   onBrokenLinks: 'throw',
   onBrokenMarkdownLinks: 'warn',

   // Even if you don't use internationalization, you can use this field to set
   // useful metadata like html lang. For example, if your site is Chinese, you
   // may want to replace "en" with "zh-Hans".
   i18n: {
      defaultLocale: 'en',
      locales: ['en'],
   },

   presets: [
      [
         'classic',
         /** @type {import('@docusaurus/preset-classic').Options} */
         ({
            docs: {
               sidebarPath: './sidebars.js',
               // Please change this to your repo.
               // Remove this to remove the "edit this page" links.
               editUrl: 'https://github.com/facebook/docusaurus/tree/main/packages/create-docusaurus/templates/shared/',
            },
            blog: false,
            // blog: {
            //   showReadingTime: true,
            //   feedOptions: {
            //     type: ['rss', 'atom'],
            //     xslt: true,
            //   },
            //   // Please change this to your repo.
            //   // Remove this to remove the "edit this page" links.
            //   editUrl:
            //     'https://github.com/facebook/docusaurus/tree/main/packages/create-docusaurus/templates/shared/',
            //   // Useful options to enforce blogging best practices
            //   onInlineTags: 'warn',
            //   onInlineAuthors: 'warn',
            //   onUntruncatedBlogPosts: 'warn',
            // },
            theme: {
               customCss: './src/css/custom.css',
            },
         }),
      ],
   ],

   themeConfig:
      /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
      ({
         // Replace with your project's social card
         image: 'img/docusaurus-social-card.jpg',
         colorMode: {
            disableSwitch: false,
            respectPrefersColorScheme: true,
         },
         navbar: {
            title: 'Laravel Eloquent Translatable',
            logo: {
               alt: 'Laravel Eloquent Translatable Logo',
               src: 'img/logo2.png',
            },
            items: [
               {
                  type: 'docSidebar',
                  sidebarId: 'tutorialSidebar',
                  position: 'left',
                  label: 'Documentation',
               },
               // { to: '/blog', label: 'Blog', position: 'left' },
               {
                  href: 'https://github.com/jonaaix/eloquent-translatable',
                  label: 'GitHub',
                  position: 'right',
               },
            ],
         },
         footer: {
            // style: 'dark',
            // links: [
            //    {
            //       title: 'Docs',
            //       items: [
            //          {
            //             label: 'Tutorial',
            //             to: '/docs/intro',
            //          },
            //       ],
            //    },
            //    {
            //       title: 'Community',
            //       items: [
            //          {
            //             label: 'Stack Overflow',
            //             href: 'https://stackoverflow.com/questions/tagged/docusaurus',
            //          },
            //          {
            //             label: 'Discord',
            //             href: 'https://discordapp.com/invite/docusaurus',
            //          },
            //          {
            //             label: 'X',
            //             href: 'https://x.com/docusaurus',
            //          },
            //       ],
            //    },
            //    {
            //       title: 'More',
            //       items: [
            //          // {
            //          //    label: 'Blog',
            //          //    to: '/blog',
            //          // },
            //          {
            //             label: 'GitHub',
            //             href: 'https://github.com/facebook/docusaurus',
            //          },
            //       ],
            //    },
            // ],
            copyright: `Copyright © ${new Date().getFullYear()} Laravel Eloquent Translatable`,
         },
         prism: {
            theme: prismThemes.github,
            darkTheme: prismThemes.palenight,
            additionalLanguages: ['php', 'bash', 'json'],
         },
      }),
};

export default config;
