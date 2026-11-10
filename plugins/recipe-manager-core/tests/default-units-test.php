<?php
/** Standalone checks: php plugins/recipe-manager-core/tests/default-units-test.php */
define( 'ABSPATH', __DIR__ );
$options = array();
function __( $text, $domain = '' ) { return $text; }
function get_option( $key, $default = false ) { global $options; return $options[$key] ?? $default; }
function update_option( $key, $value, $autoload = null ) { global $options; $options[$key] = $value; return true; }
function sanitize_text_field( $text ) { return trim( strip_tags( $text ) ); }
function maybe_unserialize( $value ) { return unserialize( $value ); }
class UnitTestDatabase {
 public $postmeta = 'postmeta';
 public $posts = 'posts';
 public $last_error = '';
 public $reads = 0;
 function prepare( $query, ...$args ) { return $query; }
 function get_col( $query ) {
  $this->reads++;
  return array( serialize( array( array( 'optional_unit' => 'cloves' ), array( 'optional_unit' => 'a custom scoop' ) ) ) );
 }
}
$wpdb = new UnitTestDatabase();
require dirname( __DIR__ ) . '/includes/units.php';
require dirname( __DIR__ ) . '/includes/admin/default-units.php';
function check_units( $condition ) { if ( ! $condition ) throw new RuntimeException( 'Default unit regression.' ); }
$initial = \RecipeManager\Core\get_default_unit_suggestions();
foreach ( array( 'g', 'kg', 'ml', 'l', 'tsp', 'tbsp', 'cup', 'lb', 'oz', 'cloves', 'a custom scoop' ) as $unit ) {
 check_units( isset( $initial[$unit] ) );
}
\RecipeManager\Core\remember_default_units( array( array( 'optional_unit' => 'handfuls & pinches' ), array( 'optional_unit' => 'cloves' ), array( 'optional_unit' => '  ' ) ) );
$updated = \RecipeManager\Core\get_default_unit_suggestions();
check_units( isset( $updated['handfuls & pinches'] ) );
check_units( 1 === count( array_keys( $options['rm_default_unit_names'], 'cloves', true ) ) );
check_units( ! in_array( '', $options['rm_default_unit_names'], true ) );
check_units( 1 === $wpdb->reads );
// Removing a name from a recipe does not remove it from the saved suggestions.
\RecipeManager\Core\remember_default_units( array() );
check_units( $updated === \RecipeManager\Core\get_default_unit_suggestions() );
echo "PASS: built-in units, existing recipe names, custom names, deduplication, persistent suggestions\n";
