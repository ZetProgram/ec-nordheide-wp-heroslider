<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function hcs_add_meta_boxes() {
	add_meta_box( 'hcs_slide_meta', 'Slide-Einstellungen', 'hcs_slide_meta_cb', 'hcs_slide', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'hcs_add_meta_boxes' );

function hcs_slide_meta_cb( $post ) {
	wp_nonce_field( 'hcs_save_meta', 'hcs_nonce' );

	$fields = array(
		'title'      => get_post_meta( $post->ID, '_hcs_title', true ),
		'subtitle'   => get_post_meta( $post->ID, '_hcs_subtitle', true ),
		'logo_id'    => get_post_meta( $post->ID, '_hcs_logo_id', true ),
		'countdown'  => get_post_meta( $post->ID, '_hcs_countdown', true ),
		'cta_label'  => get_post_meta( $post->ID, '_hcs_cta_label', true ),
		'cta_url'    => get_post_meta( $post->ID, '_hcs_cta_url', true ),
		'cta_nf'     => get_post_meta( $post->ID, '_hcs_cta_nf', true ),
		'expires_at' => get_post_meta( $post->ID, '_hcs_expires_at', true ),
		'is_active'  => get_post_meta( $post->ID, '_hcs_is_active', true ),
		'show_logo'  => get_post_meta( $post->ID, '_hcs_show_logo', true ),
	);

	$logo_url = $fields['logo_id'] ? wp_get_attachment_image_url( (int) $fields['logo_id'], 'thumbnail' ) : '';
	?>
	<style>
		.hcs-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
		.hcs-row{margin-bottom:12px;}
		.hcs-small{font-size:12px;color:#666;}
		.hcs-logo-thumb{display:block;width:96px;height:96px;object-fit:contain;border:1px solid #ddd;border-radius:8px;margin-top:6px;background:#fff;}
	</style>

	<div class="hcs-grid">
		<div class="hcs-row">
			<label for="hcs_title">Titel</label>
			<input type="text" id="hcs_title" name="hcs_title" class="widefat" value="<?php echo esc_attr( $fields['title'] ); ?>">
			<div class="hcs-small">Nur nutzen, wenn kein Logo angezeigt wird.</div>
		</div>

		<div class="hcs-row">
			<label for="hcs_subtitle">Untertitel</label>
			<input type="text" id="hcs_subtitle" name="hcs_subtitle" class="widefat" value="<?php echo esc_attr( $fields['subtitle'] ); ?>">
		</div>

		<div class="hcs-row">
			<label><input type="checkbox" name="hcs_show_logo" value="1" <?php checked( $fields['show_logo'], '1' ); ?>> Logo statt Text verwenden</label>
			<input type="hidden" name="hcs_logo_id" id="hcs_logo_id" value="<?php echo esc_attr( $fields['logo_id'] ); ?>">
			<button type="button" class="button" id="hcs_pick_logo">Logo wählen</button>
			<?php if ( $logo_url ) : ?>
				<img src="<?php echo esc_url( $logo_url ); ?>" class="hcs-logo-thumb" id="hcs_logo_preview">
			<?php else : ?>
				<img src="" class="hcs-logo-thumb" id="hcs_logo_preview" style="display:none">
			<?php endif; ?>
		</div>

		<div class="hcs-row">
			<label for="hcs_countdown">Countdown bis (ISO, z. B. 2030-01-01T00:00:00)</label>
			<input type="text" id="hcs_countdown" name="hcs_countdown" class="widefat" value="<?php echo esc_attr( $fields['countdown'] ); ?>">
		</div>

		<div class="hcs-row">
			<label for="hcs_expires_at">Gültig bis (ISO, z. B. 2030-01-01T12:00:00)</label>
			<input type="text" id="hcs_expires_at" name="hcs_expires_at" class="widefat" value="<?php echo esc_attr( $fields['expires_at'] ); ?>">
			<div class="hcs-small">Nach Ablauf wird die Slide automatisch ausgeblendet.</div>
		</div>

		<div class="hcs-row">
			<label for="hcs_cta_label">CTA Label</label>
			<input type="text" id="hcs_cta_label" name="hcs_cta_label" class="widefat" value="<?php echo esc_attr( $fields['cta_label'] ); ?>">
		</div>

		<div class="hcs-row">
			<label for="hcs_cta_url">CTA URL</label>
			<input type="url" id="hcs_cta_url" name="hcs_cta_url" class="widefat" value="<?php echo esc_attr( $fields['cta_url'] ); ?>">
			<label><input type="checkbox" name="hcs_cta_nf" value="1" <?php checked( $fields['cta_nf'], '1' ); ?>> rel="nofollow"</label>
		</div>

		<div class="hcs-row">
			<label><input type="checkbox" name="hcs_is_active" value="1" <?php checked( $fields['is_active'], '1' ); ?>> Slide aktiv</label>
		</div>
	</div>

	<script>
		(function($){
			let frame;
			$('#hcs_pick_logo').on('click', function(e){
				e.preventDefault();
				if(frame){ frame.open(); return; }
				frame = wp.media({ title:'Logo wählen', button:{ text:'Übernehmen' }, multiple:false });
				frame.on('select', function(){
					const att = frame.state().get('selection').first().toJSON();
					$('#hcs_logo_id').val(att.id);
					$('#hcs_logo_preview').attr('src', (att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url)).show();
				});
				frame.open();
			});
		})(jQuery);
	</script>
	<?php
}

function hcs_save_meta( $post_id ) {
	if ( ! isset( $_POST['hcs_nonce'] ) || ! wp_verify_nonce( $_POST['hcs_nonce'], 'hcs_save_meta' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = array(
		'_hcs_title'      => sanitize_text_field( $_POST['hcs_title'] ?? '' ),
		'_hcs_subtitle'   => sanitize_text_field( $_POST['hcs_subtitle'] ?? '' ),
		'_hcs_logo_id'    => intval( $_POST['hcs_logo_id'] ?? 0 ),
		'_hcs_show_logo'  => isset( $_POST['hcs_show_logo'] ) ? '1' : '',
		'_hcs_countdown'  => sanitize_text_field( $_POST['hcs_countdown'] ?? '' ),
		'_hcs_cta_label'  => sanitize_text_field( $_POST['hcs_cta_label'] ?? '' ),
		'_hcs_cta_url'    => esc_url_raw( $_POST['hcs_cta_url'] ?? '' ),
		'_hcs_cta_nf'     => isset( $_POST['hcs_cta_nf'] ) ? '1' : '',
		'_hcs_expires_at' => sanitize_text_field( $_POST['hcs_expires_at'] ?? '' ),
		'_hcs_is_active'  => isset( $_POST['hcs_is_active'] ) ? '1' : '',
	);
	foreach ( $fields as $k => $v ) {
		update_post_meta( $post_id, $k, $v );
	}
}
add_action( 'save_post_hcs_slide', 'hcs_save_meta' );
