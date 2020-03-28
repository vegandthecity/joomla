<?php
/**
 *
 * @version		1.0.0
 * @package		Google TagManager
 * @subpackage  System.GoogleTagManager
 * @copyright	2015 Tools for Joomla, www.toolsforjoomla.com
 * @license		GNU GPL
 * fixed location of GTM code.
 * Added option to place dataLayer declaration at the top of the <head> area
 * Fix so that dataLayer is not added to <header> tags
 * Add scrolling updates
 * 0.0.11 Update to handle multiple <body> and multiple <head> tags
 * 0.0.12 Update to load javascript in the <head> tag and iframe in the <body> section.
 * 1.0.0 Added the update server
 * 1.0.1 Corrected the JavaScript reference location
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

class plgSystemGoogleTagManager extends JPlugin {

	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		$addScrollTracker = $this->params->get('add_scrolltracker','');
		$scrollTrackerContentId = $this->params->get('scroll_tracker_content_id','');
		if ($addScrollTracker == 'on') {
			// Add the pagescrolling JavaScript library
			JHtml::_('jquery.framework');
			$document = JFactory::getDocument();
			$document->addScript('plugins/system/googletagmanager/js/scroll-tracker.js');
			$jsContent = '
jQuery(document).ready(function(){jQuery.contentIdPlugin.contentIdValue(\''.$scrollTrackerContentId.'\')});';
			$document->addScriptDeclaration( $jsContent );
		}
	}

	function onAfterRender() {

		// don't run if we are in the index.php or we are not in an HTML view
		if(strpos($_SERVER["PHP_SELF"], "index.php") === false || JRequest::getVar('format','html') != 'html'){
			return;
			}
		

		// Check to see if we are in the admin and if we should track
		$trackadmin = $this->params->get('trackadmin','');
		$mainframe = JFactory::getApplication();
		if($mainframe->isAdmin() && ($trackadmin != 'on')) {
			return;
			}
		
		// Get the Body of the HTML - have to do this twice to get the HTML
		$buffer = JResponse::getBody();
		$buffer = JResponse::getBody();
		// Get our Container ID and Track Admin parameter
		$container_id = $this->params->get('container_id','');
		$addDataLayer = $this->params->get('add_datalayer','');
		$dataLayerName = $this->params->get('datalayer_name','dataLayer');
		$addTrackLogin = $this->params->get('track_userLogin','');

		// String containing the Google Tag Manager JavaScript code including the container id 
		$gtm_js_container_code = "\n<!-- Google Tag Manager JS V.1.0.0 from Tools for Joomla -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','".$dataLayerName."','".$container_id."');</script>
<!-- End Google Tag Manager JS -->";

		$dataLayerCode = '';
		if ($addDataLayer == 'on') {
			$dataLayerCode = 'window.'.$dataLayerName.' = window.'.$dataLayerName.' || [];';
			// Tracked Logged in User here
			$user = JFactory::getUser();
			if ($addTrackLogin == 'on' && !$user->guest) {
				$dataLayerCode .= $dataLayerName.".push({'event': 'user_loggedin', 'user_id' : '".$user->id."'});";
			}
			// Match on head tag and new expression to NOT match on header tag
			$buffer = preg_replace ("/(<head(?!er).*>)/i", "$1"."\n<script>".$dataLayerCode."</script>".$gtm_js_container_code, $buffer, 1);
		}
		else {
			$buffer = preg_replace ("/(<head(?!er).*>)/i", "$1".$gtm_js_container_code, $buffer, 1);
		}
		// String containing the iframe code to be placed after the <body> tag
		$gtm_iframe_container_code = "\n<!-- Google Tag Manager iframe V.1.0.0 from Tools for Joomla -->
<noscript><iframe src='//www.googletagmanager.com/ns.html?id=".$container_id."'
height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
<!-- End Google Tag Manager iframe -->";
		
		// update to limit = 1 to add tag to only the first <body.*> tag
		$buffer = preg_replace ("/(<body.*?>)/is", "$1".$gtm_iframe_container_code, $buffer, 1);
		
		JResponse::setBody($buffer);
		
		return true;
		}
	}