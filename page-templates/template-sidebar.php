<?php
/**
 * Template Name: Sidebar Template
 * Template Post Type: page, post
 *
 * Main column + widget sidebar (Appearance → Widgets → Sidebar).
 *
 * @package CustomTheme
 */

get_header();
?>
<main id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
      ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'layout-sidebar' ); ?>>
			<div>
				<?php the_content(); ?>
			</div>
			<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
				<aside role="complementary" aria-label="<?php echo esc_attr( __( 'Sidebar', 'mbn-theme' ) ); ?>">
					<?php dynamic_sidebar( 'sidebar-1' ); ?>
				</aside>
			<?php endif; ?>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
