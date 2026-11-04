<?php
/**
 * Shared measurement unit registry for recipe editors and presentation.
 */

namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get supported units keyed by their stable storage codes.
 *
 * Mass factors convert to grams; volume factors convert to milliliters.
 * Recipe Manager uses a metric cooking convention: 1 teaspoon = 5 ml,
 * 1 tablespoon = 15 ml, and 1 cup = 250 ml. These are explicit project
 * definitions, not US customary measures or the Australian 20 ml tablespoon.
 *
 * A null factor means no general conversion is supported. In particular,
 * count and informal units cannot be converted to mass or volume without
 * additional ingredient-specific information. Factors only relate units
 * within the same measurement type; they do not provide ingredient density.
 *
 * Admin unit dropdowns and frontend conversion data should use this registry
 * rather than maintaining separate unit lists. Store the array keys in recipe
 * metadata, never the translated labels.
 *
 * @return array<string, array{label: string, type: string, to_base_factor: int|null}>
 */
function get_units() {
	return array(
		'g'       => array(
			'label'          => __( 'Grams', 'recipe-manager-core' ),
			'type'           => 'mass',
			'to_base_factor' => 1,
		),
		'kg'      => array(
			'label'          => __( 'Kilograms', 'recipe-manager-core' ),
			'type'           => 'mass',
			'to_base_factor' => 1000,
		),
		'ml'      => array(
			'label'          => __( 'Milliliters', 'recipe-manager-core' ),
			'type'           => 'volume',
			'to_base_factor' => 1,
		),
		'l'       => array(
			'label'          => __( 'Liters', 'recipe-manager-core' ),
			'type'           => 'volume',
			'to_base_factor' => 1000,
		),
		'tsp'     => array(
			'label'          => __( 'Teaspoons', 'recipe-manager-core' ),
			'type'           => 'volume',
			'to_base_factor' => 5,
		),
		'tbsp'    => array(
			'label'          => __( 'Tablespoons', 'recipe-manager-core' ),
			'type'           => 'volume',
			'to_base_factor' => 15,
		),
		'cup'     => array(
			'label'          => __( 'Cups', 'recipe-manager-core' ),
			'type'           => 'volume',
			'to_base_factor' => 250,
		),
		'piece'   => array(
			'label'          => __( 'Pieces', 'recipe-manager-core' ),
			'type'           => 'count',
			'to_base_factor' => null,
		),
		'clove'   => array(
			'label'          => __( 'Cloves', 'recipe-manager-core' ),
			'type'           => 'count',
			'to_base_factor' => null,
		),
		'slice'   => array(
			'label'          => __( 'Slices', 'recipe-manager-core' ),
			'type'           => 'count',
			'to_base_factor' => null,
		),
		'pinch'   => array(
			'label'          => __( 'Pinches', 'recipe-manager-core' ),
			'type'           => 'informal',
			'to_base_factor' => null,
		),
		'handful' => array(
			'label'          => __( 'Handfuls', 'recipe-manager-core' ),
			'type'           => 'informal',
			'to_base_factor' => null,
		),
	);
}
