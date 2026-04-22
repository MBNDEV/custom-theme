<?php
/**
 * Custom HTML injection: Preset Options (global) and per-entry (posts, pages) fields.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Map slot name to field names (post meta + theme option).
 * Updated to work with native WordPress options/meta
 *
 * @return array<string, array{post: string, global: string}>
 */
function custom_theme_custom_html_slot_field_map(): array {
  return array(
	  'head'        => array(
		  'post'   => '_blgf_post_html_head',
		  'global' => 'blgf_global_html_head',
	  ),
	  'before_body' => array(
		  'post'   => '_blgf_post_html_before_body',
		  'global' => 'blgf_global_html_before_body',
	  ),
	  'after_body'  => array(
		  'post'   => '_blgf_post_html_after_body',
		  'global' => 'blgf_global_html_after_body',
	  ),
	  'footer'      => array(
		  'post'   => '_blgf_post_html_footer',
		  'global' => 'blgf_global_html_footer',
	  ),
  );
}

/**
 * Merged HTML for the current request: singular post/page value if set, otherwise global theme option.
 * Updated to work with native WordPress options/meta
 *
 * @param string $slot One of head, before_body, after_body, footer.
 * @return string Raw HTML; empty string when unset.
 */
function custom_theme_get_merged_custom_html( string $slot ): string {
  $map = custom_theme_custom_html_slot_field_map();
  if ( ! isset( $map[ $slot ] ) ) {
    return '';
  }

  $keys = $map[ $slot ];

  // Check for post-specific HTML first (overrides global)
  if ( is_singular( array( 'post', 'page' ) ) ) {
    $post_id = (int) get_queried_object_id();
    if ( $post_id > 0 ) {
      $local = get_post_meta( $post_id, $keys['post'], true );
      if ( is_string( $local ) && '' !== trim( $local ) ) {
        return $local;
      }
    }
  }

  // Fall back to global theme option
  $global = get_option( $keys['global'], '' );

  return is_string( $global ) ? $global : '';
}

/**
 * Echo merged HTML for a slot when non-empty.
 *
 * @param string $slot Slot key.
 * @return void
 */
function custom_theme_print_merged_custom_html( string $slot ): void {
  $html = custom_theme_get_merged_custom_html( $slot );
  if ( '' === trim( $html ) ) {
    return;
  }

  echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw HTML from trusted editors.
}

/**
 * Output Head slot in wp_head.
 *
 * @return void
 */
function custom_theme_print_custom_html_head(): void {
  custom_theme_print_merged_custom_html( 'head' );
}

/**
 * Output Before Body slot on wp_body_open.
 *
 * @return void
 */
function custom_theme_print_custom_html_before_body(): void {
  custom_theme_print_merged_custom_html( 'before_body' );
}

/**
 * Output After Body slot before wp_footer.
 *
 * @return void
 */
function custom_theme_print_custom_html_after_body(): void {
  custom_theme_print_merged_custom_html( 'after_body' );
}

/**
 * Output Footer slot at the start of wp_footer.
 *
 * @return void
 */
function custom_theme_print_custom_html_footer(): void {
  custom_theme_print_merged_custom_html( 'footer' );
}

add_action( 'wp_head', 'custom_theme_print_custom_html_head', 100 );
add_action( 'wp_body_open', 'custom_theme_print_custom_html_before_body', 100 );
add_action( 'custom_theme_after_body', 'custom_theme_print_custom_html_after_body', 10 );
add_action( 'wp_footer', 'custom_theme_print_custom_html_footer', 1 );
