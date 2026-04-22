<?php
/**
 * Front-end performance: drop unused core emoji, block library, and theme.json output.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Remove Twemoji / emoji scripts and styles (front and admin).
 *
 * @return void
 */
function custom_theme_disable_wp_emoji(): void {
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'custom_theme_disable_wp_emoji', 1 );

/**
 * Check if current request needs block assets (Header/Footer templates use blocks).
 *
 * @return bool True if blocks are needed on this page.
 */
function custom_theme_needs_block_assets(): bool {
  // Always load in admin
  if ( is_admin() ) {
    return true;
  }

  // Always load for Block Templates (Header/Footer use blocks).
  // Since header/footer are on every page, we need block assets everywhere.
  return true;
}

/**
 * Remove core block library CSS, `enqueue_block_assets` chain, global styles, and classic theme styles on the front end only.
 *
 * NOTE: Currently disabled because Header/Footer Block Templates need block assets.
 * If you want to re-enable optimization, modify custom_theme_needs_block_assets() to be more selective.
 *
 * @return void
 */
function custom_theme_optimize_remove_front_core_block_and_theme_styles(): void {
  if ( is_admin() ) {
    return;
  }

  // Don't remove block assets if we need them.
  if ( custom_theme_needs_block_assets() ) {
    return;
  }

  remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );
  remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );
  remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
  remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
  remove_action( 'wp_enqueue_scripts', 'wp_enqueue_stored_styles' );
  remove_action( 'wp_footer', 'wp_enqueue_stored_styles', 1 );
}
add_action( 'init', 'custom_theme_optimize_remove_front_core_block_and_theme_styles', 1 );
