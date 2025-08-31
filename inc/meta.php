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
		'show_logo'  => get_post_meta( $post->ID, '_hcs_show_logo', true ) === '1',
		'countdown'  => get_post_meta( $post->ID, '_hcs_countdown', true ),
		'cta_label'  => get_post_meta( $post->ID, '_hcs_cta_label', true ),
		'cta_url'    => get_post_meta( $post->ID, '_hcs_cta_url', true ),
		'cta_nf'     => get_post_meta( $post->ID, '_hcs_cta_nf', true ) === '1',
		'expires_at' => get_post_meta( $post->ID, '_hcs_expires_at', true ),
		'is_active'  => get_post_meta( $post->ID, '_hcs_is_active', true ) === '1',
	);
	// Helper to convert stored date to datetime-local value
	$to_local_value = function( $str ){
		if ( empty( $str ) ) return '';
		$ts = strtotime( $str );
		if ( ! $ts ) return '';
		return gmdate( 'Y-m-d\\TH:i', $ts ); // use UTC; browsers render local offset
	};
	?>
	<style>
		.hcs-meta table{ width:100%; max-width:860px; }
		.hcs-meta th{ text-align:left; width:180px; vertical-align:top; padding:8px 8px 8px 0; }
		.hcs-meta td{ padding:8px 0; }
		.hcs-meta .desc{ color:#666; font-size:12px; }
		.hcs-media{ display:flex; gap:12px; align-items:center; }
		.hcs-media img{ max-height:48px; border-radius:4px; }
	</style>
	<div class="hcs-meta">
		<table class="form-table">
			<tr>
				<th><label for="hcs_title">Titel</label></th>
				<td><input type="text" id="hcs_title" name="hcs_title" class="regular-text" value="<?php echo esc_attr( $fields['title'] ); ?>"></td>
			</tr>
			<tr>
				<th><label for="hcs_subtitle">Untertitel</label></th>
				<td><input type="text" id="hcs_subtitle" name="hcs_subtitle" class="regular-text" value="<?php echo esc_attr( $fields['subtitle'] ); ?>"></td>
			</tr>
			<tr>
				<th>Logo</th>
				<td>
					<div class="hcs-media">
						<input type="hidden" id="hcs_logo_id" name="hcs_logo_id" value="<?php echo esc_attr( $fields['logo_id'] ); ?>">
						<img id="hcs_logo_preview" src="<?php echo $fields['logo_id'] ? esc_url( wp_get_attachment_image_url( (int) $fields['logo_id'], 'thumbnail' ) ) : ''; ?>" alt="" />
						<button type="button" class="button" id="hcs_logo_btn">Bild wählen</button>
						<label style="margin-left:8px;">
							<input type="checkbox" name="hcs_show_logo" <?php checked( $fields['show_logo'] ); ?>>
							Logo anzeigen
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<th><label for="hcs_countdown">Countdown bis</label></th>
				<td>
					<input type="datetime-local" id="hcs_countdown" name="hcs_countdown" value="<?php echo esc_attr( $to_local_value( $fields['countdown'] ) ); ?>">
					<p class="desc">Angenehme Eingabe: Datum & Uhrzeit wählen. Gespeichert wird als ISO-String.</p>
				</td>
			</tr>
			<tr>
				<th><label for="hcs_cta_label">CTA-Label</label></th>
				<td><input type="text" id="hcs_cta_label" name="hcs_cta_label" class="regular-text" value="<?php echo esc_attr( $fields['cta_label'] ); ?>"></td>
			</tr>
			<tr>
				<th><label for="hcs_cta_url">CTA-URL</label></th>
				<td>
					<input type="url" id="hcs_cta_url" name="hcs_cta_url" class="regular-text" value="<?php echo esc_url( $fields['cta_url'] ); ?>">
					<label style="margin-left:8px;">
						<input type="checkbox" name="hcs_cta_nf" <?php checked( $fields['cta_nf'] ); ?>>
						nofollow
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="hcs_expires_at">Deaktivieren ab</label></th>
				<td>
					<input type="datetime-local" id="hcs_expires_at" name="hcs_expires_at" value="<?php echo esc_attr( $to_local_value( $fields['expires_at'] ) ); ?>">
					<p class="desc">Optional. Ab diesem Zeitpunkt wird die Slide nicht mehr angezeigt.</p>
				</td>
			</tr>
			<tr>
				<th>Status</th>
				<td><label><input type="checkbox" name="hcs_is_active" <?php checked( $fields['is_active'] ); ?>> Aktiv</label></td>
			</tr>
		</table>
	</div>
	<script>
	(function($){
		// Media chooser for logo
		$('#hcs_logo_btn').on('click', function(e){
			e.preventDefault();
			const frame = wp.media({ title: 'Logo wählen', multiple: false });
			frame.on('select', function(){
				const att = frame.state().get('selection').first().toJSON();
				$('#hcs_logo_id').val(att.id);
				$('#hcs_logo_preview').attr('src', att.sizes && (att.sizes.thumbnail||att.sizes.medium) ? (att.sizes.thumbnail || att.sizes.medium).url : att.url );
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
