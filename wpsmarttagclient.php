<?php
/*
Plugin Name:SmartTag Client
Plugin URI: https://smarttag.ca
Description: Auto generate WordPress tags & keywords improve SEO and content searchability using Artificial Intelligence. SmartTag uses advanced Internet search technology and Artificial Intelligence to find relevant keywords for your content.
Version: 1.0
Author: SmartTag
Author URI: https://smarttag.ca
*/

$wp_smart_tag_url = "https://smarttag.ca/api/";

include('wpsmarttag-admin.php');

add_action( 'add_meta_boxes', 'wp_smart_tag_create_meta_box' );
function wp_smart_tag_create_meta_box()
{
	remove_meta_box( 'wp-smart-tag' , 'post' , 'high' ); 	
	$post_types = get_post_types();
	foreach ( $post_types as $post_type ) {
		        add_meta_box( 'wp-smart-tag', 'SmartTag', 'wp_smart_tag_meta_box', $post_type, 'normal', 'high' );
	}
} 


add_action( 'add_meta_boxes', 'smarttag_move_taxonomy_metabox' );

function smarttag_move_taxonomy_metabox() {

    global $wp_meta_boxes;

    $taxonomy    = 'wp-smart-tag';
    $metabox_key = $taxonomy; // or $taxonomy . 'div' if hierarhical

    if ( isset( $wp_meta_boxes['post']['side']['core'][$metabox_key] ) ) {

        $metabox = $wp_meta_boxes['post']['side']['core'][$metabox_key];
        unset( $wp_meta_boxes['post']['side']['core'][$metabox_key] );
        $wp_meta_boxes['post']['normal']['core'][$metabox_key] = $metabox;
    }
}


function wp_smart_tag_meta_box()
{
   

	echo '<div id="wpst_current_usage" style="margin-bottom:10px;" class="panel-body"></div>';

	if( strlen(get_option( 'wp_smarttag_apikey' )) > 30 ){
		echo '<a href ="http://smarttag.ca/pricing" target="_blank" class="button button-primary button-large">Get Credits</a>';
		echo '<div style="margin-bottom:10px;"><h4>Scan your content for keywords using SmartTag</h4>';
		echo '<input id="wpsmarttag_scan_btn" class="button button-primary button-large" type="button" value="Scan Content ( 1 Credit )"></div>';
		
		echo '<div id="json_response"></div>';
		
		echo '<div id="wpst_response_container"></div>';
	}else{
		
		
		echo '<h2>Please add your API key. <a href="../wp-admin/admin.php?page=SmartTag-options" target="_blank">Click Here</a></h2>';
	}
   
}


function sample_admin_notice__success() {
	?>
	<div class="notice notice-success is-dismissible">
		<p><?php _e( 'Done!', 'sample-text-domain' ); ?></p>
	</div>
	<?php
}



add_action( 'wp_ajax_wpst_ajax', 'wpst_ajax' );

