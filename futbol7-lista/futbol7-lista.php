<?php
/**
 * Plugin Name: Futbol 7 - Lista de Jugadores
 * Description: Formulario para que hasta 16 jugadores se apunten a un partido de futbol 7. Cada jugador puede borrar solo su propia inscripcion; los administradores pueden editar la fecha del partido y borrar a cualquiera.
 * Version: 1.0.0
 * Author: biencommunity
 * License: GPL-2.0-or-later
 * Text Domain: futbol7-lista
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FUTBOL7_MAX_PLAYERS', 16 );
define( 'FUTBOL7_VERSION', '1.0.0' );
define( 'FUTBOL7_PLUGIN_FILE', __FILE__ );
define( 'FUTBOL7_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FUTBOL7_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once FUTBOL7_PLUGIN_DIR . 'includes/class-futbol7-db.php';
require_once FUTBOL7_PLUGIN_DIR . 'includes/class-futbol7-rest.php';
require_once FUTBOL7_PLUGIN_DIR . 'includes/class-futbol7-shortcode.php';

register_activation_hook( __FILE__, array( 'Futbol7_DB', 'activate' ) );

add_action( 'plugins_loaded', function () {
	Futbol7_REST::init();
	Futbol7_Shortcode::init();
} );
