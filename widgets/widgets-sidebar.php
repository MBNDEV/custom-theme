<?php
/**
 * Widget Name: Sidebar Widget
 * Widget ID: custom_theme_sidebar_widgets
 * Widget Area ID: sidebar-1
 * Widget Area Name: Sidebar
 * Widget Description: Renders the theme Sidebar widget area below the main column on blank-style layouts.
 *
 * Widget area below main. Use the same id as Widget Area ID in dynamic_sidebar().
 *
 * @package CustomTheme
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
  return;
}
?>
<aside id="secondary" class="widget-area sidebar">
  <?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
