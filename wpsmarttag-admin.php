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
			
			$sendApiKey=strip_tags($_POST['apikey']);
			$sendEmail=strip_tags($_POST['email']);
			wp_smarttag_admin_api_submit($sendApiKey,$sendEmail);
	
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
		echo '<input id="smarttag_api" class="button button-primary" type="submit" value="Save Settings">';
	echo '</form>';
	
	
	
}


function wp_smarttag_admin_api_submit($key,$email){
	global $wp_smart_tag_url;
	
	//echo $key;
	//echo $email;
	//echo $wp_smart_tag_url;
	
	$fields = array(
		'apikey' => trim(strip_tags($key)),
		'apiemail' => trim(strip_tags($email)),
		'do' => 'verifyapikey',
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$wp_smart_tag_url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS,$fields);

		// receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec ($ch);
		curl_close ($ch);
	
		$response = json_decode($server_output,true);
		//echo "here";
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