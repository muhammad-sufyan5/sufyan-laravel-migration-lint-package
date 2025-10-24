// @ts-check
// Docusaurus configuration for Laravel Migration Linter Docs

import { themes as prismThemes } from "prism-react-renderer";

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: "Laravel Migration Linter",
  tagline: "Lint and analyze Laravel migrations before they break production.",
  favicon: "img/favicon.ico",

  // Base URLs
  url: "https://muhammad-sufyan5.github.io",
  baseUrl: "/sufyan-laravel-migration-lint-package/",

  // Deployment (GitHub Pages)
  organizationName: "muhammad-sufyan5", // GitHub username
  projectName: "sufyan-laravel-migration-lint-package", // Repo name
  deploymentBranch: "gh-pages",
  trailingSlash: false,

  // Link handling
  onBrokenLinks: "throw",
  onBrokenMarkdownLinks: "warn",

  // Localization
  i18n: {
    defaultLocale: "en",
    locales: ["en"],
  },

  // Presets
  presets: [
    [
      "classic",
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          routeBasePath: "/", // make docs the homepage
          sidebarPath: "./sidebars.js",
          editUrl:
            "https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/tree/main/docs-site/",
        },
        blog: false, // disable blog for a clean docs-only setup
        theme: {
          customCss: "./src/css/custom.css",
        },
      }),
    ],
  ],

  // Theme configuration
  themeConfig: {
    // Metadata for SEO
    metadata: [
      {
        name: "keywords",
        content: "Laravel, migration, linter, database, CI/CD, PHP",
      },
      { name: "author", content: "Muhammad Sufyan" },
    ],

    // Navbar
    navbar: {
      title: "Laravel Migration Linter",
      logo: {
        alt: "Linter Logo",
        src: "img/logo.svg",
      },
      items: [
        { to: '/', label: 'Docs', position: 'left' },
        {
          href: "https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package",
          label: "GitHub",
          position: "right",
        },
      ],
    },

    // Footer
    footer: {
      style: "dark",
      links: [
        {
          title: "Documentation",
          items: [
            { label: "Introduction", to: "/" },
            { label: "Installation", to: "/installation" },
            { label: "Usage", to: "/usage" },
            { label: "Configuration", to: "/configuration" },
          ],
        },
        {
          title: "Community",
          items: [
            {
              label: "GitHub Discussions",
              href: "https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/discussions",
            },
            {
              label: "Issues",
              href: "https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/issues",
            },
          ],
        },
      ],
      copyright: `© ${new Date().getFullYear()} Sufyan — MIT License.`,
    },

    // Prism (code highlighting)
    prism: {
      theme: prismThemes.github,
      darkTheme: prismThemes.dracula,
      additionalLanguages: ["php", "bash", "yaml"],
    },

    // Color mode toggle
    colorMode: {
      defaultMode: "light",
      disableSwitch: false,
      respectPrefersColorScheme: true,
    },
  },

  // Future flag for Docusaurus v4
  future: {
    v4: true,
  },
};

export default config;