function wpst_ajax() {
	global $wpdb,$wp_smart_tag_url; // this is how you get access to the database
	
	
	$apikey = get_option( 'wp_smarttag_apikey' );
	$apiemail = get_option( 'wp_smarttag_email' );
	$subscriptioncache = get_option( 'wp_smarttag_subscription' );
	$usagecache = get_option( 'wp_smarttag_usage' );
	

	if($_POST['do']=='getkeywordscontent'){
		
		
		$patterns = "/\[[\/]?vc_[^\]]*\]/";
		$content = urldecode($_POST['content']);
		$content = strip_shortcodes(preg_replace($patterns, '', $content));		
		
		$body = array(
			'content' =>  $content,
			'apikey' => $apikey,
			'apiemail' => $apiemail,
			'do' => 'getkeywordscontent',
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
		
		$server_output = wp_remote_post( $wp_smart_tag_url, $args );		
		
		
		echo($server_output['body']);
	}
	
	if($_POST['do']=='getkeyword'){
		
		
		$body = array(
		'keyword' => $_POST['keyword'],
		'apikey' => $apikey,
		'apiemail' => $apiemail,
		'do' => 'getkeyword',
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
		
		$server_output = wp_remote_post( $wp_smart_tag_url, $args );		
		
		echo( $server_output['body']);
	}
	
	
	wp_die(); // this is required to terminate immediately and return a proper response
}




add_action( 'admin_footer', 'wpsmarttag_ajax_footer' ); // Write our JS below here

function wpsmarttag_ajax_footer() { ?>


	<script type="text/javascript" >
	
	$  = jQuery;
	
	var keywordlist =[];
	
	function do_wpsmarttag_ajax(){	
		console.log('do_wpsmarttag_ajax');
		
		contentdata = encodeURIComponent($("#content").val());

		var data = {
			'action': 'wpst_ajax',
			'content': contentdata,
			'do': 'getkeywordscontent'
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			//alert('Got this from the server: ' + response);
			//jQuery("#json_response").html(response);
			rawjson = jQuery.parseJSON(response);
			console.log(rawjson);
			
			
			if(rawjson.subscription){

					wpst_update_subscription_box(rawjson.subscription);
				
			}
			
			if(rawjson.usage){

					wpst_update_usage_box(rawjson.usage.credits);
				
			}
			
			if(rawjson.data.keywords){
				
				for (var k in rawjson.data.keywords){
					
					wpst_generate_keyword_box(k);
					
				}
				
			}
			
			if(rawjson.data.watson.keywords){
				
				for (var k in rawjson.data.watson.keywords){
					console.log(rawjson.data.watson.keywords);
					wpst_generate_keyword_box(rawjson.data.watson.keywords[k].text);
					
				}
				
			}
			
			if(rawjson.data.watson.concepts){
				
				for (var k in rawjson.data.watson.concepts){
					console.log(rawjson.data.watson.concepts);
					wpst_generate_keyword_box(rawjson.data.watson.concepts[k].text);
					
				}
				
			}
			
			
		});
	
	}
	
	function wpst_update_subscription_box(subs){
	
				
				var htmldata = "";
				//console.log(subs);
				counter=0;
				for (var s in subs){
					counter++;
					
					if(counter>1){
						htmldata +=subs[s].title+', ';
					}else{
						htmldata +=subs[s].title;
					}
				}
				
				
				if(htmldata!=""){
					$("#wpst_current_subscription h4").html(htmldata);
				}
	}
	
	function wpst_update_usage_box(usage){
	
				
				var htmldata = "";
				console.log(usage);
				
				
				
				//htmldata+="Total Keywords:"+usage['total'];
				
				htmldata+="<h3>Credits</h3>";
				htmldata+="Free: "+usage['free'];
				htmldata+="<br>Subcription: "+usage['subscription'];
				htmldata+="<br>Pay As You Go: "+usage['prepaid'];
				
				if(htmldata!=""){
					$("#wpst_current_usage").html(htmldata);
				}
	}
	
	
	function do_wpsmarttag_ajax_get_keyword(keyword){	
		console.log('do_wpsmarttag_ajax_get_keyword');

		var data = {
			'action': 'wpst_ajax',
			'keyword': keyword,
			'do': 'getkeyword'
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			//alert('Got this from the server: ' + response);
			//jQuery("#json_response").html(response);
			rawjson = jQuery.parseJSON(response);
			console.log(rawjson.data);
			
			if(rawjson.data.keywords){
				
				wpst_generate_keyword_tag(rawjson);
				
			}
		
			
			if(rawjson.usage.usage){

					wpst_update_usage_box(rawjson.usage.credits);
				
			}
			
			
		});
	
	}
	 
	
	function wpst_generate_keyword_box(keyword){
		
		//console.log ("do wpst_generate_keyword_box()");
		
		var container = "#wpst_response_container";
		var keywordFormat = keyword.replace(" ","_");
		var keywordID = "wpst_keyword_"+keywordFormat.toLowerCase();
		//alert(keywordID);
		var html ="";
		console.log($("#"+keywordID).length);
		if( !$("#"+keywordID).length){
			
			
			html += '<div id="'+keywordID+'" class="postbox">';
			html += '<div><h1 style="float:left; margin-left:5px;"><span>'+keyword+'</span></h1><input style="margin-top:10px; margin-right:10px; float:right;" class="wpst_get_keyword_btn button button-primary button-large"  type="button" keyword="'+keywordFormat+'" value="Get Keyword ( 1 Credit)"></div><div style="clear:both;"></div>';
			html += '<div class="keyword_response" style="padding:10px;"></div>';
			html += '</div>';
			
			
			
			jQuery(container).append(html);
			
		}
		

		
		
		
	}

	
	function wpst_generate_keyword_tag(data){
		
		console.log ("do wpst_generate_keyword_box()");
		//console.log (data);
		var keyword = data.data.main_keyword.toLowerCase();
		var container =  "wpst_keyword_"+keyword;
		
		var html ="";
		
		
		for (var k in data.data.keywords){
					
			html+='<div style="display:inline-block;"><div style="display:inline-block; margin:3px 0px 0px 3px; border-radius: 5px 0px 0px 5px; padding:5px; line-height:10px;" class="tag button-primary">'+capitalizeFirstLetter(k)+'</div>';
			html+='<div style="display:inline-block; padding:5px; border-radius: 0px 5px 5px 0px ; margin-right:5px; line-height:10px;" class="tag-add button-primary" keyword="'+capitalizeFirstLetter(k)+'">+</div></div>';
					
		}
		

		jQuery("#"+container).find(".keyword_response").html(html);
		
		
	}
	
	
	function wpst_updatekeywordlist(){
		
		
		var keywordhtml = "";
		
		keywordlist.sort(); 
		
		
		for (var k in keywordlist){
			
			
			keywordhtml+=keywordlist[k]+', ';
		
		}
		
		$("#wpsmarttag_keyword_box").val(keywordhtml);
		
	}

	$(document).ready(function(e){

		$("#wpsmarttag_scan_btn").click(function(e){
			console.log('wpsmarttag_scan_btn');
			do_wpsmarttag_ajax();

		});
		
		$(document).on("click",".wpst_get_keyword_btn",function(e){
			
			var keyword = $(this).attr("keyword");
			do_wpsmarttag_ajax_get_keyword(keyword);
			
		});
		
		
		$(document).on("click","#wp-smart-tag .tag",function(e){
			
			var keyword = $(this).html();
			
			keywordlist[keyword]=keyword;
			
			$("#new-tag-post_tag").val(keyword);
			//$("input.tagadd").trigger('click');
			tagBox.userAction = 'add';
			tagBox.flushTags( $(document).find( '.tagsdiv' ) ,false ,1);
			
			
			//wpst_updatekeywordlist();
			
			$(this).addClass("active");
			
			
		});
		
		$(document).on("click","#wp-smart-tag .tag-add",function(e){
			
			var keyword = $(this).attr('keyword');
			wpst_generate_keyword_box(keyword);
			
			
		});
		
		$("#wpsmarttag_keyword_box").val("");


	});
	
	function capitalizeFirstLetter(string) {
    	return string.charAt(0).toUpperCase() + string.slice(1);
	}
		
		
		
</script> 
<?php
}
?>