/**
 * Sidebar configuration for Laravel Migration Linter Docs
 */
module.exports = {
  tutorialSidebar: [
    {
      type: 'category',
      label: 'Getting Started',
      collapsed: false,
      items: ['intro', 'installation', 'usage'],
    },
    {
      type: 'category',
      label: 'Configuration',
      items: ['configuration'],
    },
    {
      type: 'category',
      label: 'Linter Rules',
      items: ['rules', 'writing-custom-rules'],
    },
    {
      type: 'category',
      label: 'CI/CD Integration',
      items: ['ci-cd'],
    },
    {
      type: 'category',
      label: 'Changelog',
      items: ['changelog'],
    },
  ],
};
