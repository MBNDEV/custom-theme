<?php
/**
 * Template Sync Tools - Import/Export Block Templates.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Template Tools submenu page.
 */
function custom_theme_add_template_tools_page() {
	add_submenu_page(
      'edit.php?post_type=mbn_block_template',
      __( 'Template Sync Tools', 'mbn-theme' ),
      __( 'Sync Tools', 'mbn-theme' ),
      'manage_options',
      'template-sync-tools',
      'custom_theme_render_template_tools_page'
	);
}
add_action( 'admin_menu', 'custom_theme_add_template_tools_page' );

/**
 * Import a single template file.
 *
 * @param string $slug Template slug.
 * @return bool True if imported successfully, false otherwise.
 * @throws Exception If import fails.
 */
function custom_theme_import_single_template_file( $slug ) {
	$file_path = get_theme_file_path( 'page-templates/' . $slug . '.php' );
  if ( ! file_exists( $file_path ) ) {
      return false;
  }

	// Extract rendered content using output buffering.
	ob_start();
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_include
	include $file_path;
	$content = ob_get_clean();
	$content = trim( $content );

	// Get or create Block Template post
	$post_id = custom_theme_get_block_template_id_by_slug( $slug );

  if ( 0 === $post_id ) {
      // Create new post
      $title      = custom_theme_layout_template_title_from_slug( $slug );
      $created_id = wp_insert_post(
        array(
			'post_type'    => 'mbn_block_template',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_content' => $content,
		),
        true
      );

    if ( is_wp_error( $created_id ) ) {
      throw new Exception( esc_html( $created_id->get_error_message() ) );
    }
  } else {
      // Update existing post
      $updated = wp_update_post(
        array(
			'ID'           => $post_id,
			'post_content' => $content,
		)
      );

    if ( is_wp_error( $updated ) ) {
        throw new Exception( esc_html( $updated->get_error_message() ) );
    }
  }

	return true;
}

/**
 * Import all templates from files (header/footer from template-parts/, page templates from page-templates/).
 *
 * @throws Exception If import fails.
 */
function custom_theme_import_all_templates_from_files() {
	$imported = 0;
	$errors   = array();

	// Import header/footer using existing function
  try {
      custom_theme_maybe_seed_default_block_templates( true );
      $imported += 2; // Header + Footer
  } catch ( Exception $e ) {
      $errors[] = 'System templates: ' . $e->getMessage();
  }

	// Import page templates from page-templates/
	$page_template_slugs = custom_theme_get_layout_template_file_slugs();

  foreach ( $page_template_slugs as $slug ) {
    try {
        $imported_file = custom_theme_import_single_template_file( $slug );
      if ( $imported_file ) {
        ++$imported;
      }
    } catch ( Exception $e ) {
        $errors[] = sprintf( '%s: %s', $slug, $e->getMessage() );
    }
  }

	// Report results
  if ( ! empty( $errors ) && 0 === $imported ) {
      throw new Exception( implode( ' | ', array_map( 'esc_html', $errors ) ) );
  }

	$message = sprintf(
		// translators: %d is the number of templates imported.
      __( '%d template(s) imported successfully!', 'mbn-theme' ),
      $imported
	);

  if ( ! empty( $errors ) ) {
      $message .= ' ' . __( 'Warnings:', 'mbn-theme' ) . ' ' . implode( '; ', $errors );
  }

	add_settings_error(
      'custom_theme_sync',
      'import_success',
      $message,
      empty( $errors ) ? 'success' : 'warning'
	);
}

/**
 * Handle sync actions.
 */
function custom_theme_handle_template_sync_actions() {
  if ( ! isset( $_POST['custom_theme_sync_action'] ) ) {
      return;
  }

  if ( ! current_user_can( 'manage_options' ) ) {
      return;
  }

	check_admin_referer( 'custom_theme_sync_templates', 'custom_theme_sync_nonce' );

	$action = sanitize_text_field( $_POST['custom_theme_sync_action'] );

  if ( 'import_from_files' === $action ) {
    try {
        // Import all templates from files
        custom_theme_import_all_templates_from_files();
    } catch ( Exception $e ) {
        add_settings_error(
          'custom_theme_sync',
          'import_error',
          sprintf(
                // translators: %s is the error message.
            __( 'Import failed: %s', 'mbn-theme' ),
            $e->getMessage()
          ),
          'error'
        );
    }
  } elseif ( 'export_to_files' === $action ) {
    try {
        // Export Block Template posts to template-parts/*.php files
        custom_theme_export_templates_to_files();
    } catch ( Exception $e ) {
        add_settings_error(
          'custom_theme_sync',
          'export_error',
          sprintf(
                // translators: %s is the error message.
            __( 'Export failed: %s', 'mbn-theme' ),
            $e->getMessage()
          ),
          'error'
        );
    }
  }
}
add_action( 'admin_init', 'custom_theme_handle_template_sync_actions' );

