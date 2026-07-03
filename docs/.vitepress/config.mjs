import { defineConfig } from 'vitepress'

// GitHub Pages serves a project site under /<repo>/. In CI the deploy workflow
// injects DOCS_BASE automatically from the Pages settings, so it always matches
// the real repo name (no need to hardcode it here). The '/' fallback is only used
// for local `docs:build` / `docs:preview`, which serve from the root.
const base = process.env.DOCS_BASE ?? '/'

export default defineConfig({
  base,
  lang: 'en-US',
  title: 'Digital Sports CRM',
  description:
    'Documentation for Digital Sports CRM — an open-source Laravel platform for federation management.',
  cleanUrls: true,
  lastUpdated: true,
  ignoreDeadLinks: true,

  // The docs are a standalone site. Override PostCSS inline so Vite does NOT
  // auto-discover the app's root postcss.config.js, which loads tailwind.config.js
  // and its Filament preset from the composer `vendor/` dir. The docs CI installs
  // only npm dependencies, so that preset isn't present there. (custom.css is plain
  // CSS and needs no PostCSS plugins.)
  vite: {
    css: {
      postcss: { plugins: [] },
    },
  },

  themeConfig: {
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Get Started', link: '/guides/getting-started' },
      { text: 'Using the Platform', link: '/using-the-platform/' },
      { text: 'Architecture', link: '/architecture/01-overview' },
      { text: 'Features', link: '/features/memberships' },
      { text: 'Access Control', link: '/access-control/federation-membership-rules' },
      { text: 'Guides', link: '/guides/development-style-guide' },
    ],

    sidebar: [
      {
        text: 'Using the Platform',
        collapsed: false,
        items: [
          { text: 'Overview', link: '/using-the-platform/' },
          { text: 'Admin Portal', link: '/using-the-platform/admin' },
          { text: 'Federation Portal', link: '/using-the-platform/federation' },
          { text: 'Club Portal', link: '/using-the-platform/club' },
          { text: 'Individual Portal', link: '/using-the-platform/individual' },
        ],
      },
      {
        text: 'Architecture',
        collapsed: false,
        items: [
          { text: 'Overview', link: '/architecture/01-overview' },
          { text: 'Committee Structure', link: '/architecture/02-committee-structure' },
          { text: 'Entity Relationships', link: '/architecture/03-diving-entity-relationships' },
          { text: 'Professionals Architecture', link: '/architecture/04-diving-professionals-architecture' },
        ],
      },
      {
        text: 'Features',
        collapsed: false,
        items: [
          { text: 'Memberships', link: '/features/memberships' },
          { text: 'Licenses', link: '/features/licenses' },
          { text: 'Certifications', link: '/features/certifications' },
          { text: 'Events', link: '/features/events' },
          { text: 'Event Applications', link: '/features/event-applications' },
          { text: 'Event Enrollment Roles', link: '/features/event-enrollment-roles' },
          { text: 'Event Reports', link: '/features/event-reports' },
          { text: 'Professional Licensing', link: '/features/diving-professionals' },
          { text: 'Payments', link: '/features/payments' },
          { text: 'Payment Webhooks', link: '/features/payment_webhook_implementation' },
          { text: 'Import System', link: '/features/import-system' },
          { text: 'Platform Utilities', link: '/features/platform-utilities' },
        ],
      },
      {
        text: 'Access Control',
        collapsed: false,
        items: [
          { text: 'Federation Membership Rules', link: '/access-control/federation-membership-rules' },
          { text: 'Role Management', link: '/access-control/role-management' },
          { text: 'Permission Management', link: '/access-control/permission-management' },
          { text: 'Individual Roles', link: '/access-control/individual-roles' },
          { text: 'Entity Roles', link: '/access-control/entity-roles' },
          { text: 'Federation License Permissions', link: '/access-control/federation-license-permissions' },
        ],
      },
      {
        text: 'Guides',
        collapsed: false,
        items: [
          { text: 'Getting Started', link: '/guides/getting-started' },
          { text: 'Configuring Committees', link: '/guides/configuring-committees' },
          { text: 'Navigation & Menus', link: '/guides/navigation-and-menus' },
          { text: 'Building Integrations', link: '/guides/building-integrations' },
          { text: 'Development Style Guide', link: '/guides/development-style-guide' },
          { text: 'Frontend Style Guide', link: '/guides/frontend-style-guide' },
          { text: 'Creating a Plugin', link: '/guides/creating-a-plugin' },
        ],
      },
      {
        text: 'Integrations',
        collapsed: true,
        items: [
          { text: 'EasyPay Integration', link: '/easypay_integration' },
        ],
      },
    ],

    search: { provider: 'local' },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/DigitalFederation/DigitalSportsCrm' },
    ],

    footer: {
      message: 'Released under the Apache 2.0 License.',
    },
  },
})
