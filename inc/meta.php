<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Featured Image ganz oben im Hauptbereich anzeigen (statt in der Sidebar).
 * So steht es VOR unserer Metabox.
 */
add_action('do_meta_boxes', function(){
    // Entferne die Standard-Box rechts
    remove_meta_box('postimagediv', 'hcs_slide', 'side');
    // Füge sie im Content-Bereich wieder ein – mit hoher Priorität (vor unserer Box)
    add_meta_box('postimagediv', __('Featured image'), 'post_thumbnail_meta_box', 'hcs_slide', 'normal', 'high');
}, 20);

/**
 * Metabox registrieren
 */
function hcs_add_meta_boxes() {
	add_meta_box(
        'hcs_slide_meta',
        __('Slide-Einstellungen', 'hcs'),
        'hcs_slide_meta_cb',
        'hcs_slide',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'hcs_add_meta_boxes' );

/**
 * Metabox-UI (modernes Card/Grid Layout)
 */
function hcs_slide_meta_cb( $post ) {
	wp_nonce_field( 'hcs_save_meta', 'hcs_nonce' );
    // Media-Picker sicher laden
    wp_enqueue_media();

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

	$to_local_value = function( $str ){
		if ( empty( $str ) ) return '';
		$ts = strtotime( $str );
		if ( ! $ts ) return '';
		return gmdate( 'Y-m-d\\TH:i', $ts ); // UTC -> Browser zeigt lokal
	};

	$logo_url = $fields['logo_id'] ? wp_get_attachment_image_url( (int) $fields['logo_id'], 'thumbnail' ) : '';
	?>

	<style>
		/* ---------- Card + Grid Look ---------- */
		.hcs-wrap { max-width: 1100px; }
		.hcs-card {
			background: #fff;
			border: 1px solid #e5e7eb;
			border-radius: 12px;
			box-shadow: 0 1px 2px rgba(16,24,40,.06);
			padding: 20px;
			margin: 0 0 16px 0;
		}
		.hcs-card h3 {
			margin: 0 0 14px 0;
			font-size: 15px;
			font-weight: 600;
		}
		.hcs-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 16px;
		}
		@media (max-width: 900px) {
			.hcs-grid { grid-template-columns: 1fr; }
		}
		.hcs-field > label { display:block; font-weight:600; margin:0 0 6px; }
		.hcs-help { color:#667085; font-size:12px; margin-top:6px; }

		/* Inputs */
		.hcs-input, .hcs-input-url, .hcs-input-dt {
			width: 100%;
			border: 1px solid #e5e7eb;
			border-radius: 10px;
			padding: 10px 12px;
			font-size: 14px;
			background: #fff;
			box-shadow: 0 1px 0 rgba(16,24,40,.02) inset;
		}
		.hcs-input:focus, .hcs-input-url:focus, .hcs-input-dt:focus {
			outline: none;
			border-color: var(--wp-admin-theme-color, #3858e9);
			box-shadow: 0 0 0 3px rgba(56,88,233,.15);
		}

		/* Buttons (modern) */
		.hcs-btn {
			display:inline-flex; align-items:center; gap:8px;
			border: 1px solid #d1d5db;
			border-radius: 10px;
			padding: 8px 12px;
			font-weight: 600;
			text-decoration: none;
			cursor: pointer;
			background: linear-gradient(180deg,#fff, #f8fafc);
		}
		.hcs-btn:hover { border-color:#cbd5e1; background:#fff; }
		.hcs-btn--primary {
			border-color: transparent;
			color:#fff;
			background: linear-gradient(180deg,var(--wp-admin-theme-color,#3858e9), #263dbf);
			box-shadow: 0 1px 1px rgba(0,0,0,.05);
		}
		.hcs-btn--primary:hover { filter: brightness(1.05); }
		.hcs-btn--ghost {
			background: transparent; border-color: #e5e7eb; color:#334155;
		}

		/* Media picker */
		.hcs-media { display:flex; align-items:center; gap:12px; }
		.hcs-media .hcs-logo {
			width:56px; height:56px; border-radius:10px; background:#f1f5f9;
			display:flex; align-items:center; justify-content:center; overflow:hidden;
			border:1px solid #e2e8f0;
		}
		.hcs-media .hcs-logo img{ max-width:100%; max-height:100%; display:block; }

		/* Toggle Switch */
		.hcs-switch { display:flex; align-items:center; gap:10px; }
		.hcs-toggle { position: relative; width: 42px; height: 24px; background:#e5e7eb; border-radius: 999px; transition: .2s; }
		.hcs-toggle::after {
			content:''; position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%;
			background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.15); transition:.2s;
		}
		.hcs-toggle-input { display:none; }
		.hcs-toggle-input:checked + .hcs-toggle {
			background: var(--wp-admin-theme-color,#3858e9);
		}
		.hcs-toggle-input:checked + .hcs-toggle::after { transform: translateX(18px); }
		.hcs-switch-label { font-weight:500; }

		/* Inline helpers */
		.hcs-inline { display:flex; align-items:center; gap:12px; }
	</style>

	<div class="hcs-wrap">
		<div class="hcs-card">
			<h3><?php _e('Inhalte', 'hcs'); ?></h3>
			<div class="hcs-grid">
				<div class="hcs-field">
					<label for="hcs_title"><?php _e('Titel','hcs'); ?></label>
					<input type="text" id="hcs_title" name="hcs_title" class="hcs-input" value="<?php echo esc_attr( $fields['title'] ); ?>">
				</div>

				<div class="hcs-field">
					<label for="hcs_subtitle"><?php _e('Untertitel','hcs'); ?></label>
					<input type="text" id="hcs_subtitle" name="hcs_subtitle" class="hcs-input" value="<?php echo esc_attr( $fields['subtitle'] ); ?>">
				</div>

				<div class="hcs-field">
					<label><?php _e('Logo','hcs'); ?></label>
					<div class="hcs-media">
						<div class="hcs-logo" id="hcs_logo_preview_wrap">
							<?php if ( $logo_url ) : ?>
								<img id="hcs_logo_preview" src="<?php echo esc_url( $logo_url ); ?>" alt="">
							<?php else: ?>
								<img id="hcs_logo_preview" src="" alt="" style="display:none;">
								<span class="dashicons dashicons-format-image" style="opacity:.45;"></span>
							<?php endif; ?>
						</div>

						<input type="hidden" id="hcs_logo_id" name="hcs_logo_id" value="<?php echo esc_attr( $fields['logo_id'] ); ?>">
						<button type="button" class="hcs-btn hcs-btn--primary" id="hcs_logo_btn">
							<span class="dashicons dashicons-upload"></span><?php _e('Bild wählen','hcs'); ?>
						</button>
						<button type="button" class="hcs-btn hcs-btn--ghost" id="hcs_logo_clear">
							<span class="dashicons dashicons-no"></span><?php _e('Entfernen','hcs'); ?>
						</button>
					</div>

					<div class="hcs-switch" style="margin-top:10px;">
						<input type="checkbox" id="hcs_show_logo" name="hcs_show_logo" class="hcs-toggle-input" <?php checked( $fields['show_logo'] ); ?>>
						<span class="hcs-toggle" aria-hidden="true"></span>
						<label for="hcs_show_logo" class="hcs-switch-label"><?php _e('Logo anzeigen','hcs'); ?></label>
					</div>
				</div>

				<div class="hcs-field">
					<label for="hcs_countdown"><?php _e('Countdown bis','hcs'); ?></label>
					<input type="datetime-local" id="hcs_countdown" name="hcs_countdown" class="hcs-input-dt" value="<?php echo esc_attr( $to_local_value( $fields['countdown'] ) ); ?>">
					<div class="hcs-help"><?php _e('Datum & Uhrzeit wählen – gespeichert als ISO-String.','hcs'); ?></div>
				</div>

				<div class="hcs-field">
					<label for="hcs_cta_label"><?php _e('CTA-Label','hcs'); ?></label>
					<input type="text" id="hcs_cta_label" name="hcs_cta_label" class="hcs-input" value="<?php echo esc_attr( $fields['cta_label'] ); ?>">
				</div>

				<div class="hcs-field">
					<label for="hcs_cta_url"><?php _e('CTA-URL','hcs'); ?></label>
					<div class="hcs-inline">
						<input type="url" id="hcs_cta_url" name="hcs_cta_url" class="hcs-input-url" placeholder="https://…" value="<?php echo esc_url( $fields['cta_url'] ); ?>">
						<div class="hcs-switch">
							<input type="checkbox" id="hcs_cta_nf" name="hcs_cta_nf" class="hcs-toggle-input" <?php checked( $fields['cta_nf'] ); ?>>
							<span class="hcs-toggle" aria-hidden="true"></span>
							<label for="hcs_cta_nf" class="hcs-switch-label">nofollow</label>
						</div>
					</div>
				</div>

				<div class="hcs-field">
					<label for="hcs_expires_at"><?php _e('Deaktivieren ab','hcs'); ?></label>
					<input type="datetime-local" id="hcs_expires_at" name="hcs_expires_at" class="hcs-input-dt" value="<?php echo esc_attr( $to_local_value( $fields['expires_at'] ) ); ?>">
					<div class="hcs-help"><?php _e('Optional. Ab diesem Zeitpunkt wird die Slide nicht mehr angezeigt.','hcs'); ?></div>
				</div>

				<div class="hcs-field">
					<label><?php _e('Status','hcs'); ?></label>
					<div class="hcs-switch">
						<input type="checkbox" id="hcs_is_active" name="hcs_is_active" class="hcs-toggle-input" <?php checked( $fields['is_active'] ); ?>>
						<span class="hcs-toggle" aria-hidden="true"></span>
						<label for="hcs_is_active" class="hcs-switch-label"><?php _e('Aktiv','hcs'); ?></label>
					</div>
				</div>
			</div>
		</div><!--/.hcs-card-->
	</div><!--/.hcs-wrap-->

	<script>
	(function($){
		// Media chooser
		$('#hcs_logo_btn').on('click', function(e){
			e.preventDefault();
			const frame = wp.media({ title: '<?php echo esc_js(__('Logo wählen','hcs')); ?>', multiple: false, library: { type: 'image' } });
			frame.on('select', function(){
				const att = frame.state().get('selection').first().toJSON();
				$('#hcs_logo_id').val(att.id);
				const url = (att.sizes && (att.sizes.thumbnail || att.sizes.medium)) ? (att.sizes.thumbnail || att.sizes.medium).url : att.url;
				$('#hcs_logo_preview').attr('src', url).show();
				$('#hcs_logo_preview_wrap .dashicons').hide();
			});
			frame.open();
		});

		// Clear logo
		$('#hcs_logo_clear').on('click', function(e){
			e.preventDefault();
			$('#hcs_logo_id').val('');
			$('#hcs_logo_preview').attr('src','').hide();
			$('#hcs_logo_preview_wrap .dashicons').show();
		});
	})(jQuery);
	</script>

	<?php
}

/**
 * Speichern
 */
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
