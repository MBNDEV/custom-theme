<?php
/**
 * Custom Theme functions and setup.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! defined( 'CUSTOM_THEME_SECTION_BG_TABLET_IMAGE_SIZE' ) ) {
	define( 'CUSTOM_THEME_SECTION_BG_TABLET_IMAGE_SIZE', 'section-bg-tablet' );
}
if ( ! defined( 'CUSTOM_THEME_SECTION_BG_MOBILE_IMAGE_SIZE' ) ) {
	define( 'CUSTOM_THEME_SECTION_BG_MOBILE_IMAGE_SIZE', 'section-bg-mobile' );
}

if ( ! class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
  require_once get_theme_file_path( 'vendor/autoload.php' );
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Load global button component
 */
require_once get_theme_file_path( 'template-parts/button.php' );

/**
 * Theme setup
 */
function blacklinesecurityops_theme_setup() {
	// Add support for block styles.
	add_theme_support( 'wp-block-styles' );

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );

	// Inject compiled Tailwind CSS intro the iframed block editor canvas.
	add_editor_style( 'assets/build/tailwind.css' );

	// Add support for responsive embedded content.
	add_theme_support( 'responsive-embeds' );

	// Add support for custom line height.
	add_theme_support( 'custom-line-height' );

	// Add support for custom spacing.
	add_theme_support( 'custom-spacing' );

	// Add support for custom units.
  add_theme_support( 'custom-units' );

  // Register navigation menus
  register_nav_menus(
    array(
		'primary-menu'  => __( 'Primary Menu', 'mbn-theme' ),
		'footer-menu'   => __( 'Footer Menu', 'mbn-theme' ),
		'footer-menu-1' => __( 'Footer Menu Column 1', 'mbn-theme' ),
		'footer-menu-2' => __( 'Footer Menu Column 2', 'mbn-theme' ),
		'footer-legal'  => __( 'Footer Legal Links', 'mbn-theme' ),
		'mobile-menu'   => __( 'Mobile Menu', 'mbn-theme' ),
	)
  );
}

add_action( 'after_setup_theme', 'blacklinesecurityops_theme_setup' );

// Load theme components.
require_once get_theme_file_path( 'block-registry.php' );
require_once get_theme_file_path( 'tailwind-loader.php' );
require_once get_theme_file_path( 'optimize.php' );

// Load integrated inc/ files.
require_once get_theme_file_path( 'inc/includes-theme-options.php' );          // Native theme options page.
require_once get_theme_file_path( 'inc/includes-post-meta.php' );              // Native post meta boxes.
require_once get_theme_file_path( 'inc/includes-theme-preset-options-render.php' ); // Font presets & CSS variables.
require_once get_theme_file_path( 'inc/includes-html-injection.php' );         // Custom HTML injection.
require_once get_theme_file_path( 'inc/includes-widget-loader.php' );          // Widget area auto-loader.
require_once get_theme_file_path( 'inc/includes-block-templates.php' );        // Block Templates (Header/Footer) system.
require_once get_theme_file_path( 'inc/includes-template-page-sync.php' );     // Page template sync.
require_once get_theme_file_path( 'inc/includes-theme-block-section.php' );    // Section background utilities.
require_once get_theme_file_path( 'inc/includes-block-patterns.php' );         // Reusable block patterns.
require_once get_theme_file_path( 'inc/includes-template-sync-tools.php' );    // Template import/export tools.
require_once get_theme_file_path( 'inc/includes-page-sync.php' );              // Page content sync (optional).

PucFactory::buildUpdateChecker(
  'https://github.com/MBNDEV/mbn-theme',
  get_theme_file_path( 'style.css' ),
  'mbn-theme'
);
