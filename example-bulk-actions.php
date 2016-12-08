<?php
/*
 Plugin Name: Example Bulk Actions
  Plugin URI: https://phylax.pl/plugins/example-bulk-actions/
 Description: This plugin is an example for new WordPress 4.7 feature, bulk actions filter. Install it and go to the Pages menu and see new item in bulk menu.
      Author: Łukasz Nowicki
  Author URI: http://lukasznowicki.info/
     Version: 0.1.0
     License: GPLv2 or later
 License URI: http://www.gnu.org/licenses/gpl-2.0.html
 Text Domain: examplebulkactions
 Domain path: /languages
*/
namespace Phylax\WPPlugin\ExampleBulkActions;

/**
 * Load text domain in init action
 */
add_action( 'init', __NAMESPACE__ . '\phylax_eba_init' );

/**
 * Those filters are new in WordPress 4.7, feel free to test it!
 */
add_filter( 'bulk_actions-edit-page', __NAMESPACE__ . '\phylax_eba_editpage' );
add_filter( 'handle_bulk_actions-edit-page', __NAMESPACE__ . '\phylax_eba_handler', 10, 3 );

/**
 * It isn't something new, we use this to let you know what happens
 */
add_action( 'admin_notices', __NAMESPACE__ . '\phylax_eba_notice' );

/**
 * Load text-domain
 */
function phylax_eba_init() {
	load_plugin_textdomain( 'examplebulkactions', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Add new menu item (bulk action) to its stack
 */
function phylax_eba_editpage( $bulk_actions ) {
	$bulk_actions['eba_send_pages'] = __( 'Send those pages', 'examplebulkactions' );
	return $bulk_actions;
}

/**
 * Add argument with selected post ids to redirect url
 */
function phylax_eba_handler( $redirect_to, $doaction, $post_ids ) {
	if ( $doaction !== 'eba_send_pages' ) {
		return $redirect_to;
	}
	$redirect_to = add_query_arg( 'eba_send_pages', urlencode( json_encode( $post_ids ) ), $redirect_to );
	return $redirect_to;
}

/**
 * Send email, show notice
 */
function phylax_eba_notice() {
	/**
	 * We need this, to get your email
	 */
	global $current_user;
	if ( !empty( $_REQUEST['eba_send_pages'] ) ) {
		/**
		 * Decode page ids from url
		 */
		$eba_pages = json_decode( urldecode( $_REQUEST['eba_send_pages'] ) );
		/**
		 * Prepare email body
		 */
		$msg = '<h1>' . __( 'Selected pages:', 'examplebulkactions' ) . '</h1>';
		foreach( $eba_pages as $page_id ) {
			$msg.= '<p>' . get_the_title( $page_id ) . ' (ID: ' . $page_id . ')' . '</p>';
		}
		/**
		 * Prepare email header
		 */
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers.= 'Content-Type: text/html; charset=ISO-8859-1' . "\r\n";
		/**
		 * Note, that you may change this email with something what exists (I mean
		 * your real email on the server). Some servers block emails that doesn't
		 * exists. Of course you must have postfix or similar server running!
		 */
		$headers.= 'From: you@example.com' . "\r\n";
		/**
		 * Send email
		 */
		$mail = wp_mail(
			$current_user->user_email,
			__( 'Pages you have selected', 'examplebulkactions' ),
			$msg,
			$headers
		);
		/**
		 * Show notice
		 */
		echo '<div id="message" class="' . ( $mail ? 'updated' : 'error' ) . '">';
		if ( $mail ) {
			echo '<p>' . __( 'Email has been sent, thank you!', 'examplebulkactions' ) . '</p>';
		} else {
			echo '<p>' . __( 'Something went wrong. Please note, that you may should change `from` address to get it work. See source code.', 'examplebulkactions' ) . '</p>';
		}
		echo '</div>';
	}
}

#EOF