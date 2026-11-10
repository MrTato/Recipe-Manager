<?php
/** CRUD for Default unit suggestions; recipe measurements retain their text. */
namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=rm_recipe', __( 'Default Units', 'recipe-manager-core' ), __( 'Default Units', 'recipe-manager-core' ), 'manage_categories', 'rm-default-units', __NAMESPACE__ . '\\render_unit_manager' );
} );

/** Validate a catalog change before persisting it. */
function change_default_unit( $action, $original, $name ) {
	get_default_unit_suggestions();
	$names = get_option( 'rm_default_unit_names', array() );
	$name = sanitize_text_field( $name );
	if ( in_array( $original, get_protected_default_units(), true ) || in_array( $name, get_protected_default_units(), true ) ) {
		return new \WP_Error( 'protected', __( 'This built-in unit cannot be managed or deleted.', 'recipe-manager-core' ) );
	}
	if ( ! in_array( $action, array( 'create', 'update', 'delete' ), true ) ) {
		return new \WP_Error( 'invalid', __( 'Invalid unit action.', 'recipe-manager-core' ) );
	}
	$index = array_search( $original, $names, true );
	if ( 'create' !== $action && false === $index ) {
		return new \WP_Error( 'missing', __( 'This unit no longer exists. Refresh the page and try again.', 'recipe-manager-core' ) );
	}
	if ( 'delete' !== $action ) {
		if ( '' === $name ) {
			return new \WP_Error( 'empty', __( 'Enter a unit name.', 'recipe-manager-core' ) );
		}
		if ( in_array( $name, $names, true ) && ( 'create' === $action || $name !== $original ) ) {
			return new \WP_Error( 'duplicate', __( 'A unit with this name already exists.', 'recipe-manager-core' ) );
		}
	}
	if ( 'create' === $action ) {
		$names[] = $name;
	} elseif ( 'update' === $action ) {
		$names[ $index ] = $name;
	} else {
		unset( $names[ $index ] );
	}
	$names = array_values( $names );
	if ( $names !== get_option( 'rm_default_unit_names', array() ) && ! update_option( 'rm_default_unit_names', $names, false ) ) {
		return new \WP_Error( 'save', __( 'The unit could not be saved. Try again.', 'recipe-manager-core' ) );
	}
	return true;
}

add_action( 'admin_post_rm_default_unit', function () {
	if ( ! current_user_can( 'manage_categories' ) ) {
		wp_die( esc_html__( 'You cannot manage units.', 'recipe-manager-core' ), 403 );
	}
	check_admin_referer( 'rm_default_unit' );
	$values = array();
	foreach ( array( 'unit_action', 'original', 'unit_name' ) as $key ) {
		$values[ $key ] = isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
	}
	$result = change_default_unit( $values['unit_action'], $values['original'], $values['unit_name'] );
	set_transient( 'rm_unit_notice_' . get_current_user_id(), array(
		'error' => is_wp_error( $result ),
		'message' => is_wp_error( $result ) ? $result->get_error_message() : __( 'Unit changes saved.', 'recipe-manager-core' ),
	), 60 );
	wp_safe_redirect( admin_url( 'edit.php?post_type=rm_recipe&page=rm-default-units' ) );
	exit;
} );

function unit_manager_form_fields( $action, $original = '' ) {
	wp_nonce_field( 'rm_default_unit' );
	?>
	<input type="hidden" name="action" value="rm_default_unit">
	<input type="hidden" name="unit_action" value="<?php echo esc_attr( $action ); ?>">
	<input type="hidden" name="original" value="<?php echo esc_attr( $original ); ?>">
	<?php
}

function render_unit_manager() {
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$suggestions = get_default_unit_suggestions();
	$suggestions = array_diff_key( $suggestions, array_flip( get_protected_default_units() ) );
	uksort( $suggestions, 'strnatcasecmp' );
	$notice_key = 'rm_unit_notice_' . get_current_user_id();
	$notice = get_transient( $notice_key );
	delete_transient( $notice_key );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Default Units', 'recipe-manager-core' ); ?></h1>
		<?php if ( $notice ) : ?>
			<div class="notice <?php echo $notice['error'] ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $notice['message'] ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Manage the suggestions in the Default unit dropdown. New names entered when saving recipes are added here automatically. Renaming or deleting a suggestion leaves existing recipe measurements unchanged. You can still type any unit in a recipe.', 'recipe-manager-core' ); ?></p>
		<h2><?php esc_html_e( 'Add unit', 'recipe-manager-core' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php unit_manager_form_fields( 'create' ); ?>
			<label for="rm-new-unit"><?php esc_html_e( 'Unit name', 'recipe-manager-core' ); ?></label>
			<input id="rm-new-unit" name="unit_name" type="text" class="regular-text" required>
			<?php submit_button( __( 'Add unit', 'recipe-manager-core' ), 'primary', 'submit', false ); ?>
		</form>
		<h2><?php echo esc_html( sprintf( __( 'Saved units (%d)', 'recipe-manager-core' ), count( $suggestions ) ) ); ?></h2>
		<table class="widefat striped">
			<thead><tr><th scope="col"><?php esc_html_e( 'Unit', 'recipe-manager-core' ); ?></th><th scope="col"><?php esc_html_e( 'Edit name', 'recipe-manager-core' ); ?></th><th scope="col"><?php esc_html_e( 'Delete', 'recipe-manager-core' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $suggestions as $name => $label ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( $name ); ?><?php if ( $label !== (string) $name ) : ?><br><small><?php echo esc_html( $label ); ?></small><?php endif; ?></th>
					<td><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php unit_manager_form_fields( 'update', $name ); ?>
						<input type="text" name="unit_name" value="<?php echo esc_attr( $name ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'New name for %s', 'recipe-manager-core' ), $name ) ); ?>" required>
						<?php submit_button( __( 'Save', 'recipe-manager-core' ), 'secondary', 'submit', false ); ?>
					</form></td>
					<td><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php unit_manager_form_fields( 'delete', $name ); ?>
						<button class="button" type="submit" aria-label="<?php echo esc_attr( sprintf( __( 'Delete unit %s', 'recipe-manager-core' ), $name ) ); ?>"><?php esc_html_e( 'Delete', 'recipe-manager-core' ); ?></button>
					</form></td>
				</tr>
			<?php endforeach; ?>
			<?php if ( ! $suggestions ) : ?><tr><td colspan="3"><?php esc_html_e( 'No saved units. Add a unit above or enter one in a recipe.', 'recipe-manager-core' ); ?></td></tr><?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
