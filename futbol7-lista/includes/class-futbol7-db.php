<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Futbol7_DB {

	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'futbol7_players';
	}

	public static function activate() {
		global $wpdb;
		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			player_name VARCHAR(100) NOT NULL,
			owner_token VARCHAR(64) NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY owner_token (owner_token)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		if ( false === get_option( 'futbol7_match_date' ) ) {
			add_option( 'futbol7_match_date', '' );
		}
		if ( false === get_option( 'futbol7_match_time' ) ) {
			add_option( 'futbol7_match_time', '' );
		}
		if ( false === get_option( 'futbol7_notes' ) ) {
			add_option( 'futbol7_notes', '' );
		}
	}

	public static function get_players() {
		global $wpdb;
		$table_name = self::table_name();
		return $wpdb->get_results(
			"SELECT id, player_name, created_at FROM {$table_name} ORDER BY created_at ASC"
		);
	}

	public static function count_players() {
		global $wpdb;
		$table_name = self::table_name();
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
	}

	public static function get_player( $id ) {
		global $wpdb;
		$table_name = self::table_name();
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id )
		);
	}

	public static function insert_player( $name, $owner_token ) {
		global $wpdb;
		$table_name = self::table_name();
		$wpdb->insert(
			$table_name,
			array(
				'player_name' => $name,
				'owner_token' => $owner_token,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s' )
		);
		return (int) $wpdb->insert_id;
	}

	public static function delete_player( $id ) {
		global $wpdb;
		$table_name = self::table_name();
		return $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
	}
}
