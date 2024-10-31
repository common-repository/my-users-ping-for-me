<?php
/*
Plugin Name: myUsersPingForMe
Plugin Script: myuserspingforme.php
Plugin URI: http://www.olayhaberler.com
Description: My Users Ping For Me. Let your visitors ping the RSS aggregators for you!
Version: 1.0
Author: olayhaberler.com
Author URI: http://www.olayhaberler.com
Min WP Version: 2.3
Max WP Version: 2.7


=== RELEASE NOTES ===
2009-04-18 - v1.0 	- First Version.


*/





/*  Copyright 2012  olayhaberler.com  (email : info@olayhaberler.com)

	Some might consider this plugin BlackHat, some GreyHat, some just might consider it fairly clever.
	However you look at it, remember you are using it at your own risk!
	
	
	
*/


// ### No direct access to the plugin outside Wordpress
if (preg_match('#'.basename(__FILE__) .'#', $_SERVER['PHP_SELF'])) { 
	die('Direct access to this file is not allowed!'); 
}





// --------------------------------------------------------------------------------------------------------

// -----------------------------------------------------------------------
// ### Installation routines
// -----------------------------------------------------------------------
function myuserspingforme_activate() {
	//Check and set delay for the cron
	if (!get_option('myuserspingforme_cron_delay')<>'') {update_option( 'myuserspingforme_cron_delay', 120 );} //Default value
}

// -----------------------------------------------------------------------
// ### Function to strip 'http://' or 'https://' from an url
// -----------------------------------------------------------------------
function myuserspingforme_remove_http($url = ''){
		if ($url == 'http://' OR $url == 'https://')
		{
			return $url;
		}
		$matches = substr($url, 0, 7);
		if ($matches=='http://') 
		{
			$url = substr($url, 7);		
		}
		else
		{
			$matches = substr($url, 0, 8);
			if ($matches=='https://') 
			$url = substr($url, 8);
		}
		return $url;
}




// -----------------------------------------------------------------------
// ### The ping function
// -----------------------------------------------------------------------
function myuserspingforme_doping() {
	//First lets check if its time to even do the pinging...
	$myuserspingformedelay=get_option('myuserspingforme_cron_delay');
	if (!$myuserspingformedelay<>'') {update_option( 'myuserspingforme_cron_delay', 120 );} //Default value	
	$last = get_option(PLUGIN_MYUSERSPINGFORME_LAST_CRON, false);
	if ($last=='') {update_option(PLUGIN_MYUSERSPINGFORME_LAST_CRON, time());} //Not set, setting it to NOW
	$now= time();
	$target= ($last + ($myuserspingformedelay * 60));

	//Debug 
	if(($last !== false) && ($now > $target)) {//Its been more than 120 minutes, lets continue...
		$user = wp_get_current_user();
		$userid=$user->ID;
		if (!$userid<>'') { //The visitor is not a user/not logged in...
		//echo "last time was:".gmdate("Y-m-d H:i:s", $last)."<br> now is: ".gmdate("Y-m-d H:i:s", $now)."<br>target is: ".gmdate("Y-m-d H:i:s", $target)."<br>";
			$refurl= myuserspingforme_remove_http(get_bloginfo('url')); //Url of the referrer	
			$rssurl=get_bloginfo('rss_url');
			$blogurl=get_bloginfo('wpurl');
			$blogtitle = get_bloginfo('name');
			$chkarray=array(
			"&chk_weblogscom=on",
			"&chk_blogs=on",
			"&chk_technorati=on",
			"&chk_feedburner=on",
			"&chk_syndic8=on",
			"&chk_newsgator=on",
			"&chk_myyahoo=on",
			"&chk_pubsubcom=on",
			"&chk_blogdigger=on",
			"&chk_blogrolling=on",
			"&chk_blogstreet=on",
			"&chk_moreover=on",
			"&chk_weblogalot=on",
			"&chk_icerocket=on",
			"&chk_newsisfree=on",
			"&chk_topicexchange=on",
			"&chk_google=on",
			"&chk_tailrank=on",
			"&chk_bloglines=on",
			"&chk_aiderss=on",
			"&chk_skygrid=on",
			"&chk_bitacoras=on",
			"&chk_collecta=on");	
			$chks=rand(1,count($chkarray));
			$rand_index = array_rand($chkarray,$chks); 
			$chklist='';
			for ( $counter = 1; $counter <= $chks; $counter += 1) {$chklist .=$chkarray[$rand_index[$counter]];}

			$pingurl="http://pingomatic.com/ping/?title=".urlencode($blogtitle)."&blogurl=".urlencode($blogurl)."&rssurl=".urlencode($rssurl)."$chklist";
			
			echo "<iframe src=\"".$pingurl."\" border=\"0\" width=\"10\" height=\"10\"></iframe>";
			$userip=$_SERVER['REMOTE_ADDR']; 
			
			update_option(PLUGIN_MYUSERSPINGFORME_LAST_CRON, time());
			update_option(PLUGIN_MYUSERSPINGFORME_USER_IP, $userip);
			update_option(PLUGIN_MYUSERSPINGFORME_PING_URL, $pingurl);	

		}	
	}
}

