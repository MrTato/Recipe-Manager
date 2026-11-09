<?php
/** Public recipe discovery queries. */
namespace RecipeManager\Core;
if ( ! defined( 'ABSPATH' ) ) { exit; }

function browse_state() {
	$categories = isset( $_GET['rm_category'] ) ? (array) wp_unslash( $_GET['rm_category'] ) : array();
	$categories = array_values( array_unique( array_filter( array_map( static function ( $id ) {
		return is_scalar( $id ) ? absint( $id ) : 0;
	}, array_slice( $categories, 0, 100 ) ) ) ) );
	if ( is_tax( 'rm_recipe_category' ) ) { $categories[] = get_queried_object_id(); }
	$state = array( 'categories' => array_unique( $categories ), 'min' => '', 'max' => '', 'sort' => 'newest' );
	foreach ( array( 'min', 'max' ) as $key ) {
		$value = $_GET[ 'rm_' . $key ] ?? '';
		if ( is_string( $value ) && ctype_digit( $value ) ) { $state[ $key ] = min( 100000, (int) $value ); }
	}
	if ( '' !== $state['min'] && '' !== $state['max'] && $state['min'] > $state['max'] ) {
		$temp = $state['min']; $state['min'] = $state['max']; $state['max'] = $temp;
	}
	$sort = $_GET['rm_sort'] ?? '';
	if ( is_string( $sort ) && in_array( $sort, array( 'newest', 'oldest', 'title' ), true ) ) { $state['sort'] = $sort; }
	return $state;
}

add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) { return; }
	$is_recipe_search = $query->is_search() && 'rm_recipe' === $query->get( 'post_type' );
	if ( ! $query->is_post_type_archive( 'rm_recipe' ) && ! $query->is_tax( array( 'rm_recipe_category', 'rm_recipe_tag' ) ) && ! $is_recipe_search ) { return; }
	$query->set( 'rm_recipe_browse', true );
	$query->set( 'post_type', 'rm_recipe' );
	$query->set( 'post_status', 'publish' );
	$query->set( 'posts_per_page', 9 );
	$state = browse_state();
	if ( $state['categories'] ) {
		$tax = $query->get( 'tax_query' ) ?: array();
		$tax[] = array( 'taxonomy' => 'rm_recipe_category', 'field' => 'term_id', 'terms' => $state['categories'], 'operator' => 'IN' );
		$query->set( 'tax_query', $tax );
	}
	$query->set( 'rm_time_min', $state['min'] );
	$query->set( 'rm_time_max', $state['max'] );
	$order = 'oldest' === $state['sort'] ? 'ASC' : 'DESC';
	$query->set( 'orderby', 'title' === $state['sort'] ? array( 'title' => 'ASC', 'ID' => 'ASC' ) : array( 'date' => $order, 'ID' => $order ) );
} );

add_filter( 'posts_where', function ( $where, $query ) {
	if ( ! $query->get( 'rm_recipe_browse' ) ) { return $where; }
	$min = $query->get( 'rm_time_min' ); $max = $query->get( 'rm_time_max' );
	if ( '' === $min && '' === $max ) { return $where; }
	global $wpdb;
	// Correlated subqueries avoid duplicate results and include missing components as zero.
	$total = "(SELECT COALESCE(SUM(CASE WHEN tm.meta_value REGEXP '^[0-9]+$' THEN CAST(tm.meta_value AS UNSIGNED) ELSE 0 END),0) FROM {$wpdb->postmeta} tm WHERE tm.post_id = {$wpdb->posts}.ID AND tm.meta_key IN ('rm_prep_minutes','rm_cook_minutes'))";
	$where .= " AND $total > 0";
	if ( '' !== $min ) { $where .= $wpdb->prepare( " AND $total >= %d", $min ); }
	if ( '' !== $max ) { $where .= $wpdb->prepare( " AND $total <= %d", $max ); }
	return $where;
}, 10, 2 );

add_filter( 'posts_search', function ( $search, $query ) {
	if ( ! $query->get( 'rm_recipe_browse' ) || ! $query->is_search() || ! $search ) { return $search; }
	global $wpdb;
	$clauses = array();
	foreach ( (array) $query->get( 'search_terms' ) as $term ) {
		$exclude = '-' === substr( $term, 0, 1 );
		if ( $exclude ) { $term = substr( $term, 1 ); }
		$like = '%' . $wpdb->esc_like( $term ) . '%';
		$clause = $wpdb->prepare( "({$wpdb->posts}.post_title LIKE %s OR EXISTS (SELECT 1 FROM {$wpdb->postmeta} sm WHERE sm.post_id = {$wpdb->posts}.ID AND sm.meta_key IN ('rm_description','_rm_ingredients') AND sm.meta_value LIKE %s) OR EXISTS (SELECT 1 FROM {$wpdb->term_relationships} sr INNER JOIN {$wpdb->term_taxonomy} st ON st.term_taxonomy_id = sr.term_taxonomy_id INNER JOIN {$wpdb->terms} sn ON sn.term_id = st.term_id WHERE sr.object_id = {$wpdb->posts}.ID AND st.taxonomy IN ('rm_recipe_category','rm_recipe_tag') AND sn.name LIKE %s))", $like, $like, $like );
		$clauses[] = $exclude ? "NOT $clause" : $clause;
	}
	if ( ! $clauses ) { return $search; }
	return ' AND (' . implode( ' AND ', $clauses ) . ") AND {$wpdb->posts}.post_password = '' ";
}, 10, 2 );
