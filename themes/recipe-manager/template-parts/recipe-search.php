<?php
/** Shared floral recipe search; optionally embedded in the browse filter form. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="home-search" aria-label="<?php esc_attr_e( 'Recipe search', 'recipe-manager' ); ?>">
		<?php if ( empty( $args['embedded'] ) ) : ?><form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"><?php endif; ?>
			<label class="screen-reader-text" for="recipe-search"><?php esc_html_e( 'Search recipes', 'recipe-manager' ); ?></label>
			<div class="home-search-controls">
				<svg class="home-search-icon" viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/></svg>
				<input id="recipe-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Search recipes…', 'recipe-manager' ); ?>" value="<?php echo esc_attr( $args['value'] ?? '' ); ?>">
				<input type="hidden" name="post_type" value="rm_recipe">
				<svg class="home-search-flourish" viewBox="0 0 130 40" width="130" height="40" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 32C28 16 55 44 82 27 98 17 104 5 97 5c-5 0-5 8-5 8s-3-10-8-8c-9 4 7 31 24 27 7-1 12-2 19-5"/></svg>
				<button type="submit" class="button-primary"><?php esc_html_e( 'Search', 'recipe-manager' ); ?></button>
			</div>
		<?php if ( empty( $args['embedded'] ) ) : ?></form><?php endif; ?>
	</section>