/**
 * Validate export directories exist and are writable.
 *
 * @param array $dirs Array of directory names to validate.
 * @throws Exception If directory validation fails.
 */
function custom_theme_validate_export_directories( $dirs ) {
  foreach ( $dirs as $dir_name ) {
      $dir_path = get_theme_file_path( $dir_name );
	if ( ! is_dir( $dir_path ) ) {
        wp_mkdir_p( $dir_path );
    }
    if ( ! is_dir( $dir_path ) ) {
        throw new Exception( esc_html( sprintf( 'Directory does not exist: %s', $dir_path ) ) );
    }
      // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
    if ( ! is_writable( $dir_path ) ) {
        throw new Exception( esc_html( sprintf( 'Directory is not writable: %s. Check file permissions.', $dir_path ) ) );
    }
  }
}

/**
 * Generate template file content with PHP header.
 *
 * @param WP_Post $post Template post object.
 * @return string File content.
 */
function custom_theme_generate_template_file_content( $post ) {
	$file_content  = "<?php\n";
	$file_content .= "/**\n";
	$file_content .= ' * ' . $post->post_title . " Block Template.\n";
	$file_content .= " * \n";
	$file_content .= ' * Syncs with "' . $post->post_title . "\" Block Template post.\n";
	$file_content .= " * Edit in WordPress admin, then export using Block Templates → Sync Tools.\n";
	$file_content .= " * \n";
	$file_content .= " * @package CustomTheme\n";
	$file_content .= " */\n\n";
	$file_content .= "if ( ! defined( 'ABSPATH' ) ) {\n";
	$file_content .= "\texit;\n";
	$file_content .= "}\n";
	$file_content .= "?>\n";
	$file_content .= $post->post_content;

	return $file_content;
}

/**
 * Export a single template to file.
 *
 * @param string $slug Template slug.
 * @param array  $config Export configuration with 'filename' and 'dir'.
 * @param object $wp_filesystem WP_Filesystem instance.
 * @return bool True if exported successfully.
 * @throws Exception If export fails.
 */
function custom_theme_export_single_template( $slug, $config, $wp_filesystem ) {
	$post_id = custom_theme_get_block_template_id_by_slug( $slug );
  if ( $post_id <= 0 ) {
      throw new Exception( esc_html( sprintf( 'Template post not found for slug: %s', $slug ) ) );
  }

	$post = get_post( $post_id );
  if ( ! $post instanceof \WP_Post ) {
      throw new Exception( esc_html( sprintf( 'Invalid post object for ID: %d', $post_id ) ) );
  }

	$file_content = custom_theme_generate_template_file_content( $post );
	$file_path    = get_theme_file_path( $config['dir'] . '/' . $config['filename'] . '.php' );

	// Write file using WP_Filesystem.
	$written = $wp_filesystem->put_contents( $file_path, $file_content, FS_CHMOD_FILE );

  if ( false === $written ) {
      throw new Exception( esc_html( sprintf( 'Failed to write file: %s. Check file permissions.', $file_path ) ) );
  }

	return true;
}

/**
 * Export Block Template posts to PHP files (header/footer to template-parts/, page template blocks to template-parts/layouts/).
 *
 * @throws Exception If export fails.
 */
