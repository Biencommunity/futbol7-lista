<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Futbol7_Shortcode {

	public static function init() {
		add_shortcode( 'futbol7_lista', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ) );
	}

	public static function maybe_enqueue() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'futbol7_lista' ) ) {
			return;
		}

		wp_enqueue_style(
			'futbol7-lista',
			FUTBOL7_PLUGIN_URL . 'assets/futbol7.css',
			array(),
			FUTBOL7_VERSION
		);

		wp_enqueue_script(
			'futbol7-lista',
			FUTBOL7_PLUGIN_URL . 'assets/futbol7.js',
			array(),
			FUTBOL7_VERSION,
			true
		);

		wp_localize_script(
			'futbol7-lista',
			'futbol7Config',
			array(
				'root'  => esc_url_raw( rest_url( 'futbol7/v1' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public static function render( $atts ) {
		self::maybe_force_enqueue();
		return '<div id="futbol7-app" class="futbol7-app" data-max="' . esc_attr( FUTBOL7_MAX_PLAYERS ) . '"><p class="futbol7-loading">Cargando lista...</p></div>';
	}

	private static function maybe_force_enqueue() {
		if ( ! wp_script_is( 'futbol7-lista', 'enqueued' ) ) {
			wp_enqueue_style(
				'futbol7-lista',
				FUTBOL7_PLUGIN_URL . 'assets/futbol7.css',
				array(),
				FUTBOL7_VERSION
			);
			wp_enqueue_script(
				'futbol7-lista',
				FUTBOL7_PLUGIN_URL . 'assets/futbol7.js',
				array(),
				FUTBOL7_VERSION,
				true
			);
			wp_localize_script(
				'futbol7-lista',
				'futbol7Config',
				array(
					'root'  => esc_url_raw( rest_url( 'futbol7/v1' ) ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
				)
			);
		}
	}
}
