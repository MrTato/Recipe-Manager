<?php
/** Run with wp eval-file in the local WordPress installation. */
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once dirname( __DIR__ ) . '/includes/admin/default-units.php';
require_once dirname( __DIR__ ) . '/includes/admin/unit-manager.php';
$admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
if ( ! $admins ) {
 throw new RuntimeException( 'An administrator is required for rendering checks.' );
}
wp_set_current_user( $admins[0]->ID );
do_action( 'admin_menu' );
global $submenu;
$found = false;
foreach ( $submenu['edit.php?post_type=rm_recipe'] ?? array() as $item ) {
 if ( 'rm-default-units' === $item[2] ) $found = true;
}
if ( ! $found ) throw new RuntimeException( 'Missing Recipes submenu.' );
ob_start();
\RecipeManager\Core\render_unit_manager();
$html = ob_get_clean();
foreach ( array( 'Default Units', 'Add unit', 'Edit name', 'Delete', '_wpnonce', 'admin-post.php' ) as $expected ) {
 if ( false === strpos( $html, $expected ) ) throw new RuntimeException( 'Missing manager markup: ' . $expected );
}
foreach ( \RecipeManager\Core\get_protected_default_units() as $code ) {
 if ( false !== strpos( $html, 'name="original" value="' . $code . '"' ) ) {
  throw new RuntimeException( 'Protected unit appears in manager: ' . $code );
 }
}
$suggestions = \RecipeManager\Core\get_default_unit_suggestions();
if ( ! $suggestions ) throw new RuntimeException( 'Existing units were not loaded.' );
wp_set_current_user( 0 );
ob_start();
\RecipeManager\Core\render_unit_manager();
$unauthorized = ob_get_clean();
if ( '' !== $unauthorized ) throw new RuntimeException( 'Unauthorized page rendering.' );
echo 'PASS: Recipes submenu, manager rendering, nonce forms, existing units (' . count( $suggestions ) . "), permission checks\n";
