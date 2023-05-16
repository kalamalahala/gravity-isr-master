<?php

/*
Plugin Name: Gravity ISR - WAR Report
Description: Gravity Forms based reporting for The Johnson Group
Version: 1.1.0
Author: Derek Olalehe & Tyler Karle
Author URI: http://www.derekolalehe.com
License: GPL2
*/

/* Copyright 2021 The Johnson Group
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include main classes
require_once( plugin_dir_path( __FILE__ ) . 'class-isr.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-isr-operations.php' );


// Register actions for activation and deactivation
register_activation_hook( __FILE__, 'isr_activations' );
register_deactivation_hook( __FILE__, 'isr_deactivations' );

function isr_activations() {
	// Do nothing
}

function isr_deactivations() {
	// Do nothing
}

// Create singleton instance of ISR class
ISR::get_instance();