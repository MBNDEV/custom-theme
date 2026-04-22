<?php
/**
 * Widget Name: Sample Widget
 * Widget ID: custom_theme_sample_widgets
 * Widget Area ID: sample-widget
 * Widget Area Name: Sample Widget
 * Widget Description: Renders the theme Sample Sidebar widget area below the main column on sample-style layouts.
 *
 * @package CustomTheme
 */

if ( ! is_active_sidebar( 'sample-widget' ) ) {
  return;
}
?>
<aside id="sample-widget" class="widget-area sample-widget">
  <?php dynamic_sidebar( 'sample-widget' ); ?>
</aside>
