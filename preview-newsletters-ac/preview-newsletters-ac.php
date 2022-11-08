<?php
/**
 * @package PreviewNewslettersAC
 * @version 1.0
 */
/*
Plugin Name: Preview Newsletter Active Campaign
Plugin URI: https://github.com/gwannon/Preview-Newsletter-Active-Campaign
Description: Este plugin saca los últimos newsletter de Active Campaign para que puedan ser visualizados por los usuarios usando el código corto [previewnewsletters].
Version: 1.0
Author: @Gwannon
Author URI: https://github.com/gwannon
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*function pnlac_plugins_loaded() {
	load_plugin_textdomain( 'pnlac', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('init', 'pnlac_plugins_loaded', 0 );*/

function pnlac_curl_call($link, $api_key) {
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $link);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Api-Token: '.$api_key));
  $json = json_decode(curl_exec($curl));
  curl_close($curl);
  return $json;
}

function pnlac_preview_newsletter(){
	if(isset($_REQUEST['preview_newsletter'])) {
		$api_url = get_option("_pnlac_api_url");
		$api_key = get_option("_pnlac_api_key");
		$link = $api_url."/api/3/campaigns?orders[sdate]=DESC&offset=0&limit=10";
		$json = pnlac_curl_call($link, $api_key);
		foreach($json->campaigns as $campaign) {
			if(md5($campaign->name) == $_REQUEST['preview_newsletter']) {
				$message = pnlac_curl_call($campaign->links->campaignMessage, $api_key);
				$htmlcode = pnlac_curl_call($message->campaignMessage->links->message, $api_key);
				echo $htmlcode->message->html;
				break;
			}
		}
		die;

	}
}
add_action( "template_redirect", "pnlac_preview_newsletter" );

//Shortcodes
function pnlac_shortcode($params = array(), $content) {

	$html = "<div id='newsletters'>";
	$api_url = get_option("_pnlac_api_url");
	$api_key = get_option("_pnlac_api_key");
	$link = $api_url."/api/3/campaigns?orders[sdate]=DESC&offset=0&limit=10";
	$items = array();
	$json = pnlac_curl_call($link, $api_key);
	$codes = explode(",", $content);
	foreach ($codes as $key => $code) {
		foreach($json->campaigns as $campaign) {
			if(preg_match("/".$code."/", $campaign->name)) {
				unset ($codes[$key]);
				$message = pnlac_curl_call($campaign->links->campaignMessage, $api_key);
				$html .= "<a href='".(parse_url(get_the_permalink(), PHP_URL_QUERY) ? '&' : '?') . "preview_newsletter=". md5($campaign->name). "'>".$message->campaignMessage->subject."<span style='background-image: url(".$message->campaignMessage->screenshot.");'></span></a>";
				break;
			}
		}
	}
	$html .= "</div><style>
	#newsletters {
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
		align-content: flex-start;
		justify-content: center;
		align-items: stretch;
		gap: 20px;
	}
	
	#newsletters > a {
		display: block;
		width: calc(100% - 20px);
		background: #f00 none no-repeat;
		border: 1px solid #f00;
		font-weight: 600;
		padding: 10px 20px 460px;
		font-size: 16px;
		color: #fff;
		transition: background 0.3s;
		margin: 0px 0px 5px 0px;
		text-decoration: none;
		position: relative;
		box-sizing: border-box;
	}

	@media (min-width: 650px) {
		#newsletters > a { width: calc(50% - 20px); }
	}
	@media (min-width: 1200px) {
		#newsletters > a { width: calc(25% - 20px); }
	}

	#newsletters > a:hover {
		background-color: #333;
		border-color: #333;
	}

	#newsletters > a > span {
		display: block;
		background: #f00 none no-repeat;
		background-size: 100% auto;
		height: 450px;
		position: absolute;
		bottom: 0px;
		left: 0px;
		height: 450px;
		width: 100%;
	}
	</style>";
	return $html;
}
add_shortcode('previewnewsletters', 'pnlac_shortcode');

//Administrador 
add_action( 'admin_menu', 'pnlac_plugin_menu' );
function pnlac_plugin_menu() {
	add_options_page( __('Preview Newsletters', 'pnlac'), __('Preview Newsletters', 'pnlac'), 'manage_options', 'pnlac', 'pnlac_page_settings');
}

function pnlac_page_settings() { 
	?><h1><?php _e("Configuración", 'pnlac'); ?></h1><?php 
	if(isset($_REQUEST['send']) && $_REQUEST['send'] != '') { 
		?><p style="border: 1px solid green; color: green; text-align: center;"><?php _e("Datos guardados correctamente.", 'pnlac'); ?></p><?php
		update_option('_pnlac_api_key', $_POST['_pnlac_api_key']);
		update_option('_pnlac_api_url', $_POST['_pnlac_api_url']);
	} ?>
	<form method="post">
		<h2><?php _e("AC Api key", 'pnlac'); ?>:</h2>
		<input type="text" name="_pnlac_api_key" value="<?php echo get_option("_pnlac_api_key"); ?>" />
		<h2><?php _e("AC Api URL", 'pnlac'); ?>:</h2>
		<input type="text" name="_pnlac_api_url" value="<?php echo get_option("_pnlac_api_url"); ?>" /><br/><br/>
		<input type="submit" name="send" class="button button-primary" value="<?php _e("Guardar", 'pnlac'); ?>" />
	</form>
	<?php
}
