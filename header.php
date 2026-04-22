<?php
/**
 * Theme header template.
 *
 * @package CustomTheme
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<header id="masthead" class="site-header">
		<?php
		// Output Header Template block content.
		$header_html = custom_theme_get_global_header_template_output_html();
		echo $header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block editor content
		?>
	</header>