function custom_theme_export_templates_to_files() {
	$exported = 0;
	$errors   = array();

	// Initialize WP_Filesystem.
	global $wp_filesystem;
  if ( empty( $wp_filesystem ) ) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
      WP_Filesystem();
  }

	// Export header/footer to template-parts/
	$system_templates = array(
		custom_theme_header_template_slug() => array(
			'filename' => 'header-template',
			'dir'      => 'template-parts',
		),
		custom_theme_footer_template_slug() => array(
			'filename' => 'footer-template',
			'dir'      => 'template-parts',
		),
	);

	// Export page template BLOCK CONTENT to template-parts/layouts/
	// Note: page-templates/*.php stay as traditional WordPress templates
	$page_template_slugs = custom_theme_get_layout_template_file_slugs();
	$page_templates      = array();
	foreach ( $page_template_slugs as $slug ) {
		// Extract basename from template-* slug (e.g., template-blank → blank)
		$layout_name             = preg_replace( '/^template-/', '', $slug );
		$page_templates[ $slug ] = array(
			'filename' => $layout_name,
			'dir'      => 'template-parts/layouts',
		);
	}

	$all_templates = array_merge( $system_templates, $page_templates );

	// Check directories
	$dirs = array( 'template-parts', 'template-parts/layouts' );
	custom_theme_validate_export_directories( $dirs );

	// Export all templates
	foreach ( $all_templates as $slug => $config ) {
      try {
          $exported_template = custom_theme_export_single_template( $slug, $config, $wp_filesystem );
        if ( $exported_template ) {
            ++$exported;
        }
      } catch ( Exception $e ) {
          $errors[] = sprintf( '%s: %s', $config['filename'], $e->getMessage() );
      }
	}

	// Report results
	if ( $exported > 0 ) {
		$message = sprintf(
			// translators: %d is the number of templates exported.
          __( '%d template(s) exported successfully!', 'mbn-theme' ),
          $exported
		);

      if ( ! empty( $errors ) ) {
          $message .= ' ' . __( 'Errors:', 'mbn-theme' ) . ' ' . implode( '; ', $errors );
      }

		add_settings_error(
          'custom_theme_sync',
          'export_success',
          $message,
          empty( $errors ) ? 'success' : 'warning'
		);
	} else {
		$error_message = __( 'No templates were exported.', 'mbn-theme' );

      if ( ! empty( $errors ) ) {
          $error_message .= ' ' . __( 'Errors:', 'mbn-theme' ) . ' ' . implode( '; ', array_map( 'esc_html', $errors ) );
      } else {
          $error_message .= ' ' . __( 'Make sure Block Template posts exist.', 'mbn-theme' );
      }

		throw new Exception( esc_html( $error_message ) );
	}
}

/**
 * Render export destinations diagnostic table.
 */
