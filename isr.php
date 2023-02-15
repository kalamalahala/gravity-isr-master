<?php

/*

Plugin Name: Gravity ISR

Description: Gravity Forms based insurance sales reporting

Version: 1.0.5

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

require_once( plugin_dir_path( __FILE__ ) . 'class-isr.php' );

require_once( plugin_dir_path( __FILE__ ) . 'class-isr-operations.php' );

ISR::get_instance();

register_activation_hook( __FILE__, 'isr_activations' );

function isr_activations(){


}


register_deactivation_hook( __FILE__, 'isr_deactivations' );


function isr_deactivations(){    
    

}


?>