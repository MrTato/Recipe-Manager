<?php
/** Convert historic mass measurements to metric without changing other fields. */
namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function normalize_metric_mass_rows( $rows ) {
	$units = get_units();
	foreach ( $rows as &$row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		// Also update the original single-measurement storage format.
		foreach ( array( array( 'mass_quantity', 'mass_unit' ), array( 'quantity', 'unit' ) ) as $fields ) {
			list( $quantity_key, $unit_key ) = $fields;
			$unit = $row[ $unit_key ] ?? '';
			$quantity = $row[ $quantity_key ] ?? null;
			if ( ! is_string( $unit ) || ! isset( $units[ $unit ] ) || 'mass' !== $units[ $unit ]['type']
				|| in_array( $unit, array( 'g', 'kg' ), true ) || ! is_numeric( $quantity ) ) {
				continue;
			}
			$grams = (float) $quantity * $units[ $unit ]['to_base_factor'];
			if ( ! is_finite( $grams ) || $grams <= 0 ) {
				continue;
			}
			$row[ $quantity_key ] = rtrim( rtrim( number_format( $grams, 9, '.', '' ), '0' ), '.' );
			$row[ $unit_key ] = 'g';
		}
	}
	unset( $row );
	return $rows;
}

/** Idempotent, paged migration covering all recipe statuses. Retry failed writes. */
function migrate_recipe_mass_to_metric() {
	if ( get_option( 'rm_metric_mass_migrated_v1' ) ) {
		return;
	}
	global $wpdb;
	$last_id = 0;
	$success = true;
	do {
		$ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND ID > %d ORDER BY ID LIMIT 100",
			'rm_recipe', $last_id
		) );
		if ( $wpdb->last_error ) {
			return;
		}
		foreach ( $ids as $post_id ) {
			$last_id = (int) $post_id;
			$rows = get_post_meta( $post_id, '_rm_ingredients', true );
			if ( ! is_array( $rows ) ) {
				continue;
			}
			$converted = normalize_metric_mass_rows( $rows );
			if ( $converted !== $rows && ! update_post_meta( $post_id, '_rm_ingredients', wp_slash( $converted ) ) ) {
				$success = false;
			}
		}
	} while ( count( $ids ) === 100 );
	if ( $success ) {
		update_option( 'rm_metric_mass_migrated_v1', 1, false );
	}
}
add_action( 'init', __NAMESPACE__ . '\\migrate_recipe_mass_to_metric', 20 );
