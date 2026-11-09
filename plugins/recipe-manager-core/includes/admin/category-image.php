<?php
/** Recipe category images, stored as attachment IDs in term metadata. */
namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function category_image_error() {
	return __( 'Choose an image no larger than 512 × 512 pixels. Resize larger images before uploading.', 'recipe-manager-core' );
}

function valid_category_image( $id ) {
	if ( ! wp_attachment_is_image( $id ) ) { return false; }
	$path = wp_get_original_image_path( $id );
	$size = $path ? wp_getimagesize( $path ) : false;
	return $size && $size[0] > 0 && $size[1] > 0 && $size[0] <= 512 && $size[1] <= 512;
}

function category_image_fields( $term_id = 0 ) {
	$id = (int) get_term_meta( $term_id, 'rm_category_image_id', true );
	wp_nonce_field( 'rm_category_image', 'rm_category_image_nonce' );
	?>
	<div class="rm-category-image-picker">
		<input type="hidden" name="rm_category_image_id" value="<?php echo esc_attr( $id ); ?>">
		<div class="rm-category-image-preview" style="margin:10px 0;max-width:128px">
			<?php if ( $id ) { echo wp_get_attachment_image( $id, 'thumbnail', false, array( 'style' => 'max-width:128px;height:auto' ) ); } ?>
		</div>
		<button type="button" class="button rm-category-image-select"><?php esc_html_e( 'Choose image', 'recipe-manager-core' ); ?></button>
		<button type="button" class="button rm-category-image-remove" <?php echo $id ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove image', 'recipe-manager-core' ); ?></button>
		<p class="description"><?php esc_html_e( 'Maximum 512 × 512 pixels. A square image with a transparent background works best.', 'recipe-manager-core' ); ?></p>
		<p class="rm-category-image-error" role="alert" style="color:#b32d2e"></p>
	</div>
	<?php
}

add_action( 'rm_recipe_category_add_form_fields', function () {
	echo '<div class="form-field"><label>' . esc_html__( 'Category image', 'recipe-manager-core' ) . '</label>';
	category_image_fields();
	echo '</div>';
} );
add_action( 'rm_recipe_category_edit_form_fields', function ( $term ) {
	echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Category image', 'recipe-manager-core' ) . '</th><td>';
	category_image_fields( $term->term_id );
	echo '</td></tr>';
} );

function save_category_image( $term_id ) {
	$taxonomy = get_taxonomy( 'rm_recipe_category' );
	$nonce = $_POST['rm_category_image_nonce'] ?? '';
	if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->edit_terms ) || ! is_string( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'rm_category_image' ) || ! isset( $_POST['rm_category_image_id'] ) ) { return; }
	$value = $_POST['rm_category_image_id'];
	if ( ! is_string( $value ) || ! preg_match( '/\A[0-9]*\z/', $value ) ) { return; }
	$id = (int) $value;
	if ( ! $id ) {
		delete_term_meta( $term_id, 'rm_category_image_id' );
	} elseif ( valid_category_image( $id ) ) {
		update_term_meta( $term_id, 'rm_category_image_id', $id );
	} else {
		set_transient( 'rm_category_image_error_' . get_current_user_id(), 1, 60 );
	}
}
add_action( 'created_rm_recipe_category', __NAMESPACE__ . '\\save_category_image' );
add_action( 'edited_rm_recipe_category', __NAMESPACE__ . '\\save_category_image' );

// Reject invalid selections before category creation, including AJAX requests.
add_filter( 'pre_insert_term', function ( $term, $taxonomy ) {
	if ( 'rm_recipe_category' !== $taxonomy || empty( $_POST['rm_category_image_id'] ) ) { return $term; }
	$id = $_POST['rm_category_image_id'];
	if ( ! is_string( $id ) || ! ctype_digit( $id ) || ! valid_category_image( (int) $id ) ) {
		return new \WP_Error( 'rm_category_image_size', category_image_error() );
	}
	return $term;
}, 10, 2 );

add_action( 'admin_notices', function () {
	$key = 'rm_category_image_error_' . get_current_user_id();
	if ( ! get_transient( $key ) ) { return; }
	delete_transient( $key );
	echo '<div class="notice notice-error"><p>' . esc_html__( 'Category image was not changed.', 'recipe-manager-core' ) . ' ' . esc_html( category_image_error() ) . '</p></div>';
} );

add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
	if ( empty( $_POST['rm_category_image_upload'] ) ) { return $file; }
	$size = wp_getimagesize( $file['tmp_name'] );
	if ( ! $size || $size[0] > 512 || $size[1] > 512 ) {
		$file['error'] = category_image_error();
	}
	return $file;
} );

add_action( 'admin_enqueue_scripts', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'rm_recipe_category' !== $screen->taxonomy || ! in_array( $screen->base, array( 'edit-tags', 'term' ), true ) ) { return; }
	wp_enqueue_media();
	$root = dirname( __DIR__, 2 );
	wp_enqueue_script( 'rm-category-image', plugins_url( 'assets/category-image.js', $root . '/recipe-manager-core.php' ), array( 'media-views', 'jquery' ), filemtime( $root . '/assets/category-image.js' ), true );
	wp_localize_script( 'rm-category-image', 'rmCategoryImage', array(
		'title' => __( 'Choose category image', 'recipe-manager-core' ),
		'button' => __( 'Use image', 'recipe-manager-core' ),
		'error' => category_image_error(),
	) );
} );
