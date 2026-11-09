<?php
/** Homepage category shortcuts and an expandable category directory. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! taxonomy_exists( 'rm_recipe_category' ) ) { return; }
$categories = get_terms( array( 'taxonomy' => 'rm_recipe_category', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ) );
if ( is_wp_error( $categories ) || ! $categories ) { return; }
?>
<nav class="home-category-cards" aria-label="<?php esc_attr_e( 'Recipe categories', 'recipe-manager' ); ?>">
	<?php foreach ( array_slice( $categories, 0, 6 ) as $category ) :
		$url = get_term_link( $category );
		if ( is_wp_error( $url ) ) { continue; }
		$image = wp_get_attachment_image( (int) get_term_meta( $category->term_id, 'rm_category_image_id', true ), 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
		<a class="home-category-card" href="<?php echo esc_url( $url ); ?>">
			<?php if ( $image ) : echo $image; else : ?><span class="home-category-fallback" aria-hidden="true">♡</span><?php endif; ?>
			<span><?php echo esc_html( $category->name ); ?></span>
		</a>
	<?php endforeach; ?>
	<details class="home-category-more">
		<summary class="home-category-card">
			<svg width="40" height="40" viewBox="0 0 30 30" fill="currentColor" aria-hidden="true" focusable="false"><circle cx="6" cy="6" r="3"/><circle cx="15" cy="6" r="3"/><circle cx="24" cy="6" r="3"/><circle cx="6" cy="15" r="3"/><circle cx="15" cy="15" r="3"/><circle cx="24" cy="15" r="3"/><circle cx="6" cy="24" r="3"/><circle cx="15" cy="24" r="3"/><circle cx="24" cy="24" r="3"/></svg>
			<span><?php esc_html_e( 'More Categories', 'recipe-manager' ); ?></span>
		</summary>
		<ul class="home-category-directory">
			<?php foreach ( $categories as $category ) : $url = get_term_link( $category );
				if ( is_wp_error( $url ) ) { continue; } ?>
				<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</details>
</nav>
