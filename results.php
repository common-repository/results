<?php
/*
Plugin Name: Sport Results
Plugin URI: http://iworks.pl/sport-results/
Description: Sport results, log for sportsmens to keep results, events description, positions, etc, etc
Version: trunk
Author: iWorks
Author URI: http://iworks.pl
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*

Copyright 2012 Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'WPINC' ) ) {
    die;
}

/**
 * i18n
 */
load_plugin_textdomain( 'iworks_results', false, basename( dirname( __FILE__ ) ) . '/languages' );
/**
 * static options
 */
define( 'IWORKS_RESULTS_VERSION', '0' );
define( 'IWORKS_RESULTS_PREFIX',  'iworks_results_' );
/**
 * requires
 */
require_once dirname(__FILE__).'/lib/class-iworks-sport-results.php';
/**
 * run
 */
$iworks_results = new iWorks_Sport_Results();
/**
 * activate && deactivate
 */
register_activation_hook  ( __FILE__, array( $iworks_results, 'activate'   ) );
register_deactivation_hook( __FILE__, array( $iworks_results, 'deactivate' ) );

