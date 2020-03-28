<?php
/**
 * @version    $version 4.6.1 Peter Bui  $
 * @copyright    Copyright (C) 2012 PB Web Development. All rights reserved.
 * @license    GNU/GPL, see LICENSE.php
 * Updated    15th March 2015
 *
 * Twitter: @astroboysoup
 * Blog: http://nicheextensions.com
 * Email: mail@nicheextensions.com
 *
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgSystemGoogleAnalytics extends JPlugin
{
    function plgGoogleAnalytics(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->_plugin = JPluginHelper::getPlugin('system', 'GoogleAnalytics');
        $this->_params = new JParameter($this->_plugin->params);
    }

    function onAfterRender()
    {
        $type = $this->params->get('type', '');
        $trackerCode = $this->params->get('code', '');
        $domain = $this->params->get('domain', 'auto');
        $verify = $this->params->get('verify', '');

        $javascript = '';
        $app = JFactory::getApplication();

        if ($app->isAdmin()) {
            return;
        }

        $buffer = JResponse::getBody();

        if($verify){
            $javascript .= "\n\n<meta name=\"google-site-verification\" content=\"".$verify."\" />\n\n";}


        if ($type == 'asynchronous') {
            $javascript .= "<script type=\"text/javascript\">
            ". $enhancedOutput ."
 var _gaq = _gaq || [];
 _gaq.push(['_setAccount', '" . $trackerCode . "']);
";

            $javascript .= "_gaq.push(['_trackPageview']);
 (function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
 })();
</script>
<!-- Asynchonous Google Analytics Plugin by PB Web Development -->";
        }

        if ($type == 'universal') {
   $javascript .= "
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '" . $trackerCode . "', '" . $domain . "');
  ga('send', 'pageview');

</script>
<!-- Universal Google Analytics Plugin by PB Web Development -->
";
        }

        $buffer = preg_replace("/<\/head>/", "\n\n" . $javascript . "\n\n</head>", $buffer);

        JResponse::setBody($buffer);

        return true;
    }
}
?>