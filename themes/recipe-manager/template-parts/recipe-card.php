<?php
/** Shared recipe preview for discovery sections. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$description = rm_theme_text( get_the_ID(), 'rm_description' );
$prep = get_post_meta( get_the_ID(), 'rm_prep_minutes', true );
$minutes = is_numeric( $prep ) && is_finite( (float) $prep ) && (float) $prep > 0 ? (int) $prep : 0;
?>
<article <?php post_class( 'home-recipe-card' ); ?>>
	<a class="home-card-image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) :
			the_post_thumbnail( 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) );
		else : ?><span class="home-card-placeholder"><?php esc_html_e( 'From the kitchen', 'recipe-manager' ); ?></span><?php endif; ?>
	</a>
	<div class="home-card-body">
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( $description ) : ?><p class="home-card-description"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $description ), 40 ) ); ?></p><?php endif; ?>
		<?php if ( $minutes > 0 ) : ?>
		<p class="home-card-time">
			<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/></svg>
			<span><?php printf( esc_html__( '%s min prep', 'recipe-manager' ), esc_html( number_format_i18n( $minutes ) ) ); ?></span>
		</p>
		<?php endif; ?>
	</div>
</article>
