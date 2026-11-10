<?php
/** Run with wp eval-file after loading WordPress. */
$rows = array(
 array( 'mass_quantity' => '1.5', 'mass_unit' => 'lb', 'optional_quantity' => '680', 'optional_unit' => 'g' ),
 array( 'quantity' => '2', 'unit' => 'oz' ),
 array( 'mass_quantity' => '2', 'mass_unit' => 'kg' ),
 array( 'volume_quantity' => '1', 'volume_unit' => 'cup' ),
);
$converted = \RecipeManager\Core\normalize_metric_mass_rows( $rows );
if ( '680.388555' !== $converted[0]['mass_quantity'] || 'g' !== $converted[0]['mass_unit']
 || '56.69904625' !== $converted[1]['quantity'] || 'g' !== $converted[1]['unit']
 || $rows[2] !== $converted[2] || $rows[3] !== $converted[3]
 || '680' !== $converted[0]['optional_quantity']
 || $converted !== \RecipeManager\Core\normalize_metric_mass_rows( $converted ) ) {
 throw new \RuntimeException( 'Mass conversion regression.' );
}
echo "PASS: pounds, ounces, legacy storage, kilograms, volume, default fields, and idempotence\n";
\RecipeManager\Core\migrate_recipe_mass_to_metric();
global $wpdb;
$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'rm_recipe'" );
$remaining = 0;
foreach ( $ids as $id ) {
 $stored = get_post_meta( $id, '_rm_ingredients', true );
 if ( is_array( $stored ) && $stored !== \RecipeManager\Core\normalize_metric_mass_rows( $stored ) ) {
  $remaining++;
 }
}
echo 'Migration complete: ' . get_option( 'rm_metric_mass_migrated_v1' ) . "\n";
echo "Recipes checked: " . count( $ids ) . "; still needing conversion: $remaining\n";
if ( $remaining || ! get_option( 'rm_metric_mass_migrated_v1' ) ) {
 throw new \RuntimeException( 'Migration incomplete.' );
}
