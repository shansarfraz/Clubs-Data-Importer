<?php
/*
Plugin Name: Afga Import
Plugin URI: 
Description: This Plugins allows you to import memebers from Afga Website
Version: 0.1
Author: Shaun Sylver
Author URI: 
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('admin_menu', 'afga_import_settings');

function afga_import_settings(){

 add_submenu_page('options-general.php', 'Afga Members Import', 'Afga Import', 'manage_options', 'afga-import', 'afga_import_callback');

}

function afga_import_callback(){
 include('afga-uploadbox.php');
}

add_filter( 'bp_get_send_message_button', function( $array ) {
    if ( friends_check_friendship( bp_loggedin_user_id(), bp_displayed_user_id() ) ) {
        return $array;
    } else {
        return '';
    }
} );

add_filter( 'bp_get_send_public_message_button', function( $r ) {
    if ( friends_check_friendship( bp_loggedin_user_id(), bp_displayed_user_id() ) ) {
        return $r;
    } else {
        $r['component'] = '';
        return $r;
    }
} );