<?php
/** Shared recipe preview for discovery sections. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$description = rm_theme_text( get_the_ID(), 'rm_description' );
$prep = get_post_meta( get_the_ID(), 'rm_prep_minutes', true );
$minutes = is_numeric( $prep ) && is_finite( (float) $prep ) && (float) $prep > 0 ? (int) $prep : 0;
$cook = get_post_meta( get_the_ID(), 'rm_cook_minutes', true );
$cook_minutes = is_numeric( $cook ) && is_finite( (float) $cook ) && (float) $cook >= 0 ? (int) $cook : null;
?>
<article <?php post_class( 'home-recipe-card' ); ?>>
	<?php if ( ! empty( $args['featured'] ) ) : ?>
		<span class="home-card-featured-badge">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false"><path d="m2 6 5 4 5-7 5 7 5-4-3 13H5L2 6Z"/></svg>
			<?php esc_html_e( 'Featured', 'recipe-manager' ); ?>
		</span>
	<?php endif; ?>
	<a class="home-card-image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) :
			the_post_thumbnail( 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) );
		else : ?><span class="home-card-placeholder"><?php esc_html_e( 'From the kitchen', 'recipe-manager' ); ?></span><?php endif; ?>
	</a>
	<div class="home-card-body">
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( $description && empty( $args['compact'] ) ) : ?><p class="home-card-description"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $description ), 40 ) ); ?></p><?php endif; ?>
		<?php if ( $minutes > 0 || null !== $cook_minutes ) : ?>
		<div class="home-card-times">
		<?php if ( $minutes > 0 ) : ?>
		<p class="home-card-time">
			<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/></svg>
			<span><?php printf( esc_html__( '%s min prep', 'recipe-manager' ), esc_html( number_format_i18n( $minutes ) ) ); ?></span>
		</p>
		<?php endif; ?>
		<?php if ( null !== $cook_minutes ) : ?>
		<p class="home-card-time">
			<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 11h14v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7Zm-3 2h3m14 0h3M4 8h16M9 5V3m6 2V3"/></svg>
			<span><?php printf( esc_html__( '%s min cook', 'recipe-manager' ), esc_html( number_format_i18n( $cook_minutes ) ) ); ?></span>
		</p>
		<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</article>
