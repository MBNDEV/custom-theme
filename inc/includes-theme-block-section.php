<?php
/**
 * Shared block section: outer full-width background, text color, responsive images, and inner container.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Attachment URL for full-size background, or empty.
 *
 * @param int $attachment_id Attachment ID.
 * @return string
 */
function custom_theme_get_section_background_image_url( int $attachment_id ): string {
  if ( $attachment_id <= 0 ) {
    return '';
  }
  $url = wp_get_attachment_image_url( $attachment_id, 'full' );

  return is_string( $url ) ? $url : '';
}

/**
 * Register intermediate sizes used when tablet/mobile section images are not set (desktop image is reused).
 *
 * Existing uploads may not have these sizes until thumbnails are regenerated.
 *
 * @return void
 */
function custom_theme_register_section_background_image_sizes(): void {
  $tablet_w = (int) apply_filters( 'custom_theme_section_bg_tablet_max_width', 1024 );
  $mobile_w = (int) apply_filters( 'custom_theme_section_bg_mobile_max_width', 640 );

  add_image_size( CUSTOM_THEME_SECTION_BG_TABLET_IMAGE_SIZE, max( 1, $tablet_w ), 0 );
  add_image_size( CUSTOM_THEME_SECTION_BG_MOBILE_IMAGE_SIZE, max( 1, $mobile_w ), 0 );
}

add_action( 'after_setup_theme', 'custom_theme_register_section_background_image_sizes', 20 );

/**
 * Attachment URL for a named size, falling back to large then full if the size is missing.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $size_name     Registered size name.
 * @return string
 */
function custom_theme_get_section_background_sized_image_url( int $attachment_id, string $size_name ): string {
  if ( $attachment_id <= 0 || '' === $size_name ) {
    return '';
  }

  $url = wp_get_attachment_image_url( $attachment_id, $size_name );
  if ( is_string( $url ) && '' !== $url ) {
    return $url;
  }

  $large = wp_get_attachment_image_url( $attachment_id, 'large' );
  if ( is_string( $large ) && '' !== $large ) {
    return $large;
  }

  $full = wp_get_attachment_image_url( $attachment_id, 'full' );

  return is_string( $full ) ? $full : '';
}

/**
 * Read and validate a hex color from section args.
 *
 * @param array<string, mixed> $args Args.
 * @param string               $key  Key, e.g. section_bg_color.
 * @return string Empty or #rrggbb.
 */
function custom_theme_get_section_arg_hex_color( array $args, string $key ): string {
  $raw = isset( $args[ $key ] ) ? sanitize_hex_color( (string) $args[ $key ] ) : '';

  return ( is_string( $raw ) && '' !== $raw ) ? $raw : '';
}

/**
 * Inline style for section background and text color.
 *
 * @param string $bg_color Background hex or empty.
 * @param string $fg_color Text hex or empty.
 * @return string
 */
function custom_theme_join_section_surface_styles( string $bg_color, string $fg_color ): string {
  $parts = array();
  if ( '' !== $bg_color ) {
    $parts[] = 'background-color:' . $bg_color;
  }
  if ( '' !== $fg_color ) {
    $parts[] = 'color:' . $fg_color;
  }

  return implode( ';', $parts );
}

/**
 * Resolve desktop / tablet / mobile background URLs.
 *
 * When tablet or mobile is not chosen, reuse another attachment with a smaller registered size
 * (tablet/mobile image sizes) instead of full resolution.
 *
 * @param int $desktop_id Desktop attachment ID.
 * @param int $tablet_id  Tablet attachment ID.
 * @param int $mobile_id  Mobile attachment ID.
 * @return array{desktop: string, tablet: string, mobile: string}
 */
function custom_theme_resolve_block_section_background_urls( int $desktop_id, int $tablet_id, int $mobile_id ): array {
  $desktop    = custom_theme_get_section_background_image_url( $desktop_id );
  $tablet_raw = custom_theme_get_section_background_image_url( $tablet_id );
  $mobile_raw = custom_theme_get_section_background_image_url( $mobile_id );

  // Tablet: explicit image, else sized desktop, else tablet-only / mobile-only fallbacks.
  if ( '' !== $tablet_raw ) {
    $tablet = $tablet_raw;
  } elseif ( $desktop_id > 0 ) {
    $tablet = custom_theme_get_section_background_sized_image_url( $desktop_id, CUSTOM_THEME_SECTION_BG_TABLET_IMAGE_SIZE );
  } elseif ( $mobile_id > 0 ) {
    $tablet = $mobile_raw;
  } else {
    $tablet = '';
  }

  // Mobile: explicit image, else sized desktop, else sized tablet image.
  if ( '' !== $mobile_raw ) {
    $mobile = $mobile_raw;
  } elseif ( $desktop_id > 0 ) {
    $mobile = custom_theme_get_section_background_sized_image_url( $desktop_id, CUSTOM_THEME_SECTION_BG_MOBILE_IMAGE_SIZE );
  } elseif ( $tablet_id > 0 ) {
    $mobile = custom_theme_get_section_background_sized_image_url( $tablet_id, CUSTOM_THEME_SECTION_BG_MOBILE_IMAGE_SIZE );
  } else {
    $mobile = $tablet;
  }

  return array(
	  'desktop' => $desktop,
	  'tablet'  => $tablet,
	  'mobile'  => $mobile,
  );
}

