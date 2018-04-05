<?php


add_action( 'admin_menu', 'wp_smarttag_admin_menu' );

/** Step 1. */
function wp_smarttag_admin_menu() {
	//add_options_page( 'SmartTag Options', 'SmartTag', 'manage_options', 'SmartTag-menu', 'wp_smarttag_admin_options' );
	add_menu_page('SmartTag Options', 'SmartTag', 'manage_options', 'SmartTag-options', 'wp_smarttag_admin_options');
}


function wp_smarttag_admin_options(){
	
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
		
		if($_GET['submit']=='apikey' && $_POST['apikey']){
			if(check_admin_referer( 'smarttag_api')){
				
				$sendApiKey=sanitize_text_field(trim(strip_tags($_POST['apikey'])));
				$sendEmail=sanitize_email(trim(strip_tags($_POST['email'])));
				wp_smarttag_admin_api_submit($sendApiKey,$sendEmail);
			}
		}
		
		//get  creds
		$smarttag_apikey = '';
		$smarttag_email = '';
		if(get_option( 'wp_smarttag_apikey' )){

			$smarttag_apikey = get_option( 'wp_smarttag_apikey' );
		
		}
		//get creds
		if(get_option( 'wp_smarttag_email' )){

			$smarttag_email = get_option( 'wp_smarttag_email' );
		
		}
	
	
	echo '<div class="wrap">';
	
	echo '<h2>SmartTag</h2>';
	
	echo '</div>';
	
	echo '<h3>Welcome to SmartTag Settings. Please enter your API key below. If you do not have an API key please visit <a href="https://smarttag.ca/" target="_blank">https://smarttag.ca/</a> to create an account</h3>';
	
	echo '<form action="?page=SmartTag-options&submit=apikey" method="post">';
		echo '<div>API Key: <input class="regular-text" name="apikey" id="smarttag_api" type="text" value="'.$smarttag_apikey.'"></div>';
		echo '<div>Account Email: <input class="regular-text" name="email" id="smarttag_api" type="text" value="'.$smarttag_email.'"></div>';
		
		echo wp_nonce_field('smarttag_api');
		
		echo '<input id="smarttag_api" class="button button-primary" type="submit" value="Save Settings">';
	echo '</form>';
	
	
	
}


function wp_smarttag_admin_api_submit($key,$email){
	global $wp_smart_tag_url;
	

		
		$body = array(
			'apikey' =>  sanitize_text_field(trim(strip_tags($key))),
			'apiemail' => sanitize_email(trim(strip_tags($email))),
			'do' => 'verifyapikey',
		);
		
		$args = array(
			'body' => $body,
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'cookies' => array()
		);
		
		$response = wp_remote_post( $wp_smart_tag_url, $args );		
		
		$response = json_decode($response['body'],true);
		//print_r($response);
		
		if($response['msg'] == 'valid'){
		
			$option_name = 'wp_smarttag_apikey' ;
			$new_value = $key ;
		
			if ( get_option( $option_name ) !== false ) {

				// The option already exists, so we just update it.
				update_option( $option_name, $new_value );

			} else {

				// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
				$deprecated = null;
				$autoload = 'no';
				add_option( $option_name, $new_value, $deprecated, $autoload );
			}
			
			$option_name = 'wp_smarttag_email' ;
			$new_value = $email ;
		
			if ( get_option( $option_name ) !== false ) {

				// The option already exists, so we just update it.
				update_option( $option_name, $new_value );

			} else {

				// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
				$deprecated = null;
				$autoload = 'no';
				add_option( $option_name, $new_value, $deprecated, $autoload );
			}
			
			
			echo '<div id="message" class="updated notice notice-success"><p>API Key is valid<p></div>';
			
		
		}else{
			
			
		
			echo '<div id="message" class="error"><p>API Key is invalid<p></div>';
			
		}
		
	
}



?>