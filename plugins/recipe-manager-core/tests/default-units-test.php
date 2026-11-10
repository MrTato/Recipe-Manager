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

function add_action( ...$args ) {}
class WP_Error {
 public $code;
 function __construct( $code, $message = '' ) { $this->code = $code; }
}
require dirname( __DIR__ ) . '/includes/admin/unit-manager.php';
check_units( true === \RecipeManager\Core\change_default_unit( 'create', '', 'test scoop' ) );
check_units( isset( \RecipeManager\Core\get_default_unit_suggestions()['test scoop'] ) );
check_units( \RecipeManager\Core\change_default_unit( 'create', '', 'test scoop' ) instanceof WP_Error );
check_units( \RecipeManager\Core\change_default_unit( 'create', '', '   ' ) instanceof WP_Error );
check_units( \RecipeManager\Core\change_default_unit( 'invalid', '', 'foo' ) instanceof WP_Error );
check_units( \RecipeManager\Core\change_default_unit( 'update', 'missing', 'foo' ) instanceof WP_Error );
check_units( \RecipeManager\Core\change_default_unit( 'update', 'test scoop', 'cloves' ) instanceof WP_Error );
check_units( true === \RecipeManager\Core\change_default_unit( 'update', 'test scoop', 'new scoop' ) );
check_units( ! isset( \RecipeManager\Core\get_default_unit_suggestions()['test scoop'] ) );
check_units( isset( \RecipeManager\Core\get_default_unit_suggestions()['new scoop'] ) );
check_units( true === \RecipeManager\Core\change_default_unit( 'delete', 'new scoop', '' ) );
foreach ( \RecipeManager\Core\get_protected_default_units() as $code ) {
 check_units( \RecipeManager\Core\change_default_unit( 'delete', $code, '' ) instanceof WP_Error );
 check_units( \RecipeManager\Core\change_default_unit( 'update', $code, 'changed' ) instanceof WP_Error );
 check_units( \RecipeManager\Core\change_default_unit( 'update', 'cloves', $code ) instanceof WP_Error );
 check_units( isset( \RecipeManager\Core\get_default_unit_suggestions()[$code] ) );
}
$options['rm_default_unit_names'] = array_values( array_diff( $options['rm_default_unit_names'], array( 'g' ) ) );
check_units( isset( \RecipeManager\Core\get_default_unit_suggestions()['g'] ) );
check_units( ! isset( \RecipeManager\Core\get_default_unit_suggestions()['new scoop'] ) );
$old_rows = array( array( 'optional_unit' => 'new scoop' ) );
\RecipeManager\Core\remember_default_units( $old_rows, $old_rows );
check_units( ! isset( \RecipeManager\Core\get_default_unit_suggestions()['new scoop'] ) );
\RecipeManager\Core\remember_default_units( $old_rows );
check_units( isset( \RecipeManager\Core\get_default_unit_suggestions()['new scoop'] ) );
check_units( true === \RecipeManager\Core\change_default_unit( 'create', '', '123' ) );
check_units( true === \RecipeManager\Core\change_default_unit( 'update', '123', '456' ) );
check_units( true === \RecipeManager\Core\change_default_unit( 'delete', '456', '' ) );
echo "PASS: unit CRUD, duplicates, validation, built-in protection and restoration, deletion persistence, new-entry recreation, numeric names\n";
