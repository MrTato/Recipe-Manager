<?php
/** Persistent suggestions for the freely editable Default unit field. */
namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_protected_default_units() {
	return array( 'g', 'kg', 'ml', 'l', 'lb', 'oz', 'tbsp', 'tsp', 'cup', 'handful', 'piece', 'pinch', 'slice' );
}

function remember_default_units( $rows, $previous_rows = array() ) {
	$previous_names = array_column( array_filter( $previous_rows, 'is_array' ), 'optional_unit' );
	$saved = get_option( 'rm_default_unit_names', array() );
	$names = is_array( $saved ) ? $saved : array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || ! is_string( $row['optional_unit'] ?? null ) ) {
			continue;
		}
		$name = sanitize_text_field( $row['optional_unit'] );
		if ( '' !== $name && ! in_array( $name, $names, true ) && ! in_array( $name, $previous_names, true ) ) {
			$names[] = $name;
		}
	}
	if ( $names !== $saved ) {
		update_option( 'rm_default_unit_names', $names, false );
	}
}

function get_default_unit_suggestions() {
	// Collect names already in recipes once, including drafts and trashed recipes.
	if ( ! get_option( 'rm_default_units_seeded_v1' ) ) {
		global $wpdb;
		$stored = $wpdb->get_col( $wpdb->prepare(
			"SELECT m.meta_value FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE p.post_type = %s AND m.meta_key = %s",
			'rm_recipe', '_rm_ingredients'
		) );
		if ( ! $wpdb->last_error ) {
			foreach ( $stored as $value ) {
				$rows = maybe_unserialize( $value );
				if ( is_array( $rows ) ) {
					remember_default_units( $rows );
				}
			}
			update_option( 'rm_default_units_seeded_v1', 1, false );
		}
	}
	$units = get_units();
	if ( ! get_option( 'rm_default_unit_catalog_v1' ) ) {
		$names = get_option( 'rm_default_unit_names', array() );
		update_option( 'rm_default_unit_names', array_values( array_unique( array_merge( array_keys( $units ), $names ) ) ), false );
		update_option( 'rm_default_unit_catalog_v1', 1, false );
	}
	$suggestions = array();
	foreach ( get_option( 'rm_default_unit_names', array() ) as $name ) {
		$suggestions[ $name ] = $units[ $name ]['label'] ?? $name;
	}
	// Protected units remain available even if an older manager deleted them.
	foreach ( get_protected_default_units() as $code ) {
		$suggestions[ $code ] = $units[ $code ]['label'];
	}
	return $suggestions;
}
