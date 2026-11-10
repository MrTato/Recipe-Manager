<?php
/** Persistent suggestions for the freely editable Default unit field. */
namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function remember_default_units( $rows ) {
	$saved = get_option( 'rm_default_unit_names', array() );
	$names = is_array( $saved ) ? $saved : array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || ! is_string( $row['optional_unit'] ?? null ) ) {
			continue;
		}
		$name = sanitize_text_field( $row['optional_unit'] );
		if ( '' !== $name && ! in_array( $name, $names, true ) ) {
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
	$suggestions = array();
	foreach ( get_units() as $code => $unit ) {
		$suggestions[ $code ] = $unit['label'];
	}
	foreach ( get_option( 'rm_default_unit_names', array() ) as $name ) {
		if ( ! isset( $suggestions[ $name ] ) ) {
			$suggestions[ $name ] = $name;
		}
	}
	return $suggestions;
}