/**
 * Print scoped CSS for responsive section backgrounds (mobile-first breakpoints).
 *
 * @param string                                                 $uid  Unique class suffix from wp_unique_id().
 * @param array{mobile: string, tablet: string, desktop: string} $urls Resolved URLs.
 * @return void
 */
function custom_theme_print_block_section_background_styles( string $uid, array $urls ): void {
  echo '<style>';
  echo '.' . esc_attr( $uid ) . '{background-image:url(' . esc_url( $urls['mobile'] ) . ');}';
  echo '@media (min-width:640px){.' . esc_attr( $uid ) . '{background-image:url(' . esc_url( $urls['tablet'] ) . ');}}';
  echo '@media (min-width:1024px){.' . esc_attr( $uid ) . '{background-image:url(' . esc_url( $urls['desktop'] ) . ');}}';
  echo '</style>';
}

/**
 * Build section shell context from merged block args.
 *
 * @param array<string, mixed> $args Merged block args.
 * @return array{
 *   data_block: string,
 *   section_style: string,
 *   section_classes: array<int, string>,
 *   inner_classes: string,
 *   has_bg_image: bool,
 *   urls: array{desktop: string, tablet: string, mobile: string},
 *   uid: string
 * }
 */
function custom_theme_collect_block_section_shell_context( array $args ): array {
  $data_block = isset( $args['data_custom_theme_block'] ) ? sanitize_text_field( (string) $args['data_custom_theme_block'] ) : '';

  $bg_color = custom_theme_get_section_arg_hex_color( $args, 'section_bg_color' );
  $fg_color = custom_theme_get_section_arg_hex_color( $args, 'section_text_color' );

  $desk_id = isset( $args['section_bg_image_desktop'] ) ? absint( $args['section_bg_image_desktop'] ) : 0;
  $tab_id  = isset( $args['section_bg_image_tablet'] ) ? absint( $args['section_bg_image_tablet'] ) : 0;
  $mob_id  = isset( $args['section_bg_image_mobile'] ) ? absint( $args['section_bg_image_mobile'] ) : 0;

  $urls          = custom_theme_resolve_block_section_background_urls( $desk_id, $tab_id, $mob_id );
  $has_bg_image  = '' !== $urls['mobile'] || '' !== $urls['tablet'] || '' !== $urls['desktop'];
  $uid           = wp_unique_id( 'cbb-section-' );
  $section_style = custom_theme_join_section_surface_styles( $bg_color, $fg_color );
  $inner_classes = isset( $args['inner_container_classes'] ) && is_string( $args['inner_container_classes'] )
    ? $args['inner_container_classes']
    : 'mx-auto container';

  $section_classes = array(
	  'custom-theme-block-section',
	  'relative',
	  'w-full',
	  'px-4',
	  'py-8',
	  'sm:px-8',
  );

  if ( $has_bg_image ) {
    $section_classes[] = 'bg-cover';
    $section_classes[] = 'bg-center';
    $section_classes[] = 'bg-no-repeat';
    $section_classes[] = $uid;
  }

  return array(
	  'data_block'      => $data_block,
	  'section_style'   => $section_style,
	  'section_classes' => $section_classes,
	  'inner_classes'   => $inner_classes,
	  'has_bg_image'    => $has_bg_image,
	  'urls'            => $urls,
	  'uid'             => $uid,
  );
}

/**
 * Print outer section + inner container around block markup.
 *
 * Expected $args keys (from Abstract_Block::map_advanced_fields_to_template_args plus block-specific):
 * - section_bg_color, section_text_color (optional hex)
 * - section_bg_image_desktop, section_bg_image_tablet, section_bg_image_mobile (attachment IDs)
 * - data_custom_theme_block (string for data-custom-theme-block)
 * - inner_container_classes (optional; default constrained container)
 *
 * @param array<string, mixed> $args       Merged block args.
 * @param string               $inner_html Markup for the block body (inside inner container).
 * @return void
 */
function custom_theme_render_block_section_shell( array $args, string $inner_html ): void {
  $ctx = custom_theme_collect_block_section_shell_context( $args );

  if ( $ctx['has_bg_image'] ) {
    custom_theme_print_block_section_background_styles( $ctx['uid'], $ctx['urls'] );
  }

  echo '<section class="' . esc_attr( implode( ' ', $ctx['section_classes'] ) ) . '"';
  if ( '' !== $ctx['data_block'] ) {
    echo ' data-custom-theme-block="' . esc_attr( $ctx['data_block'] ) . '"';
  }
  if ( '' !== $ctx['section_style'] ) {
    echo ' style="' . esc_attr( $ctx['section_style'] ) . '"';
  }
  echo '>';

  echo '<div class="' . esc_attr( $ctx['inner_classes'] ) . '">';
  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Composed block inner HTML from theme templates.
  echo $inner_html;
  echo '</div></section>';
}
