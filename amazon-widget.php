<?php
/*
Plugin Name: Amazon Widget
Plugin URI: http://www.phpaid.org/amazon-widget
Description: Amazon Widget, simple adds a single product to a side bar widget. A demo can be seen at <a href="http://allsouthpark.info">All South Park</a> as well as many of our other sites that run this widget.  It simply works!
Author: PHP AID
Version: 1
Author URI: http://www.phpaid.org
*/
 if ( ! defined( 'WP_CONTENT_URL' ) )
       define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
 if ( ! defined( 'WP_CONTENT_DIR' ) )
       define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
 if ( ! defined( 'WP_PLUGIN_URL' ) )
       define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
 if ( ! defined( 'WP_PLUGIN_DIR' ) )
       define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
define('RX',WP_PLUGIN_DIR.'/amazon-widget');


function triqui_widget() {
	
    /* Example usage of the Amazon Product Advertising API */
    $s = get_option('aws_key');
	$sk = get_option('aws_secret_key');
	
	include(RX."/amazon_api_class.php");

    $obj = new AmazonProductAPI();
    $searchTerm = get_option('amazon_search');
    try
    {
        $result = $obj->searchProducts($searchTerm,
                                       AmazonProductAPI::DVD,
                                       "TITLE");
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }

    $purl = $result->Items->Item->DetailPageURL;
    $ptitle = $result->Items->Item->ItemAttributes->Title;
	$pprice = $result->Items->Item->OfferSummary->LowestUsedPrice->FormattedPrice;
	$preview = $result->Items->Item->EditorialReviews->EditorialReview->Content;

	echo "<li style=\"text-align:center:\" ><center><a href=\"$purl\"><h2>$ptitle</h2><img  border=\"0\" src=\"" . $result->Items->Item->MediumImage->URL . "\" /></a><br><br>$pprice</center></li>";
    
}

add_filter('plugin_row_meta', 'aw_filter_plugin_links', 10, 2);
// Add FAQ and support information
function aw_filter_plugin_links($links, $file)
{
	if ( $file == plugin_basename(__FILE__) )
	{
		$links[] = '<a href="options-general.php?page=amazon-widget/amazon-widget.php">' . __('Settings','aw') . '</a>';
	}
	
	return $links;
}

function widget_control(){

	$s = get_option('amazon_search');
	if(empty($s)):
		$s = 'South Park';
		add_option('amazon_search',$s);
	endif;
	if(isset($_POST['search'])):
		update_option('amazon_search',$_POST['search']);
	endif;
	?>
	<label>Amazon Search Term</label>
	<input type="text" name="search" value="<?php echo $s;?>" />
	<?php
	
}
function amazon_widget_form(){

	
	?>
	<h3>Amazon Widget Settings Page</h3>
	
	<form action="options.php" method="post">
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="aws_key,aws_secret_key" />
	<?php wp_nonce_field('update-options'); ?>
	<table class="form-table">
	<tr align="top">
	<th scope="row">Amazon AWS Key</th>
	<td><input type="text" name="aws_key" value="<?php echo get_option('aws_key'); ?>" /></td>
	</tr>
	<th scope="row">Amazon AWS Secret Key</th>
	<td><input type="text" name="aws_secert_key" value="<?php echo get_option('aws_secret_key'); ?>" /></td>
	</tr>
	</table>
	<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
	</form>
	<?php
	
}
function init_triqui(){
	register_sidebar_widget("Amazon Widget", "triqui_widget");
    register_widget_control("Amazon Widget", 'widget_control', 300, 200 );     
}
function amazon_admin(){
	add_options_page('Amazon Widget','Amazon Widget','manage_options',__FILE__,'amazon_widget_form');
	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}
function register_mysettings() {
	//register our settings
	register_setting( 'aws-settings', 'aws_key' );
	register_setting( 'aws-settings', 'aws_secret_key' );
}

add_action('admin_menu','amazon_admin');
add_action("plugins_loaded", "init_triqui");
 
?>