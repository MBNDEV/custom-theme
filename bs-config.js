/**
 * BrowserSync configuration for WordPress theme development
 * Hot reload for PHP, CSS, and JS files
 */

module.exports = {
  proxy: 'mybizniche.test', // Change to your local WordPress URL
  files: [
    '**/*.php',
    'assets/build/**/*.css',
    'build/**/*.js',
    'blocks/**/*.php',
    'blocks-render/**/*.php',
    'template-parts/**/*.php',
  ],
  watchEvents: ['change', 'add', 'unlink', 'addDir', 'unlinkDir'],
  watchOptions: {
    ignoreInitial: true,
    ignored: [
      'node_modules/**',
      'vendor/**',
      '.git/**',
    ],
  },
  snippetOptions: {
    rule: {
      match: /<\/body>/i,
      fn: function (snippet, match) {
        return snippet + match;
      },
    },
  },
  reloadDelay: 100,
  reloadDebounce: 500,
  notify: true,
  open: false, // Set to true to auto-open browser
  ui: {
    port: 3001,
  },
  port: 3000,
};
