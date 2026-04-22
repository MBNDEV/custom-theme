<?php
/**
 * Theme page templates (page-templates/*.php) and Block Template sync.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Directory holding page template files (one Block Template per PHP file).
 *
 * @return string Absolute path, no trailing slash.
 */
function custom_theme_layout_templates_directory(): string {
  return get_theme_file_path( 'page-templates' );
}

/**
 * Slugs reserved for system Block Templates (not created from layout files).
 *
 * @return array<int, string>
 */
function custom_theme_reserved_layout_block_template_slugs(): array {
  return array(
	  custom_theme_header_template_slug(),
	  custom_theme_footer_template_slug(),
  );
}

/**
 * Basenames (without .php) of layout files present on disk.
 *
 * @return array<int, string>
 */
function custom_theme_get_layout_template_file_slugs(): array {
  $dir = custom_theme_layout_templates_directory();
  if ( ! is_dir( $dir ) ) {
    return array();
  }

  $pattern = trailingslashit( $dir ) . '*.php';
  $files   = glob( $pattern );
  if ( false === $files ) {
    return array();
  }

  $slugs = array();
  foreach ( $files as $file ) {
    $base = basename( $file, '.php' );
    $base = sanitize_title( $base );
    if ( '' !== $base ) {
      $slugs[] = $base;
    }
  }

  return array_unique( $slugs );
}

/**
 * Human-readable Block Template title (e.g. template-blank → Blank Template).
 *
 * @param string $slug Sanitized slug.
 * @return string
 */
function custom_theme_layout_template_title_from_slug( string $slug ): string {
  $slug = sanitize_title( $slug );
  if ( '' === $slug ) {
    return '';
  }

  if ( preg_match( '/^template-(.+)$/', $slug, $matches ) ) {
    $words = explode( '-', $matches[1] );
    $parts = array_map(
      static function ( $w ) {
        return ucfirst( $w );
      },
      $words
    );
    return implode( ' ', $parts ) . ' Template';
  }

  $raw_parts = explode( '-', $slug );
  $last      = strtolower( (string) end( $raw_parts ) );
  $parts     = array_map(
    static function ( $w ) {
      return ucfirst( $w );
    },
    $raw_parts
  );

  $human = implode( ' ', $parts );

  if ( 'template' === $last ) {
    return $human;
  }

  return $human . ' Template';
}

/**
 * Slugs to treat as one layout with the given page template file (legacy names; avoids duplicate Block Template posts).
 *
 * @param string $file_slug Sanitized basename without .php.
 * @return array<int, string>
 */
function custom_theme_page_template_block_duplicate_check_slugs( string $file_slug ): array {
  $file_slug = sanitize_title( $file_slug );
  if ( '' === $file_slug ) {
    return array();
  }

  $legacy = array(
	  'template-blank'   => array( 'blank', 'blank-template' ),
	  'template-sidebar' => array( 'sidebar', 'sidebar-template' ),
  );

  $slugs = array( $file_slug );
  if ( isset( $legacy[ $file_slug ] ) ) {
    $slugs = array_merge( $slugs, $legacy[ $file_slug ] );
  }

  return array_values( array_unique( array_filter( array_map( 'sanitize_title', $slugs ) ) ) );
}

/**
 * Whether a Block Template already exists for this layout (any checked slug, any post status).
 *
 * @param string $file_slug Sanitized basename without .php.
 * @return bool
 */
function custom_theme_block_layout_already_claimed_for_page_template_file( string $file_slug ): bool {
  foreach ( custom_theme_page_template_block_duplicate_check_slugs( $file_slug ) as $check_slug ) {
    if ( custom_theme_get_block_template_id_by_slug_any_status( $check_slug ) > 0 ) {
      return true;
    }
  }

  return false;
}

/**
 * Whether this Block Template slug corresponds to a theme page template layout (file or legacy slug).
 *
 * @param string $slug Post name.
 * @return bool
 */
function custom_theme_block_slug_is_page_template_layout( string $slug ): bool {
  $slug = sanitize_title( $slug );
  if ( '' === $slug ) {
    return false;
  }

  if ( in_array( $slug, custom_theme_reserved_layout_block_template_slugs(), true ) ) {
    return false;
  }

  if ( in_array( $slug, custom_theme_get_layout_template_file_slugs(), true ) ) {
    return true;
  }

  return in_array( $slug, array( 'blank', 'sidebar', 'blank-template', 'sidebar-template' ), true );
}

/**
 * Create Block Template posts for each page-templates/*.php file when none exists (no duplicates).
 *
 * @return void
 */
function custom_theme_sync_layout_template_files_to_block_templates(): void {
  if ( ! post_type_exists( 'mbn_block_template' ) ) {
    return;
  }

  $reserved = array_flip( custom_theme_reserved_layout_block_template_slugs() );
  $slugs    = custom_theme_get_layout_template_file_slugs();

  foreach ( $slugs as $slug ) {
    if ( isset( $reserved[ $slug ] ) ) {
      continue;
    }

    if ( custom_theme_block_layout_already_claimed_for_page_template_file( $slug ) ) {
      continue;
    }

    $title = custom_theme_layout_template_title_from_slug( $slug );
    if ( '' === $title ) {
      continue;
    }

    $created = wp_insert_post(
      array(
		  'post_type'    => 'mbn_block_template',
		  'post_title'   => $title,
		  'post_name'    => $slug,
		  'post_status'  => 'publish',
		  'post_content' => '',
      ),
      true
    );
    if ( is_wp_error( $created ) ) {
      continue;
    }
  }
}
add_action( 'init', 'custom_theme_sync_layout_template_files_to_block_templates', 25 );
