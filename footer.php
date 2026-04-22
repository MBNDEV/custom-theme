<?php
/**
 * Theme footer template.
 *
 * @package CustomTheme
 */

?>
	<footer id="colophon" class="site-footer">
		<?php
		// Output Footer Template block content.
		$footer_html = custom_theme_get_global_footer_template_output_html();
		echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block editor content
		?>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
