<?php
/*
Plugin Name: Gravity Forms Button - An Ajax Form Loader
Plugin URI: http://www.stevenhenty.com
Description: Load Gravity Forms at the touch of a button. Shortcode usage: [gravityforms action="button" id=1 text="button text"]
Version: 0.1
Author: stevehenty
Author URI: http://www.stevenhenty.com

------------------------------------------------------------------------
Copyright 2015 Steven Henty

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

// Hook up the AJAX ajctions
add_action( 'wp_ajax_nopriv_gf_button_get_form', 'gf_button_ajax_get_form' );
add_action( 'wp_ajax_gf_button_get_form', 'gf_button_ajax_get_form' );

// Add the "button" action to the gravityforms shortcode
// e.g. [gravityforms action="button" id=1 text="button text"]
add_filter( 'gform_shortcode_button', 'gf_button_shortcode', 10, 3 );

function gf_button_shortcode( $shortcode_string, $attributes, $content ){
	$a = shortcode_atts( array(
		'id' => 0,
		'text' => 'Show me the form!',
		'button_class' => ''
	), $attributes );

	$form_id = absint( $a['id'] );

	if ( $form_id < 1 ) {
		return 'Missing the ID attribute.';
	}

	// Enqueue the scripts and styles
	gravity_form_enqueue_scripts( $form_id, true );

	$ajax_url = admin_url( 'admin-ajax.php' );

	$html = sprintf( '<button id="gf_button_get_form_%d" class="%s">%s</button>', $form_id, $a['button_class'], $a['text'] );
	$html .= sprintf( '<div id="gf_button_form_container_%d" style="display:none;"></div>', $form_id );
	$html .= "<script>
				(function (SHFormLoader, $) {
				$('#gf_button_get_form_{$form_id}').click(function(){
					var button = $(this);
					$.get('{$ajax_url}?action=gf_button_get_form&form_id={$form_id}',function(response){
						$('#gf_button_form_container_{$form_id}').html(response).fadeIn();
						button.remove();
						if(window['gformInitDatepicker']) {gformInitDatepicker();}
					});
				});
			}(window.SHFormLoader = window.SHFormLoader || {}, jQuery));
			</script>";
	return $html;
}

function gf_button_ajax_get_form(){
	$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
	gravity_form( $form_id,true, false, false, false, true );
	die();
}
