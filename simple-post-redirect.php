<?php
/*
Plugin Name:  Simple Post Redirect
Plugin URI: http://wordpress.org/plugins/simple-post-redirect/
Description: This plugin allows you to make simple redirects of single pages of any custom post type to any url.
Author: Mohit Agarwal
Version: 1.0
Author URI: http://agarwalmohit.com/
Stable tag: "trunk"
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Simple Post Redirect is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Simple Post Redirect is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Simple Post Redirect. If not, see http://www.gnu.org/licenses/gpl-2.0.html
*/




/**
 * @package Simple Post Redirect
 * @version 1.0
 */

function me_spr_redirect_add_meta_boxes()
{
    foreach ( array_keys( $GLOBALS['wp_post_types'] ) as $post_type )
    {
        // Skip:
        if ( in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item' ) ) )
            continue;

	  $args = array();

	  add_meta_box(
		'me_spr_redirect_url',
		__( 'Simple Redirect to: ', 'my_textdomain' ), // Title
		'me_spr_redirect_callback_function',               // Callback function that renders the content of the meta box
		$post_type,                               // Admin page (or post type) to show the meta box on
		'side',                               // Context where the box is shown on the page
		'high',                               // Priority within that context
		$args                                 // Arguments to pass the callback function, if any
	  );
    }
}

add_action( 'add_meta_boxes', 'me_spr_redirect_add_meta_boxes' );

function me_spr_redirect_callback_function( $args ){
	
		$saved_url = get_post_meta(get_the_ID(),'me_spr_post_redirect',true);
		$html = '<input style="width:100%;margin-top:5px;" placeholder="Type a URL here." type="text" name="me_spr_post_redirect" value="'.$saved_url.'">';
		echo $html;
        wp_nonce_field( 'custom_nonce_action', 'custom_nonce' );
}


function me_spr_redirect_metabox_save( $post_id ) {

  if( !isset( $_POST['custom_nonce'] ) || !wp_verify_nonce( $_POST['custom_nonce'],'custom_nonce_action') ) {
  return;}
  
  
  if ( !current_user_can( 'edit_post', $post_id )){
   // print 'Sorry, you do not have sufficient permission to edit this page.';
  return;}


  if ( isset($_POST['me_spr_post_redirect']) ) {        
    update_post_meta($post_id, 'me_spr_post_redirect', esc_url_raw($_POST['me_spr_post_redirect']));      
  } else {
    delete_post_meta($post_id, 'me_spr_post_redirect');
  }

}
add_action('save_post', 'me_spr_redirect_metabox_save');


add_filter('template_redirect','me_spr_permalink_redirect');
function me_spr_permalink_redirect($permalink) {
    global $post;
	$url = get_post_meta($post->ID,'me_spr_post_redirect',true);
    if (!empty($url)) {
		wp_redirect($url,301);
		exit;
    }
    return ;
}