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
	if ( is_post_type_archive( 'rm_recipe' ) || is_tax( array( 'rm_recipe_category', 'rm_recipe_tag' ) ) || ( is_search() && 'rm_recipe' === get_query_var( 'post_type' ) ) ) {
		wp_enqueue_script( 'recipe-manager-browse', get_template_directory_uri() . '/assets/browse.js', array(), filemtime( get_template_directory() . '/assets/browse.js' ), true );
	}
	if ( is_singular( 'rm_recipe' ) && function_exists( 'rm_get_recipe_ingredients' ) ) {
		wp_enqueue_script( 'recipe-manager-recipe', get_template_directory_uri() . '/assets/recipe.js', array(), filemtime( get_template_directory() . '/assets/recipe.js' ), true );
		wp_localize_script( 'recipe-manager-recipe', 'rmRecipeConfig', array(
			'units' => \RecipeManager\Core\get_units(),
			'ingredientOf' => __( 'of', 'recipe-manager' ),
			'measurementModes' => array(
				'original' => __( 'Default', 'recipe-manager' ),
				'mass' => __( 'Mass', 'recipe-manager' ),
				'volume' => __( 'Volume', 'recipe-manager' ),
			),
			'cycleLabel' => __( 'Measurements: %1$s. Switch to %2$s.', 'recipe-manager' ),
			'nextMeasurement' => __( 'Next measurement: %s', 'recipe-manager' ),
			'updated' => __( 'Ingredient quantities updated for %s servings.', 'recipe-manager' ),
		) );
	}
} );

/** Provision editable homepage copy once, without replacing an existing static front page. */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'rm_theme_homepage_setup' ) ) {
		return;
	}
	if ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) {
		update_option( 'rm_theme_homepage_setup', 1 );
		return;
	}
	$home = get_page_by_path( 'home', OBJECT, 'page' );
	if ( $home && 'publish' !== $home->post_status ) {
		// Leave existing unpublished content for its author to review.
		return;
	}
	$home_id = $home ? $home->ID : wp_insert_post( array(
		'post_type' => 'page',
		'post_status' => 'publish',
		'post_title' => __( 'Home', 'recipe-manager' ),
		'post_name' => 'home',
		'post_content' => '<p>' . esc_html__( 'Explore recipes from my kitchen, find something delicious, and make it your own.', 'recipe-manager' ) . '</p>',
	), true );
	if ( is_wp_error( $home_id ) || ! $home_id ) { return; }
	update_option( 'page_on_front', $home_id );
	update_option( 'show_on_front', 'page' );
	update_option( 'rm_theme_homepage_setup', 1 );
} );

/** Read plain multiline fields without requiring ACF at render time. */
function rm_theme_text( $id, $key ) {
	$value = get_post_meta( $id, $key, true );
	return is_string( $value ) ? $value : '';
}

/** Display sensible precision, retaining very small positive quantities. */
function rm_theme_quantity( $quantity, $unit = null ) {
	// Match only common fractions within 0.001, with at most 1% error.
	if ( null !== $unit && ! in_array( strtolower( trim( $unit ) ), array( 'g', 'kg', 'ml', 'l' ), true ) ) {
		$whole = floor( $quantity );
		$fractions = array( '⅛' => 1 / 8, '¼' => 1 / 4, '⅓' => 1 / 3, '⅜' => 3 / 8, '½' => 1 / 2, '⅝' => 5 / 8, '⅔' => 2 / 3, '¾' => 3 / 4, '⅞' => 7 / 8 );
		foreach ( $fractions as $symbol => $fraction ) {
			if ( abs( $quantity - $whole - $fraction ) <= min( 0.001, $quantity * 0.01 ) ) {
				return ( $whole > 0 ? (string) $whole : '' ) . $symbol;
			}
		}
	}
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
