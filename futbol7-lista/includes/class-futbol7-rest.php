<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Futbol7_REST {

	const NAMESPACE = 'futbol7/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/state',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_state' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/players',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'add_player' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'name' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/players/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'delete_player' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/settings',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_settings' ),
				'permission_callback' => array( __CLASS__, 'is_admin' ),
			)
		);
	}

	public static function is_admin() {
		return current_user_can( 'manage_options' );
	}

	private static function verify_nonce( $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		return $nonce && wp_verify_nonce( $nonce, 'wp_rest' );
	}

	public static function get_state( $request ) {
		$players = Futbol7_DB::get_players();

		$out_players = array();
		foreach ( $players as $p ) {
			$out_players[] = array(
				'id'   => (int) $p->id,
				'name' => $p->player_name,
			);
		}

		return new WP_REST_Response(
			array(
				'players' => $out_players,
				'max'     => FUTBOL7_MAX_PLAYERS,
				'date'    => get_option( 'futbol7_match_date', '' ),
				'time'    => get_option( 'futbol7_match_time', '' ),
				'notes'   => get_option( 'futbol7_notes', '' ),
				'isAdmin' => self::is_admin(),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			),
			200
		);
	}

	public static function add_player( $request ) {
		if ( ! self::verify_nonce( $request ) ) {
			return new WP_Error( 'futbol7_bad_nonce', 'Peticion no valida.', array( 'status' => 403 ) );
		}

		$name = sanitize_text_field( $request->get_param( 'name' ) );
		$name = trim( $name );

		if ( '' === $name ) {
			return new WP_Error( 'futbol7_empty_name', 'Escribe tu nombre.', array( 'status' => 400 ) );
		}
		if ( mb_strlen( $name ) > 60 ) {
			$name = mb_substr( $name, 0, 60 );
		}

		if ( Futbol7_DB::count_players() >= FUTBOL7_MAX_PLAYERS ) {
			return new WP_Error( 'futbol7_full', 'La lista ya esta completa (16 jugadores).', array( 'status' => 409 ) );
		}

		$owner_token = wp_generate_password( 32, false, false );
		$id          = Futbol7_DB::insert_player( $name, $owner_token );

		return new WP_REST_Response(
			array(
				'id'          => $id,
				'name'        => $name,
				'owner_token' => $owner_token,
			),
			201
		);
	}

	public static function delete_player( $request ) {
		if ( ! self::verify_nonce( $request ) ) {
			return new WP_Error( 'futbol7_bad_nonce', 'Peticion no valida.', array( 'status' => 403 ) );
		}

		$id     = (int) $request->get_param( 'id' );
		$player = Futbol7_DB::get_player( $id );

		if ( ! $player ) {
			return new WP_Error( 'futbol7_not_found', 'Ese jugador ya no esta en la lista.', array( 'status' => 404 ) );
		}

		$is_admin    = self::is_admin();
		$owner_token = sanitize_text_field( (string) $request->get_param( 'owner_token' ) );

		if ( ! $is_admin && ( '' === $owner_token || ! hash_equals( $player->owner_token, $owner_token ) ) ) {
			return new WP_Error( 'futbol7_forbidden', 'Solo puedes borrar tu propia inscripcion.', array( 'status' => 403 ) );
		}

		Futbol7_DB::delete_player( $id );

		return new WP_REST_Response( array( 'deleted' => $id ), 200 );
	}

	public static function update_settings( $request ) {
		if ( ! self::verify_nonce( $request ) ) {
			return new WP_Error( 'futbol7_bad_nonce', 'Peticion no valida.', array( 'status' => 403 ) );
		}

		$date  = sanitize_text_field( (string) $request->get_param( 'date' ) );
		$time  = sanitize_text_field( (string) $request->get_param( 'time' ) );
		$notes = sanitize_textarea_field( (string) $request->get_param( 'notes' ) );

		update_option( 'futbol7_match_date', $date );
		update_option( 'futbol7_match_time', $time );
		update_option( 'futbol7_notes', $notes );

		return new WP_REST_Response(
			array(
				'date'  => $date,
				'time'  => $time,
				'notes' => $notes,
			),
			200
		);
	}
}
