<?php

new SC_Ajax_Login;

class SC_Ajax_Login {

	static $success = false;

	public function __construct () {
		add_action( 'wp_ajax_sc_login',        array( $this, 'login' ) );
		add_action( 'wp_ajax_nopriv_sc_login', array( $this, 'login' ) );
		add_action( 'wp_enqueue_scripts',      array( $this, 'localize'   ), 20 );
	}

	public function login() {
		// Do it this way to catch weird nonce issue.
		if ( ! wp_verify_nonce( $_POST['security'], 'sc-login' ) ) {
			wp_logout();
			wp_send_json_error();
		}

		$status = wp_signon();

		if ( is_wp_error( $status ) ) {
			if ( 'incorrect_password' == $status->get_error_code() ) {
				$message = sprintf( "The password you entered doesn't match our records. Please try again or <a href='%s' title='Password Lost and Found'>reset your password</a>.", wp_lostpassword_url() );
			} else {
				$message = str_replace( '<strong>ERROR</strong>: ', '', $status->get_error_message() );
			}
			wp_send_json_error( $message );
		}

		wp_send_json_success( $status );
	}

	public function localize() {
		wp_localize_script( 'sc', 'scAjaxLogin', array(
			'security' => wp_create_nonce( 'sc-login' ),
			'success'  => esc_html__( 'Success! Refreshing the page...', 'sc' ),
			'error'    => esc_html__( 'Something went wrong, please try again', 'sc' ),
		) );
	}

}