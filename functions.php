<?php
/**
 * Custom Theme functions and setup.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
	require_once get_theme_file_path( 'vendor/autoload.php' );
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

PucFactory::buildUpdateChecker(
  'https://github.com/MBNDEV/custom-theme',
  get_theme_file_path( 'style.css' ),
  'custom-theme'
);
