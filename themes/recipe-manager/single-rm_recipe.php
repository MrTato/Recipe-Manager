<?php
/** Structured recipe, readable with or without JavaScript. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<main id="main" class="site-main">
<?php while ( have_posts() ) : the_post(); ?>
<?php if ( post_password_required() ) : ?>
	<h1><?php the_title(); ?></h1>
	<?php echo get_the_password_form(); continue; ?>
<?php endif;
if ( ! function_exists( 'rm_get_recipe_ingredients' ) ) : ?>
	<h1><?php the_title(); ?></h1><?php the_content(); continue; ?>
<?php endif;
$id = get_the_ID();
$ingredients = rm_get_recipe_ingredients( $id );
$steps = rm_get_recipe_steps( $id );
$servings = rm_get_recipe_servings( $id );
$description = rm_theme_text( $id, 'rm_description' );
$notes = rm_theme_text( $id, 'rm_notes' );
$units = \RecipeManager\Core\get_units();
?>
<article <?php post_class( 'recipe' ); ?> data-recipe data-servings="<?php echo esc_attr( $servings ); ?>">
	<header class="recipe-hero <?php echo has_post_thumbnail() ? '' : 'recipe-hero--text'; ?>">
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="recipe-photo"><?php the_post_thumbnail( 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></div>
		<?php endif; ?>
		<div class="recipe-intro">
			<div class="recipe-categories"><?php rm_theme_terms( $id, 'rm_recipe_category' ); ?></div>
			<h1><?php the_title(); ?></h1>
			<?php if ( '' !== trim( $description ) ) : ?><div class="recipe-description"><?php echo nl2br( esc_html( $description ) ); ?></div><?php endif; ?>
			<dl class="recipe-facts">
				<div><dt><?php esc_html_e( 'Servings', 'recipe-manager' ); ?></dt><dd data-servings-display><?php echo esc_html( $servings ); ?></dd></div>
				<?php foreach ( array( 'rm_prep_minutes' => __( 'Prep', 'recipe-manager' ), 'rm_cook_minutes' => __( 'Cook', 'recipe-manager' ), 'total' => __( 'Total', 'recipe-manager' ) ) as $key => $label ) :
					$minutes = 'total' === $key ? rm_get_recipe_total_minutes( $id ) : rm_recipe_meta_integer( get_post_meta( $id, $key, true ) ); ?>
				<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( sprintf( __( '%s min', 'recipe-manager' ), number_format_i18n( $minutes ) ) ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		</div>
	</header>
	<div class="recipe-toolbar" data-controls hidden>
		<div><label for="recipe-servings"><?php esc_html_e( 'Servings', 'recipe-manager' ); ?></label>
		<input id="recipe-servings" type="number" min="1" max="9007199254740991" step="1" value="<?php echo esc_attr( $servings ); ?>"></div>
		<div><label for="recipe-measurement"><?php esc_html_e( 'Measurements', 'recipe-manager' ); ?></label>
		<select id="recipe-measurement"><option value="original"><?php esc_html_e( 'As written', 'recipe-manager' ); ?></option><option value="mass"><?php esc_html_e( 'Mass', 'recipe-manager' ); ?></option><option value="volume"><?php esc_html_e( 'Volume', 'recipe-manager' ); ?></option></select></div>
		<?php foreach ( array( 'mass' => __( 'Mass unit', 'recipe-manager' ), 'volume' => __( 'Volume unit', 'recipe-manager' ) ) as $type => $label ) : ?>
		<div><label for="recipe-<?php echo esc_attr( $type ); ?>-unit"><?php echo esc_html( $label ); ?></label>
		<select id="recipe-<?php echo esc_attr( $type ); ?>-unit"><option value="original"><?php esc_html_e( 'As written', 'recipe-manager' ); ?></option>
		<?php foreach ( $units as $code => $unit ) : if ( $type !== $unit['type'] ) { continue; } ?>
		<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $unit['label'] ); ?></option>
		<?php endforeach; ?></select></div>
		<?php endforeach; ?>
		<button type="button" data-reset><?php esc_html_e( 'Reset', 'recipe-manager' ); ?></button>
		<button type="button" class="button-primary" data-print><?php esc_html_e( 'Print recipe', 'recipe-manager' ); ?></button>
		<p class="measurement-help"><?php echo esc_html( sprintf( __( 'Mass and volume switch only where the author supplied both. Other ingredients keep their available measurement. Cups = %1$s ml; tbsp = %2$s ml; tsp = %3$s ml.', 'recipe-manager' ), $units['cup']['to_base_factor'], $units['tbsp']['to_base_factor'], $units['tsp']['to_base_factor'] ) ); ?></p>
	</div>
	<p class="screen-reader-text" role="status" aria-live="polite" data-status></p>
	<div class="recipe-body">
		<section class="recipe-ingredients" aria-labelledby="ingredients-heading">
			<h2 id="ingredients-heading"><?php esc_html_e( 'Ingredients', 'recipe-manager' ); ?></h2>
			<?php $previous_group = null; foreach ( $ingredients as $row ) :
				if ( $row['group'] !== $previous_group ) {
					if ( null !== $previous_group ) { echo '</ul>'; }
					if ( '' !== $row['group'] ) { echo '<h3 class="group-heading">' . esc_html( $row['group'] ) . '</h3>'; }
					echo '<ul class="ingredient-list">';
					$previous_group = $row['group'];
				}
				$type = null !== $row['mass_quantity'] ? 'mass' : ( null !== $row['volume_quantity'] ? 'volume' : 'optional' ); ?>
				<li data-ingredient="<?php echo esc_attr( wp_json_encode( $row ) ); ?>"><label class="ingredient-label"><input class="ingredient-check" type="checkbox"><span class="ingredient-copy"><span class="ingredient-amount"><?php echo esc_html( rm_theme_quantity( $row[ $type . '_quantity' ] ) . ' ' . $row[ $type . '_unit' ] ); ?></span> <span><?php echo esc_html( $row['name'] ); ?></span><?php if ( $row['note'] ) : ?><small><?php echo esc_html( $row['note'] ); ?></small><?php endif; ?></span></label></li>
			<?php endforeach; if ( null !== $previous_group ) { echo '</ul>'; } ?>
		</section>
		<section class="recipe-instructions" aria-labelledby="instructions-heading">
			<h2 id="instructions-heading"><?php esc_html_e( 'Instructions', 'recipe-manager' ); ?></h2>
			<?php $previous_group = null; foreach ( $steps as $index => $step ) :
				if ( $step['group'] !== $previous_group ) {
					if ( null !== $previous_group ) { echo '</ol>'; }
					if ( '' !== $step['group'] ) { echo '<h3 class="group-heading">' . esc_html( $step['group'] ) . '</h3>'; }
					echo '<ol role="list" class="step-list" start="' . esc_attr( $index + 1 ) . '">';
					$previous_group = $step['group'];
				} ?>
				<li class="recipe-step <?php echo $step['image_id'] ? 'has-image' : ''; ?>">
					<span class="step-number" aria-hidden="true"><?php echo esc_html( $index + 1 ); ?></span>
					<?php if ( $step['image_id'] ) : ?><div class="step-photo"><?php echo wp_get_attachment_image( $step['image_id'], 'medium', false, array( 'loading' => 'lazy' ) ); ?></div><?php endif; ?>
					<div class="step-text"><?php echo nl2br( esc_html( $step['text'] ) ); ?></div>
				</li>
			<?php endforeach; if ( null !== $previous_group ) { echo '</ol>'; } ?>
		</section>
	</div>
	<?php if ( '' !== trim( $notes ) ) : ?>
	<section class="recipe-notes" aria-labelledby="notes-heading"><h2 id="notes-heading"><?php esc_html_e( 'Notes & tips', 'recipe-manager' ); ?></h2><div><?php echo nl2br( esc_html( $notes ) ); ?></div></section>
	<?php endif; ?>
	<div class="recipe-tags"><?php rm_theme_terms( $id, 'rm_recipe_tag' ); ?></div>
</article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
