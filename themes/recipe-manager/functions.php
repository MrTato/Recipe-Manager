<?php
/** Theme presentation and assets. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'recipe-manager', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus( array( 'primary' => __( 'Primary navigation', 'recipe-manager' ) ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'recipe-manager', get_stylesheet_uri(), array(), filemtime( get_template_directory() . '/style.css' ) );
	if ( is_singular( 'rm_recipe' ) && function_exists( 'rm_get_recipe_ingredients' ) ) {
		wp_enqueue_script( 'recipe-manager-recipe', get_template_directory_uri() . '/assets/recipe.js', array(), filemtime( get_template_directory() . '/assets/recipe.js' ), true );
		wp_localize_script( 'recipe-manager-recipe', 'rmRecipeConfig', array(
			'units' => \RecipeManager\Core\get_units(),
			'measurementModes' => array(
				'original' => __( 'Default', 'recipe-manager' ),
				'mass' => __( 'Mass', 'recipe-manager' ),
				'volume' => __( 'Volume', 'recipe-manager' ),
				'optional' => __( 'Optional unit', 'recipe-manager' ),
			),
			'cycleLabel' => __( 'Measurements: %1$s. Switch to %2$s.', 'recipe-manager' ),
			'nextMeasurement' => __( 'Next measurement: %s', 'recipe-manager' ),
			'updated' => __( 'Ingredient quantities updated for %s servings.', 'recipe-manager' ),
		) );
	}
} );

/** Read plain multiline fields without requiring ACF at render time. */
function rm_theme_text( $id, $key ) {
	$value = get_post_meta( $id, $key, true );
	return is_string( $value ) ? $value : '';
}

/** Display sensible precision, retaining very small positive quantities. */
function rm_theme_quantity( $quantity ) {
	$decimals = $quantity < 0.01 ? min( 12, max( 3, (int) ceil( -log10( $quantity ) ) + 1 ) ) : 2;
	return rtrim( rtrim( number_format( $quantity, $decimals, '.', '' ), '0' ), '.' );
}

/** Output linked taxonomy terms, ignoring missing taxonomies and bad links. */
function rm_theme_terms( $id, $taxonomy ) {
	$terms = get_the_terms( $id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) { return; }
	foreach ( $terms as $term ) {
		$url = get_term_link( $term );
		if ( ! is_wp_error( $url ) ) {
			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a> ';
		}
	}
}
