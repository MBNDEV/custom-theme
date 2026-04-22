<?php
/**
 * Register widget areas and WP_Widget classes from `widgets/*.php` file headers (like page template headers).
 *
 * Headers: Widget Name (required), Widget ID (WP id_base), Widget Description, Widget Area ID (register_sidebar),
 * Widget Area Name (optional; defaults to Widget Name).
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Default file headers for widget partials.
 *
 * @return array<string, string>
 */
function custom_theme_widget_file_headers(): array {
  return array(
	  'widget_name'        => 'Widget Name',
	  'widget_id'          => 'Widget ID',
	  'widget_description' => 'Widget Description',
	  'widget_area_id'     => 'Widget Area ID',
	  'widget_area_name'   => 'Widget Area Name',
  );
}

/**
 * PHP files under `widgets/` that are infrastructure, not widget partials.
 *
 * @return array<int, string>
 */
function custom_theme_widget_bootstrap_basenames(): array {
  return array();
}

/**
 * Escape a string for use as a single-quoted PHP literal inside generated code.
 *
 * @param string $value Raw string.
 * @return string Quoted literal (e.g. 'foo\'bar').
 */
function custom_theme_widget_php_string_literal( string $value ): string {
  return "'" . addcslashes( $value, "'\\" ) . "'";
}

/**
 * Whether a path is inside the theme `widgets/` directory.
 *
 * @param string $file Absolute path to a file.
 * @return bool
 */
function custom_theme_is_widgets_dir_file( string $file ): bool {
  $widgets_dir = wp_normalize_path( trailingslashit( get_theme_file_path( 'widgets' ) ) );
  $real        = wp_normalize_path( (string) realpath( $file ) );

  if ( '' === $real ) {
    return false;
  }

  return strpos( $real, $widgets_dir ) === 0;
}

/**
 * Register sidebars from `Widget Area ID:` headers (once per area id).
 *
 * @param array<int, string>    $files Widget PHP files.
 * @param array<string, bool>   $skip  Basenames to skip (keys = basenames).
 * @param array<string, string> $hdrs  Header map for get_file_data().
 * @return void
 */
function custom_theme_register_widget_sidebars_from_files( array $files, array $skip, array $hdrs ): void {
  $registered_areas = array();

  foreach ( $files as $file ) {
    if ( ! custom_theme_is_widgets_dir_file( $file ) ) {
      continue;
    }

    $base = basename( $file );
    if ( isset( $skip[ $base ] ) ) {
      continue;
    }

    $data = get_file_data( $file, $hdrs, 'widget' );
    if ( empty( $data['widget_name'] ) || empty( $data['widget_area_id'] ) ) {
      continue;
    }

    $area_id = sanitize_key( $data['widget_area_id'] );
    if ( '' === $area_id || isset( $registered_areas[ $area_id ] ) ) {
      continue;
    }

    $area_name = ! empty( $data['widget_area_name'] ) ? $data['widget_area_name'] : $data['widget_name'];
    $area_desc = ! empty( $data['widget_description'] )
      ? $data['widget_description']
      : __( 'Widgets shown beside the main column when using the Sidebar Template.', 'mbn-theme' );

    register_sidebar(
      array(
		  'name'          => $area_name,
		  'id'            => $area_id,
		  'description'   => $area_desc,
		  'before_widget' => '<section id="%1$s" class="widget %2$s mb-6">',
		  'after_widget'  => '</section>',
		  'before_title'  => '<h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-gray-600">',
		  'after_title'   => '</h2>',
      )
    );
    $registered_areas[ $area_id ] = true;
  }
}

/**
 * Register one WP_Widget subclass per file with `Widget Name:`.
 *
 * @param array<int, string>    $files Widget PHP files.
 * @param array<string, bool>   $skip  Basenames to skip (keys = basenames).
 * @param array<string, string> $hdrs  Header map for get_file_data().
 * @return void
 */
function custom_theme_register_widget_classes_from_files( array $files, array $skip, array $hdrs ): void {
  foreach ( $files as $file ) {
    if ( ! custom_theme_is_widgets_dir_file( $file ) ) {
      continue;
    }

    $base = basename( $file );
    if ( isset( $skip[ $base ] ) ) {
      continue;
    }

    $data = get_file_data( $file, $hdrs, 'widget' );
    if ( empty( $data['widget_name'] ) ) {
      continue;
    }

    $name        = $data['widget_name'];
    $description = isset( $data['widget_description'] ) ? $data['widget_description'] : '';
    if ( ! empty( $data['widget_id'] ) ) {
      $id_base = sanitize_key( $data['widget_id'] );
    } else {
      $id_base = 'custom_theme_' . sanitize_key( basename( $file, '.php' ) );
    }
    if ( '' === $id_base ) {
      $id_base = 'custom_theme_' . sanitize_key( basename( $file, '.php' ) );
    }

    $class_name = 'Custom_Theme_File_Widget_' . md5( $file );

    if ( class_exists( $class_name, false ) ) {
      continue;
    }

    $path_export = custom_theme_widget_php_string_literal( $file );
    $name_export = custom_theme_widget_php_string_literal( $name );
    $desc_export = custom_theme_widget_php_string_literal( $description );
    $id_export   = custom_theme_widget_php_string_literal( $id_base );

    // WordPress instantiates widgets with new $class() (no args); each file needs its own subclass.
    // phpcs:ignore Squiz.PHP.Eval.Discouraged -- dynamic widget class per theme file; inputs are validated paths/strings.
    eval(
      sprintf(
        'final class %1$s extends \\WP_Widget {
          private $template_path;
          public function __construct() {
            $this->template_path = %2$s;
            parent::__construct(
              %3$s,
              %4$s,
              array(
                \'description\' => %5$s,
              )
            );
          }
          public function widget( $args, $instance ) {
            include $this->template_path;
          }
          public function form( $instance ) {
            return \'\';
          }
          public function update( $new_instance, $old_instance ) {
            return $old_instance;
          }
        }',
        $class_name,
        $path_export,
        $id_export,
        $name_export,
        $desc_export
      )
    );

    register_widget( $class_name );
  }
}

/**
 * Register sidebars and WP_Widget classes from `widgets/*.php` headers.
 *
 * @return void
 */
function custom_theme_register_file_widgets(): void {
  $widgets_dir = get_theme_file_path( 'widgets' );
  $pattern     = trailingslashit( $widgets_dir ) . '*.php';
  $files       = glob( $pattern );

  if ( false === $files ) {
    return;
  }

  $skip = array_flip( custom_theme_widget_bootstrap_basenames() );
  $hdrs = custom_theme_widget_file_headers();

  custom_theme_register_widget_sidebars_from_files( $files, $skip, $hdrs );
  custom_theme_register_widget_classes_from_files( $files, $skip, $hdrs );
}
add_action( 'widgets_init', 'custom_theme_register_file_widgets' );
