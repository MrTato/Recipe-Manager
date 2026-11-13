<?php
/**
 * Public, read-only recipe accessors for templates and serving calculations.
 *
 * These helpers read raw WordPress metadata, so ACF need not be active.
 * Escape returned plain text for its output context when rendering templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read metadata only from a valid recipe. Missing or invalid values return ''.
 *
 * @internal
 * @param int|string $recipe_id Recipe post ID.
 * @param string     $key Metadata key.
 * @return mixed
 */
function rm_recipe_meta_value( $recipe_id, $key ) {
	$id = rm_recipe_meta_integer( $recipe_id );
	if ( ! $id || 'rm_recipe' !== get_post_type( $id ) ) {
		return '';
	}
	return get_post_meta( $id, $key, true );
}

/**
 * Parse a nonnegative integer without coercing arrays, negatives, or fractions.
 *
 * @internal
 * @return int Zero for missing, malformed, or out-of-range values.
 */
function rm_recipe_meta_integer( $value ) {
	if ( is_int( $value ) ) {
		return max( 0, $value );
	}
	if ( ! is_string( $value ) || ! preg_match( '/\A[0-9]+\z/', $value ) ) {
		return 0;
	}
	// Normalize leading zeroes before checking for integer overflow.
	$number = filter_var( ltrim( $value, '0' ) ?: '0', FILTER_VALIDATE_INT );
	return false === $number ? 0 : $number;
}

/**
 * Normalize an optional plain-text field.
 *
 * @internal
 * @return string
 */
function rm_recipe_meta_text( $value ) {
	return is_string( $value ) ? sanitize_text_field( $value ) : '';
}

/**
 * Normalize a positive measurement with a unit of the requested type.
 *
 * @internal
 * @return array{0: float|null, 1: string}
 */
function rm_recipe_meta_measurement( $quantity, $unit, $type, $units ) {
	if ( ! is_string( $unit ) || ( 'optional' !== $type && ( ! isset( $units[ $unit ] ) || $units[ $unit ]['type'] !== $type ) )
		|| ( ! is_string( $quantity ) && ! is_int( $quantity ) && ! is_float( $quantity ) ) ) {
		return array( null, '' );
	}
	if ( is_string( $quantity ) && ! preg_match( '/\A(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)\z/', trim( $quantity ) ) ) {
		return array( null, '' );
	}
	$number = (float) $quantity;
	return is_finite( $number ) && $number > 0 ? array( $number, $unit ) : array( null, '' );
}

/**
 * Get ordered ingredients with consistent fields and numeric quantities.
 *
 * Invalid measurements become null/empty pairs. Rows without a name or any
 * valid measurement are omitted. Older quantity/unit rows are read as mass
 * or volume measurements without rewriting the original stored collection.
 *
 * @param int|string $recipe_id Recipe post ID.
 * @return array<int, array{name: string, mass_quantity: float|null, mass_unit: string, volume_quantity: float|null, volume_unit: string, optional_quantity: float|null, optional_unit: string, note: string, group: string}>
 */
function rm_get_recipe_ingredients( $recipe_id ) {
	$stored = rm_recipe_meta_value( $recipe_id, '_rm_ingredients' );
	if ( ! is_array( $stored ) ) {
		return array();
	}
	$units = \RecipeManager\Core\get_units();
	$ingredients = array();
	foreach ( $stored as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$name = rm_recipe_meta_text( $row['name'] ?? '' );
		if ( '' === $name ) {
			continue;
		}
		$legacy_unit = $row['unit'] ?? '';
		$legacy_type = is_string( $legacy_unit ) ? ( $units[ $legacy_unit ]['type'] ?? '' ) : '';
		$measurements = array();
		foreach ( array( 'mass', 'volume', 'optional' ) as $type ) {
			$quantity = $row[ $type . '_quantity' ] ?? null;
			$unit = $row[ $type . '_unit' ] ?? '';
			if ( ! array_key_exists( $type . '_quantity', $row ) && ! array_key_exists( $type . '_unit', $row ) && $legacy_type === $type ) {
				$quantity = $row['quantity'] ?? null;
				$unit = $legacy_unit;
			}
			if ( 'optional' === $type ) {
				$unit = rm_recipe_meta_text( $unit );
			}
			list( $quantity, $unit ) = rm_recipe_meta_measurement( $quantity, $unit, $type, $units );
			$measurements[ $type . '_quantity' ] = $quantity;
			$measurements[ $type . '_unit' ] = $unit;
		}
		if ( null === $measurements['mass_quantity'] && null === $measurements['volume_quantity'] && null === $measurements['optional_quantity'] ) {
			continue;
		}
		$ingredients[] = array_merge(
			array( 'name' => $name ),
			$measurements,
			array( 'note' => rm_recipe_meta_text( $row['note'] ?? '' ), 'group' => rm_recipe_meta_text( $row['group'] ?? '' ) )
		);
	}
	return $ingredients;
}

/**
 * Get ordered steps; derive displayed step numbers from the returned indexes.
 *
 * Empty/malformed text rows are omitted. Missing, deleted, or non-image
 * attachments become image_id 0. Group labels default to an empty string.
 *
 * @param int|string $recipe_id Recipe post ID.
 * @return array<int, array{text: string, image_id: int, group: string}>
 */
function rm_get_recipe_steps( $recipe_id ) {
	$stored = rm_recipe_meta_value( $recipe_id, '_rm_steps' );
	if ( ! is_array( $stored ) ) {
		return array();
	}
	$steps = array();
	foreach ( $stored as $row ) {
		if ( ! is_array( $row ) || ! is_string( $row['text'] ?? null ) ) {
			continue;
		}
		$text = sanitize_textarea_field( $row['text'] );
		if ( '' === trim( $text ) ) {
			continue;
		}
		$image_id = rm_recipe_meta_integer( $row['image_id'] ?? 0 );
		if ( $image_id && ( 'attachment' !== get_post_type( $image_id ) || ! wp_attachment_is_image( $image_id ) || 'trash' === get_post_status( $image_id ) ) ) {
			$image_id = 0;
		}
		$steps[] = array( 'text' => $text, 'image_id' => $image_id, 'group' => rm_recipe_meta_text( $row['group'] ?? '' ) );
	}
	return $steps;
}

/**
 * Get the original default servings, with a safe nonzero scaling denominator.
 *
 * @param int|string $recipe_id Recipe post ID.
 * @return int Positive integer; defaults to 1 for missing or invalid values.
 */
function rm_get_recipe_servings( $recipe_id ) {
	return max( 1, rm_recipe_meta_integer( rm_recipe_meta_value( $recipe_id, 'rm_servings' ) ) );
}

/**
 * Get preparation plus cooking minutes. Missing/invalid components count as 0.
 *
 * @param int|string $recipe_id Recipe post ID.
 * @return int Nonnegative total, capped at PHP_INT_MAX to avoid overflow.
 */
function rm_get_recipe_total_minutes( $recipe_id ) {
	$prep = rm_recipe_meta_integer( rm_recipe_meta_value( $recipe_id, 'rm_prep_minutes' ) );
	$cook = rm_recipe_meta_integer( rm_recipe_meta_value( $recipe_id, 'rm_cook_minutes' ) );
	return $prep > PHP_INT_MAX - $cook ? PHP_INT_MAX : $prep + $cook;
}