function custom_theme_render_export_destinations_table() {
  ?>
	<div class="card" style="max-width: 800px; background: #fff3cd; border-left: 4px solid #ffc107;">
		<h2>🔍 Export Destinations (Debug Info)</h2>
		<table class="widefat striped" style="margin-top: 10px;">
			<thead>
				<tr>
					<th>Block Template</th>
					<th>Export Location</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Header Template</strong> (<?php echo esc_html( custom_theme_header_template_slug() ); ?>)</td>
					<td><code>template-parts/header-template.php</code></td>
				</tr>
				<tr>
					<td><strong>Footer Template</strong> (<?php echo esc_html( custom_theme_footer_template_slug() ); ?>)</td>
					<td><code>template-parts/footer-template.php</code></td>
				</tr>
				<?php
				$page_slugs = custom_theme_get_layout_template_file_slugs();
				foreach ( $page_slugs as $slug ) {
					$layout_name = preg_replace( '/^template-/', '', $slug );
					$title       = custom_theme_layout_template_title_from_slug( $slug );
                  ?>
					<tr>
						<td><strong><?php echo esc_html( $title ); ?></strong> (<?php echo esc_html( $slug ); ?>)</td>
						<td><code>template-parts/layouts/<?php echo esc_html( $layout_name ); ?>.php</code></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<p style="margin-top: 10px;">
			<em>This table shows where each Block Template will be exported when you click the Export button.</em>
		</p>
	</div>
	<?php
}

/**
 * Render import card section.
 */
function custom_theme_render_import_card() {
  ?>
	<div class="card" style="max-width: 800px;">
		<h2>📥 Import Templates from Files</h2>
		<p>
			<strong>Use this to deploy to staging/production:</strong><br>
			Imports all Block Templates from PHP files:
		</p>
		<ul>
			<li>Header/Footer from <code>template-parts/</code></li>
			<li>Page Template Blocks from <code>template-parts/layouts/</code></li>
		</ul>
		<p>
			<strong>Note:</strong> Traditional WordPress template files in <code>page-templates/</code> 
			are Git-tracked separately and don't need syncing.
		</p>
		<p>
			This will <strong>overwrite</strong> existing Block Template posts with content from PHP files.
		</p>
		<form method="post" style="margin-top: 20px;">
			<?php wp_nonce_field( 'custom_theme_sync_templates', 'custom_theme_sync_nonce' ); ?>
			<input type="hidden" name="custom_theme_sync_action" value="import_from_files">
			<button type="submit" class="button button-primary">
				📥 Import from Files (Overwrite Database)
			</button>
		</form>
	</div>
	<?php
}

/**
 * Render export card section.
 */
function custom_theme_render_export_card() {
  ?>
	<div class="card" style="max-width: 800px; margin-top: 20px;">
		<h2>📤 Export Templates to Files</h2>
		<p>
			<strong>Use this after editing in WordPress admin:</strong><br>
			Exports all Block Template posts to PHP files:
		</p>
		<ul>
			<li>Header/Footer → <code>template-parts/</code></li>
			<li>Page Template Blocks → <code>template-parts/layouts/</code></li>
		</ul>
		<p>
			<strong>Note:</strong> This exports <em>block content</em> only. Traditional WordPress 
			templates in <code>page-templates/</code> are edited directly in PHP.
		</p>
		<p>
			Allows you to version control templates and deploy via Git.
		</p>
		<form method="post" style="margin-top: 20px;">
			<?php wp_nonce_field( 'custom_theme_sync_templates', 'custom_theme_sync_nonce' ); ?>
			<input type="hidden" name="custom_theme_sync_action" value="export_to_files">
			<button type="submit" class="button button-secondary">
				📤 Export to Files (Update Git Files)
			</button>
		</form>
	</div>
	<?php
}

/**
 * Render workflow instructions card.
 */
function custom_theme_render_workflow_card() {
  ?>
	<div class="card" style="max-width: 800px; margin-top: 20px; background: #f0f6fc; border-left: 4px solid #0073aa;">
		<h2>ℹ️ Development Workflow</h2>
		<h3>Local Development:</h3>
		<ol>
			<li>Edit Block Templates in WordPress admin (Block Templates menu)</li>
			<li>Click <strong>"📤 Export to Files"</strong> button above</li>
			<li>Commit updated files to Git:
				<ul>
					<li><code>template-parts/*.php</code> (header/footer)</li>
					<li><code>template-parts/layouts/*.php</code> (page template blocks)</li>
				</ul>
			</li>
			<li>Push to GitHub</li>
		</ol>

		<h3>Staging/Production Deployment:</h3>
		<ol>
			<li>Pull latest code from Git</li>
			<li>Go to <strong>Block Templates → Sync Tools</strong></li>
			<li>Click <strong>"📥 Import from Files"</strong> button</li>
			<li>Templates are now updated!</li>
		</ol>

		<h3>For Page Content (Home, About, etc):</h3>
		<p>
			Use <strong>Block Patterns</strong> instead of building pages in the editor.<br>
			Patterns are defined in <code>inc/includes-block-patterns.php</code> and ship via Git automatically.
		</p>
		<p>
			To use a pattern:
		</p>
		<ol>
			<li>Edit a page in WordPress</li>
			<li>Click the <strong>"+"</strong> button to add a block</li>
			<li>Go to the <strong>"Patterns"</strong> tab</li>
			<li>Select <strong>"Black Line Security Ops"</strong> category</li>
			<li>Insert your pattern (e.g., "Complete Home Page")</li>
		</ol>
	</div>
	<?php
}

/**
 * Render Template Sync Tools page.
 */
function custom_theme_render_template_tools_page() {
  ?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Template Sync Tools', 'mbn-theme' ); ?></h1>
		
		<?php settings_errors( 'custom_theme_sync' ); ?>

		<?php custom_theme_render_export_destinations_table(); ?>
		<?php custom_theme_render_import_card(); ?>
		<?php custom_theme_render_export_card(); ?>
		<?php custom_theme_render_workflow_card(); ?>
	</div>
	<?php
}
