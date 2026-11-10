<?php
/** Run with wp eval-file after loading WordPress. */
$rows = array(
 array( 'volume_quantity' => '1.5', 'volume_unit' => 'cup', 'optional_quantity' => '680', 'optional_unit' => 'g' ),
 array( 'quantity' => '2', 'unit' => 'tbsp' ),
 array( 'volume_quantity' => '2', 'volume_unit' => 'l' ),
 array( 'mass_quantity' => '1', 'mass_unit' => 'kg' ),
);
$rows[] = array( 'volume_quantity' => '3', 'volume_unit' => 'tsp' );
$converted = \RecipeManager\Core\normalize_metric_volume_rows( $rows );
if ( '15' !== $converted[4]['volume_quantity'] || 'ml' !== $converted[4]['volume_unit'] || '375' !== $converted[0]['volume_quantity'] || 'ml' !== $converted[0]['volume_unit']
 || '30' !== $converted[1]['quantity'] || 'ml' !== $converted[1]['unit']
 || $rows[2] !== $converted[2] || $rows[3] !== $converted[3]
 || '680' !== $converted[0]['optional_quantity']
 || $converted !== \RecipeManager\Core\normalize_metric_volume_rows( $converted ) ) {
 throw new \RuntimeException( 'Volume conversion regression.' );
}
echo "PASS: cups, tablespoons, legacy storage, liters, mass, default fields, and idempotence\n";
\RecipeManager\Core\migrate_recipe_volume_to_metric();
global $wpdb;
$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'rm_recipe'" );
$remaining = 0;
foreach ( $ids as $id ) {
 $stored = get_post_meta( $id, '_rm_ingredients', true );
 if ( is_array( $stored ) && $stored !== \RecipeManager\Core\normalize_metric_volume_rows( $stored ) ) {
  $remaining++;
 }
}
echo 'Migration complete: ' . get_option( 'rm_metric_volume_migrated_v1' ) . "\n";
echo "Recipes checked: " . count( $ids ) . "; still needing conversion: $remaining\n";
if ( $remaining || ! get_option( 'rm_metric_volume_migrated_v1' ) ) {
 throw new \RuntimeException( 'Migration incomplete.' );
}
