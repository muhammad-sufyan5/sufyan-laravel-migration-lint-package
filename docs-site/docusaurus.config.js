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
          routeBasePath: "/docs", // make docs the homepage
          sidebarPath: "./sidebars.js",
          editUrl:
            "https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/tree/main/docs-site/",
          showLastUpdateTime: true,
          showLastUpdateAuthor: true,
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
    onBrokenLinks: "warn", // instead of 'throw'
    onBrokenMarkdownLinks: "warn",

    // Metadata for SEO
    metadata: [
      {
        name: "description",
        content:
          "Laravel Migration Linter — a lightweight tool to analyze and prevent risky Laravel migration changes before production.",
      },
      {
        name: "keywords",
        content:
          "Laravel, migrations, linter, PHP, CI/CD, database, schema, sufyandev",
      },
      { property: "og:title", content: "Laravel Migration Linter" },
      {
        property: "og:description",
        content:
          "Analyze your database migrations before they break production.",
      },
      { property: "og:image", content: "img/logo.png" },
      { property: "twitter:card", content: "summary_large_image" },
    ],

    // Navbar
    navbar: {
      title: "Laravel Migration Linter",
      logo: {
        alt: "Linter Logo",
        src: "img/logo.jpeg",
      },
      items: [
        { to: "/docs/", label: "Docs", position: "left" },
        { type: 'search', position: 'left', className: 'navbar-search-centered' },
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
            { label: "Introduction", to: "/docs/" },
            { label: "Installation", to: "/docs/installation" },
            { label: "Usage", to: "/docs/usage" },
            { label: "Configuration", to: "/docs/configuration" },
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

  plugins: [
    [
      require.resolve("@easyops-cn/docusaurus-search-local"),
      {
        hashed: true,
        indexDocs: true,
        docsRouteBasePath: "/docs",
        highlightSearchTermsOnTargetPage: true,
        searchResultLimits: 10,
        indexBlog: false,
      language: 'en',
      // 👇 This forces search index generation during `npm run start`
      docsDir: 'docs',
      },
    ],
  ],

  // Future flag for Docusaurus v4
  future: {
    v4: true,
  },
};

export default config;
