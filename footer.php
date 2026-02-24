<?php
/**
 * Theme footer template.
 *
 * @package CustomTheme
 */

?>
	<footer id="colophon" class="site-footer">
		<?php if ( is_active_sidebar( 'footer' ) ) : ?>
			<?php dynamic_sidebar( 'footer' ); ?>
		<?php endif; ?>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
