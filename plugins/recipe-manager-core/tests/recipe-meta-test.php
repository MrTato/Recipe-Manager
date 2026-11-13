<?php
/** Standalone accessor checks: php plugins/recipe-manager-core/tests/recipe-meta-test.php */
define( 'ABSPATH', __DIR__ );
$metadata = array();
function __( $text, $domain = '' ) { return $text; }
function get_post_type( $id ) { return 7 === $id ? 'rm_recipe' : ( in_array( $id, array( 123, 124, 125 ), true ) ? 'attachment' : 'post' ); }
function get_post_status( $id ) { return 125 === $id ? 'trash' : 'inherit'; }
function get_post_meta( $id, $key, $single ) { global $metadata; return $metadata[ $key ] ?? ''; }
function sanitize_text_field( $value ) { return trim( preg_replace( '/\s+/', ' ', strip_tags( $value ) ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( $value ) ); }
function wp_attachment_is_image( $id ) { return in_array( $id, array( 123, 125 ), true ); }
function update_post_meta( ...$args ) { throw new RuntimeException( 'Accessors must never write metadata.' ); }
require dirname( __DIR__ ) . '/includes/units.php';
require dirname( __DIR__ ) . '/includes/recipe-meta.php';
function expect_same( $expected, $actual ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( 'Expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) );
	}
}

expect_same( array(), rm_get_recipe_ingredients( 7 ) );
expect_same( array(), rm_get_recipe_steps( 7 ) );
expect_same( 1, rm_get_recipe_servings( 7 ) );
expect_same( 0, rm_get_recipe_total_minutes( 7 ) );
foreach ( array( null, array(), -7, '7.5', 'bogus', 8 ) as $bad_id ) {
	expect_same( array(), rm_get_recipe_ingredients( $bad_id ) );
	expect_same( array(), rm_get_recipe_steps( $bad_id ) );
	expect_same( 1, rm_get_recipe_servings( $bad_id ) );
	expect_same( 0, rm_get_recipe_total_minutes( $bad_id ) );
}
$metadata['rm_servings'] = '4';
$metadata['rm_prep_minutes'] = '10';
$metadata['rm_cook_minutes'] = '25';
expect_same( 4, rm_get_recipe_servings( '7' ) );
expect_same( 35, rm_get_recipe_total_minutes( 7 ) );
foreach ( array( '0', '-1', '1.5', array(), 'garbage', str_repeat( '9', 100 ) ) as $bad_value ) {
	$metadata['rm_servings'] = $bad_value;
	$metadata['rm_prep_minutes'] = $bad_value;
	expect_same( 1, rm_get_recipe_servings( 7 ) );
	expect_same( 25, rm_get_recipe_total_minutes( 7 ) );
}
$metadata['rm_prep_minutes'] = PHP_INT_MAX;
expect_same( PHP_INT_MAX, rm_get_recipe_total_minutes( 7 ) );

$metadata['_rm_ingredients'] = array(
	9 => array( 'name' => '<b>Flour</b>', 'mass_quantity' => '240', 'mass_unit' => 'g', 'volume_quantity' => '2', 'volume_unit' => 'cup', 'group' => 'Dough', 'note' => "Sifted\nwell" ),
	2 => array( 'name' => 'Milk', 'quantity' => '0.5', 'unit' => 'l' ),
	3 => array( 'name' => 'Invalid', 'mass_quantity' => '0', 'mass_unit' => 'g' ),
	4 => array( 'name' => array(), 'mass_quantity' => '10', 'mass_unit' => 'g' ),
	5 => 'bad row',
	6 => array( 'name' => 'Water', 'mass_quantity' => '1', 'mass_unit' => 'ml', 'volume_quantity' => '100', 'volume_unit' => 'ml' ),
);
$ingredients = rm_get_recipe_ingredients( 7 );
expect_same( array( 0, 1, 2 ), array_keys( $ingredients ) );
expect_same( array( 'name' => 'Flour', 'mass_quantity' => 240.0, 'mass_unit' => 'g', 'volume_quantity' => 2.0, 'volume_unit' => 'cup', 'optional_quantity' => null, 'optional_unit' => '', 'note' => 'Sifted well', 'group' => 'Dough' ), $ingredients[0] );
expect_same( 0.5, $ingredients[1]['volume_quantity'] );
expect_same( null, $ingredients[1]['mass_quantity'] );
expect_same( '', $ingredients[1]['group'] );
expect_same( null, $ingredients[2]['mass_quantity'] );
expect_same( '', $ingredients[2]['mass_unit'] );

$metadata['_rm_steps'] = array(
	8 => array( 'text' => "<b>Cut chicken.</b>\nSeason it.", 'image_id' => '123', 'group' => 'Chicken' ),
	2 => array( 'text' => 'Cook.', 'image_id' => '124' ),
	3 => array( 'text' => 'Serve.', 'image_id' => '125' ),
	4 => array( 'text' => 'Rest.', 'image_id' => '-123' ),
	5 => array( 'text' => 'Finish.', 'image_id' => '999' ),
	6 => array( 'text' => '' ),
	7 => array( 'text' => array() ),
);
$steps = rm_get_recipe_steps( 7 );
expect_same( array( 0, 1, 2, 3, 4 ), array_keys( $steps ) );
expect_same( array( 'text' => "Cut chicken.\nSeason it.", 'image_id' => 123, 'group' => 'Chicken' ), $steps[0] );
foreach ( array_slice( $steps, 1 ) as $step ) {
	expect_same( 0, $step['image_id'] );
	expect_same( '', $step['group'] );
}
$before = $metadata;
rm_get_recipe_ingredients( 7 );
rm_get_recipe_steps( 7 );
rm_get_recipe_servings( 7 );
rm_get_recipe_total_minutes( 7 );
expect_same( $before, $metadata );
$metadata['_rm_ingredients'] = 'broken';
$metadata['_rm_steps'] = false;
expect_same( array(), rm_get_recipe_ingredients( 7 ) );
expect_same( array(), rm_get_recipe_steps( 7 ) );
echo "PASS: accessor defaults, normalization, legacy rows, ordering, attachment checks, numeric parsing, read-only behavior\n";

// A blank Default unit counts the ingredient itself, including on save/read round trips.
function add_action( ...$args ) {}
class WP_Error {}
require dirname( __DIR__ ) . '/includes/mass-migration.php';
require dirname( __DIR__ ) . '/includes/volume-migration.php';
require dirname( __DIR__ ) . '/includes/admin/save-recipe.php';
$counted = array( 'name' => 'white onions (cubed)', 'optional_quantity' => '0.5', 'optional_unit' => '  ' );
$saved = \RecipeManager\Core\sanitize_ingredient_rows( array( $counted ) );
expect_same( true, is_array( $saved ) );
expect_same( '', $saved[0]['optional_unit'] );
$metadata['_rm_ingredients'] = $saved;
$read = rm_get_recipe_ingredients( 7 );
expect_same( 0.5, $read[0]['optional_quantity'] );
expect_same( '', $read[0]['optional_unit'] );
foreach ( array(
 array( 'name' => 'Onions' ),
 array( 'name' => 'Onions', 'optional_quantity' => '0' ),
 array( 'name' => 'Onions', 'optional_quantity' => '-1' ),
 array( 'name' => 'Onions', 'optional_quantity' => '1/2' ),
 array( 'name' => 'Garlic', 'optional_unit' => 'cloves' ),
 array( 'name' => 'Flour', 'mass_quantity' => '2' ),
 array( 'name' => 'Milk', 'volume_quantity' => '2' ),
) as $invalid ) {
 expect_same( true, \RecipeManager\Core\sanitize_ingredient_rows( array( $invalid ) ) instanceof WP_Error );
}
expect_same( array(), \RecipeManager\Core\sanitize_ingredient_rows( array( array() ) ) );
echo "PASS: unitless Default quantity save/read round trip and measurement validation\n";
