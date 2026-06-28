<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	$idsString = is_array( $products_ids ) ? implode( ',',$products_ids ) : '';

	// In AJAX (e.g. after removing an item) get_the_permalink() resolves to admin-ajax.php,
	// so fall back to the page that triggered the request.
	$share_base = wp_doing_ajax() ? wp_get_referer() : get_the_permalink();
	if ( ! $share_base ) {
		$share_base = home_url( '/' );
	}
	$share_base = remove_query_arg( 'wishsuitepids', $share_base );
	$share_link = add_query_arg( 'wishsuitepids', $idsString, $share_base );
	$share_title = get_the_title();

	$thumb_id = get_post_thumbnail_id();
	$thumb_url = wp_get_attachment_image_src( $thumb_id, 'thumbnail-size', true );

	$encoded_share_link = rawurlencode( $share_link );

	$social_button_list = [
		'facebook' => [
			'title' => esc_html__( 'Facebook', 'wishsuite' ),
			'url' 	=> 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_share_link,
		],
		'twitter' => [
			'title' => esc_html__( 'Twitter', 'wishsuite' ),
			'url' 	=> 'https://twitter.com/share?url=' . $encoded_share_link . '&amp;text=' . rawurlencode( $share_title ),
		],
		'pinterest' => [
			'title' => esc_html__( 'Pinterest', 'wishsuite' ),
			'url' 	=> 'https://pinterest.com/pin/create/button/?url=' . $encoded_share_link . '&media=' . rawurlencode( $thumb_url[0] ),
		],
		'linkedin' => [
			'title' => esc_html__( 'Linkedin', 'wishsuite' ),
			'url' 	=> 'https://www.linkedin.com/shareArticle?mini=true&url=' . $encoded_share_link . '&amp;title=' . rawurlencode( $share_title ),
		],
		'email' => [
			'title' => esc_html__( 'Email', 'wishsuite' ),
			'url' 	=> 'mailto:?subject=' . rawurlencode( esc_html__( 'Wishlist', 'wishsuite' ) ) . '&amp;body=' . $encoded_share_link,
		],

		'reddit' => [
			'title' => esc_html__( 'Reddit', 'wishsuite' ),
			'url' 	=> 'http://reddit.com/submit?url=' . $encoded_share_link . '&amp;title=' . rawurlencode( $share_title ),
		],
		'telegram' => [
			'title' => esc_html__( 'Telegram', 'wishsuite' ),
			'url' 	=> 'https://telegram.me/share/url?url=' . $encoded_share_link,
		],
		'odnoklassniki' => [
			'title' => esc_html__( 'Odnoklassniki', 'wishsuite' ),
			'url' 	=> 'https://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl=' . $encoded_share_link,
		],
		'whatsapp' => [
			'title' => esc_html__( 'WhatsApp', 'wishsuite' ),
			'url' 	=> 'https://wa.me/?text=' . $encoded_share_link,
		],
		'vk' => [
			'title' => esc_html__( 'VK', 'wishsuite' ),
			'url' 	=> 'https://vk.com/share.php?url=' . $encoded_share_link,
		],
	];


	$default_buttons = [
        'facebook'   => esc_html__( 'Facebook', 'wishsuite' ),
        'twitter'    => esc_html__( 'Twitter', 'wishsuite' ),
        'pinterest'  => esc_html__( 'Pinterest', 'wishsuite' ),
        'linkedin'   => esc_html__( 'Linkedin', 'wishsuite' ),
        'telegram'   => esc_html__( 'Telegram', 'wishsuite' ),
    ];
	$button_list = wishsuite_get_option( 'social_share_buttons','wishsuite_table_settings_tabs', $default_buttons );
	$button_text = wishsuite_get_option( 'social_share_button_title','wishsuite_table_settings_tabs', 'Share:' );

	$enable_copy_link = wishsuite_get_option( 'enable_copy_link','wishsuite_table_settings_tabs', 'off' );
	$copy_link_title  = esc_html__( 'Click to copy', 'wishsuite' );

?>

<div class="wishsuite-social-share">
	<span class="wishsuite-social-title"><?php esc_html_e( $button_text, 'wishsuite' ); ?></span>
	<ul>
		<?php
			foreach ( $button_list as $buttonkey => $button ) {
				?>
				<li>
					<a rel="nofollow" href="<?php echo esc_url( $social_button_list[$buttonkey]['url'] ); ?>" <?php echo ( $buttonkey === 'email' ? '' : 'target="_blank"' ) ?>>
						<span class="wishsuite-social-icon">
							<?php echo wishsuite_icon_list( $buttonkey ); ?>
						</span>
					</a>
				</li>
				<?php
			}
		?>
		<?php if ( $enable_copy_link === 'on' && ! empty( $idsString ) ) { ?>
			<li class="wishsuite-copy-link-item">
				<button type="button" class="wishsuite-copy-link" data-clipboard="<?php echo esc_url( $share_link ); ?>" data-tooltip="<?php echo esc_attr( $copy_link_title ); ?>" data-copied="<?php esc_attr_e( 'Copied', 'wishsuite' ); ?>" aria-label="<?php echo esc_attr( $copy_link_title ); ?>">
					<span class="wishsuite-social-icon">
						<?php echo wishsuite_icon_list( 'copy_link' ); ?>
					</span>
				</button>
			</li>
		<?php } ?>
	</ul>
</div>