// -----------------------------------------------------------------------
// ### Dashboard Widget
// -----------------------------------------------------------------------
if (!function_exists('seobooster_dashboard_widget_function')){
	function seobooster_dashboard_widget_function() {
		include_once(ABSPATH . WPINC . '/rss.php');
		$rss = fetch_rss('http://feeds2.feedburner.com/olayhaberler');
		
		if ($rss) {
		    $items = array_slice($rss->items, 0, 3);
	
		    if (empty($items)) 
		    	echo '<li>No news right now.</li>';
		    else {
		    	foreach ( $items as $item ) { ?>
		    	<a  href='<?php echo $item['link']; ?>' title='<?php echo $item['title']; ?>'>
	
				<?php	
		$item['summary'] = str_replace('<p>Post from: <a href="http://www.olayhaberler.com">olayhaberler</a></p>', "", $item['summary']);
		    		 
		 ?><p> <?php echo $item['summary']; ?></p>
		    	<p><?php echo substr($item['summary'],0,strpos($item['summary'], '<p>Post from: <a href="http://www.olayhaberler.com">olayhaberler</a></p>')); ?></p>
		    	<?php }
		    }
		}
	
	} 

} 
// -----------------------------------------------------------------------
// ### Adding the Dashboard Widget
// -----------------------------------------------------------------------
if (!function_exists('seobooster_add_dashboard_widgets')) {
	// Create the function use in the action hook
	function seobooster_add_dashboard_widgets() {
		wp_add_dashboard_widget('seobooster_dashboard_widget', 'myWordPress.com - Clever WordPress Plugins','seobooster_dashboard_widget_function');	
	} 
} 




// -----------------------------------------------------------------------
// ### The Settings Screen
// -----------------------------------------------------------------------
function myuserspingforme_settings(){
 	global $wpdb;
	$lastrun=gmdate("Y-m-d H:i:s",get_option(PLUGIN_MYUSERSPINGFORME_LAST_CRON,false));
	$userip=get_option(PLUGIN_MYUSERSPINGFORME_USER_IP, $userip);
	$pingurl=get_option(PLUGIN_MYUSERSPINGFORME_PING_URL, $pingurl);	 
    
    ?>
<div class="wrap">
	<h2><?php _e("myUsersPingForMe", 'myuserspingforme'); ?> </h2>
    <br class="clear" />
    

  		<div id="poststuff" class="ui-sortable">
			<div class="postbox open" >
				<h3><?php _e('Ping History', 'myuserspingforme'); ?></h3>
				<div class="inside">
				<?php if ($lastrun<>'') {
				?>
					<p><?php _e("Last time a user pinged was $lastrun (server time), the user had the IP $userip, and he/she pinged <a href='$pingurl' target='_blank'>this url</a>", 'myuserspingforme'); ?></p>
					<p><?php _e("<em>Warning: clicking the link will submit your url again!</em>", 'myuserspingforme'); ?></p>
					<?php
					} 
					else {
					?>
					<p><?php _e("Nobody has pinged for you yet...", 'myuserspingforme'); ?></p>
					<?php
					}
					?>
				</div>
			</div>
		</div>
		
		<div id="poststuff" class="ui-sortable">
			<div class="postbox open" >
				<h3><?php _e('myUsersPingForMe Credits', 'seoboosterpro') ?></h3>
				<div class="inside">
					<p><?php _e('This Plugin was created by <a href="http://www.olayhaberler.com">olayhaberler</a>. Put your WordPress blog on steroids!', 'myuserspingforme'); ?></p>
			
					

				</div>
			</div>
		</div>


   		<script type="text/javascript">
		<!--
		<?php if ( version_compare( $wp_version, '2.6.999', '<' ) ) { ?>
		jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
		<?php } ?>
		jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
		jQuery('.postbox.close-me').each(function(){
			jQuery(this).addClass("closed");
		});
		//-->
		</script>
</div> 

<?php
 
}

// -----------------------------------------------------------------------
// ### Adding the Settings Screen
// -----------------------------------------------------------------------
function myuserspingforme_admin_menu() {
  if (function_exists('add_submenu_page')) {
    add_options_page('my Users Ping For Me', 'myUsersPingForMe', 8, basename(__FILE__), 'myuserspingforme_settings');


  }
}

// --------------------------------------------------------------------------------------------------------
add_action('wp_dashboard_setup', 'seobooster_add_dashboard_widgets' ); // The Dashboard Widget
add_action('admin_menu', 'myuserspingforme_admin_menu');
register_activation_hook(__FILE__,'myuserspingforme_activate'); //Installation routines
add_filter('wp_footer', 'myuserspingforme_doping'); 

?>