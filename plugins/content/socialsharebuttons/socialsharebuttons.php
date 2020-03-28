<?php
/**
 * @package		 Social Share Buttons Plugins
 * @subpackage	 Social
 * @author		 E-max
 * @copyright    Copyright (C) 2008 - 2013 - E-max. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Social Share Buttons Plugin
 *
 * @package		Social Share Buttons Plugins
 * @subpackage	Social
 * @since 		1.5
 */
class plgContentSocialShareButtons extends JPlugin {
    
    private $fbLocale = "en_US";
    private $currentView = "";
    
    /**
     * Constructor
     *
     * @param object $subject The object to observe
     * @param array  $config  An optional associative array of configuration settings.
     * Recognized key values include 'name', 'group', 'params', 'language'
     * (this list is not meant to be comprehensive).
     * @since 1.5
     */
    public function __construct(&$subject, $config = array()) {
        parent::__construct($subject, $config);
        
        if($this->params->get("fbDynamicLocale", 0)) {
            $lang = JFactory::getLanguage();
            $locale = $lang->getTag();
            $this->fbLocale = str_replace("-","_",$locale);
        } else {
            $this->fbLocale = $this->params->get("fbLocale", "en_US");
        }
        
    }
    
    /**
     * Add social buttons into the article
     *
     * Method is called by the view
     *
     * @param   string  The context of the content being passed to the plugin.
     * @param   object  The content object.  Note $article->text is also available
     * @param   object  The content params
     * @param   int     The 'page' number
     * @since   1.6
     */
    public function onContentPrepare($context, &$article, &$params, $limitstart) {

        $app = JFactory::getApplication();
        /* @var $app JApplication */

        if($app->isAdmin()) {
            return;
        }
        
        $doc     = JFactory::getDocument();
        /* @var $doc JDocumentHtml */
        $docType = $doc->getType();
        
        // Check document type
        if(strcmp("html", $docType) != 0){
            return;
        }
        
        $currentOption = JRequest::getCmd("option");
        
        if( ($currentOption != "com_content") OR !isset($this->params)) {
            return;            
        }
        $custom   = $this->params->get('custom');
		if ($custom) {
        	$ok = strstr ($article->text, '{socialsharebuttons}');
		}
		else {
			$ok=0;
		}
        $this->currentView  = JRequest::getCmd("view");
        
        /** Check for selected views, which will display the buttons. **/   
        /** If there is a specific set and do not match, return an empty string.**/
        $showInArticles     = $this->params->get('showInArticles');
        
        if(!$showInArticles AND (strcmp("article", $this->currentView) == 0)){
            return "";
        }
        
        // Check for category view
        $showInCategories   = $this->params->get('showInCategories');
        
        if(!$showInCategories AND (strcmp("category", $this->currentView) == 0)){
            return;
        }
        
        if($showInCategories AND ($this->currentView == "category")) {
            $articleData        = $this->getArticle($article);
            $article->id        = $articleData['id'];
            $article->catid     = $articleData['catid'];
            $article->title     = $articleData['title'];
            $article->slug      = $articleData['slug'];
            $article->catslug   = $articleData['catslug'];
        }
        
        if(!isset($article) OR empty($article->id) ) {
            return;            
        }
        
        $excludeArticles = $this->params->get('excludeArticles');
        if(!empty($excludeArticles)){
            $excludeArticles = explode(',', $excludeArticles);
        }
        settype($excludeArticles, 'array');
        JArrayHelper::toInteger($excludeArticles);
        
        // Exluded categories
        $excludedCats           = $this->params->get('excludeCats');
        if(!empty($excludedCats)){
            $excludedCats = explode(',', $excludedCats);
        }
        settype($excludedCats, 'array');
        JArrayHelper::toInteger($excludedCats);
        
        // Included Articles
        $includedArticles = $this->params->get('includeArticles');
        if(!empty($includedArticles)){
            $includedArticles = explode(',', $includedArticles);
        }
        settype($includedArticles, 'array');
        JArrayHelper::toInteger($includedArticles);
        
        if(!in_array($article->id, $includedArticles)) {
            // Check exluded places
            if(in_array($article->id, $excludeArticles) OR in_array($article->catid, $excludedCats)){
                return "";
            }
        }
            
        // Generate content
		$content      = $this->getContent($article, $params);
        $position     = $this->params->get('position');
        
        switch($position){
			case 0:
                $article->text = $content . $article->text . $content;
                break;
            case 1:
                $article->text = $content . $article->text;
                break;
            case 2:
                $article->text = $article->text . $content;
                break;
            default:
                break;
        }
        if ($ok) {
			$article->text = str_replace('{socialsharebuttons}', $content, $article->text);
		}
        return;
    }
    
    /**
     * Generate content
     * @param   object      The article object.  Note $article->text is also available
     * @param   object      The article params
     * @return  string      Returns html code or empty string.
     */
    private function getContent(&$article, &$params){
        
        $doc   = JFactory::getDocument();
        /* @var $doc JDocumentHtml */
        
        $doc->addStyleSheet(JURI::root() . "plugins/content/socialsharebuttons/style/style.css");
        
        $url = JURI::getInstance();
        $root= $url->getScheme() ."://" . $url->getHost();
        
        $url = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug), false);
        $url = $root.$url;
        $title= htmlentities($article->title, ENT_QUOTES, "UTF-8");
        
        $html = '<!-- Social Share Buttons | Powered by <a href="http://e-max.it/promozione-siti-web" title="e-max.it: posizionamento sui motori" target="_blank" rel="nofollow">e-max.it: la strada del successo</a> -->
        <div class="social-share-buttons-share">';
        $html .= $this->getFacebookLike($this->params, $url, $title);
        $html .= $this->getFacebookShareMe($this->params, $url, $title);        
        $html .= $this->getTwitter($this->params, $url, $title);
        $html .= $this->getReTweetMeMe($this->params, $url, $title);
        $html .= $this->getGooglePlusOne($this->params, $url, $title);
        $html .= $this->getLinkedIn($this->params, $url, $title);
        $html .= $this->getBuzz($this->params, $url, $title);
        $html .= $this->getDigg($this->params, $url, $title);
        $html .= $this->getStumbpleUpon($this->params, $url, $title);
		$html .= $this->getTumblr($this->params, $url, $title);
		$html .= $this->getReddit($this->params, $url, $title);
		$html .= $this->getPinterest($this->params, $url, $title);
		$html .= $this->getBufferApp($this->params, $url, $title);
    
        $html .= '
        </div>
        <div style="clear:both;"></div>
        ';

    	$credits = $this->params->get('credits');
			if ($credits) {
				$html .= '<div class="social_share_buttons_credits"><a href="http://e-max.it/posizionamento-siti-web/roi-highway" title="e-max.it: posizionamento siti web" target="_blank" rel="nofollow"><img src="'.JURI::base(true).'/plugins/content/socialsharebuttons/assets/img/primi_sui_motori.png" alt="primi sui motori con e-max" width="12" height="12" style="vertical-align:middle;" /></a></div>';
			}
			else {
				$html .= '<div class="social_share_buttons_credits" style="display:none;"><a href="http://e-max.it/posizionamento-siti-web/roi-highway" title="e-max.it: posizionamento siti web" target="_blank" rel="nofollow"><img src="'.JURI::base(true).'/plugins/content/socialsharebuttons/assets/img/primi_sui_motori.png" alt="primi sui motori con e-max" width="12" height="12" style="vertical-align:middle;" /></a></div>';
			}
		$html .= '<!-- Social Share Buttons | Powered by <a href="http://e-max.it/posizionamento-siti-web/power-traffic" title="promozione siti web" target="_blank" rel="nofollow">e-max.it: seo e posizionamento sui motori</a> -->';
		
        return $html;
    }
    
    private function getArticle(&$article) {
        
        $db = JFactory::getDbo();
        
        $query = "
            SELECT 
                `#__content`.`id`,
                `#__content`.`catid`,
                `#__content`.`alias`,
                `#__content`.`title`,
                `#__categories`.`alias` as category_alias
            FROM
                `#__content`
            INNER JOIN
                `#__categories`
            ON
                `#__content`.`catid`=`#__categories`.`id`
            WHERE
                `#__content`.`introtext` = " . $db->Quote($article->text); 
        
        $db->setQuery($query);
        $result = $db->loadAssoc();
        
        if ($db->getErrorNum() != 0) {
            JError::raiseError(500, "System error!", $db->getErrorMsg());
        }
        
        if(!empty($result)) {
            $result['slug'] = $result['alias'] ? ($result['id'].':'.$result['alias']) : $result['id'];
            $result['catslug'] = $result['category_alias'] ? ($result['catid'].':'.$result['category_alias']) : $result['catid'];
        }
        
        return $result;
    }
      
    private function getTwitter($params, $url, $title){
        
        $html = "";
        if($params->get("twitterButton")) {
            $html = '
            <div class="social-share-buttons-share-tw">
            <a href="http://twitter.com/share" class="twitter-share-button" data-url="' . $url . '" data-text="' . $title . '" data-count="' . $params->get("twitterCounter") . '" data-via="' . $params->get("twitterName") . '" data-lang="' . $params->get("twitterLanguage") . '">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
            </div>
            ';
        }
        
        return $html;
    }
    
    private function getGooglePlusOne($params, $url, $title){
        $type = "";
        $language = "";
        if($params->get("plusType")) {
            $type = 'size="' . $params->get("plusType") . '"';
        }
        
        if($params->get("plusLocale")) {
            $language = " {lang: '" . $params->get("plusLocale") . "'}";
        }
            
        $html = "";
        if($params->get("plusButton")) {
            $html = '
            <div class="social-share-buttons-share-gone">
            <!-- Place this tag in your head or just before your close body tag -->
            <script type="text/javascript" src="http://apis.google.com/js/plusone.js">' . $language . '</script>
            <!-- Place this tag where you want the +1 button to render -->
            <g:plusone ' . $type . ' href="' . $url . '"></g:plusone>
            </div>
            ';
        }
        
        return $html;
    }
    
    private function getFacebookLike($params, $url, $title){
        
        $html = "";
        if($params->get("facebookLikeButton")) {
            
            $faces = (!$params->get("facebookLikeFaces")) ? "false" : "true";
            
            $layout = $params->get("facebookLikeType","button_count");
            if(strcmp("box_count", $layout)==0){
                $height = "80";
            } else {
                $height = "25";
            }
            
            if(!$params->get("facebookLikeRenderer")){ // iframe
                $html = '
                <div class="social-share-buttons-share-fbl">
                <iframe src="http://www.facebook.com/plugins/like.php?';
                
                if($params->get("facebookLikeAppId")) {
                    $html .= 'app_id=' . $params->get("facebookLikeAppId"). '&amp;';
                }
                
                $html .= '
                href=' . rawurlencode($url) . '&amp;';
                if($params->get("facebookLikeSend")){
                    $html .= 'send="true"&amp;';
                }
				else {
                	$html .= 'send="false"&amp;';
				}
                $html .= 'locale=' . $this->fbLocale . '&amp;
                layout=' . $layout . '&amp;
                show_faces=' . $faces . '&amp;
                width=' . $params->get("facebookLikeWidth","450") . '&amp;
                action=' . $params->get("facebookLikeAction",'like') . '&amp;
                colorscheme=' . $params->get("facebookLikeColor",'light') . '&amp;
                height='.$height.'
                ';
                if($params->get("facebookLikeFont")){
                    $html .= "&amp;font=" . $params->get("facebookLikeFont");
                }
                $html .= '
                " scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:' . $params->get("facebookLikeWidth", "450") . 'px; height:' . $height . 'px;" allowTransparency="true"></iframe>
                </div>
                ';
            } else {//XFBML
                $html = '<div class="social-share-buttons-share-fbl">';
                
                if($params->get("facebookRootDiv",1)) {
                    $html .= '<div id="fb-root"></div>';
                }
                
               if($params->get("facebookLoadJsLib", 1)) {
                    $html .= '<script src="http://connect.facebook.net/' . $this->fbLocale . '/all.js#';
                    if($params->get("facebookLikeAppId")){
                        $html .= 'appId=' . $params->get("facebookLikeAppId"). '&amp;'; 
                    }
                    $html .= 'xfbml=1"></script>';
                }
                
                $html .= '
                <fb:like 
                href="' . $url . '" 
                layout="' . $layout . '" 
                show_faces="' . $faces . '" 
                width="' . $params->get("facebookLikeWidth","450") . '" 
                colorscheme="' . $params->get("facebookLikeColor","light") . '"';
				if($params->get("facebookLikeSend")){
                    $html .= 'send="true" ';
                }
				else {
                	$html .= 'send="false" ';
				}
                $html .= 'action="' . $params->get("facebookLikeAction",'like') . '" ';

                if($params->get("facebookLikeFont")){
                    $html .= 'font="' . $params->get("facebookLikeFont") . '"';
                }
                $html .= '></fb:like>
                </div>
                ';
            }
        }
        
        return $html;
    }
    
    private function getDigg($params, $url, $title){
        $title = html_entity_decode($title,ENT_QUOTES, "UTF-8");
        
        $html = "";
        if($params->get("diggButton")) {
            
            $html = '
            <div class="social-share-buttons-share-digg">
            <script type="text/javascript">
(function() {
var s = document.createElement(\'SCRIPT\'), s1 = document.getElementsByTagName(\'SCRIPT\')[0];
s.type = \'text/javascript\';
s.async = true;
s.src = \'http://widgets.digg.com/buttons.js\';
s1.parentNode.insertBefore(s, s1);
})();
</script>
<a 
class="DiggThisButton '.$params->get("diggType","DiggCompact") . '"
href="http://digg.com/submit?url=' . rawurlencode($url) . '&amp;title=' . rawurlencode($title) . '">
</a>
            </div>
            ';
        }
        
        return $html;
    }
    
    private function getStumbpleUpon($params, $url, $title){
        
        $html = "";
        if($params->get("stumbleButton")) {
            
            $html = '
            <div class="social-share-buttons-share-su">
            <script src="http://www.stumbleupon.com/hostedbadge.php?s=' . $params->get("stumbleType",1). '&r=' . rawurlencode($url) . '"></script>
            </div>
            ';
        }
        
        return $html;
    }
    
    private function getTumblr($params, $url, $title){
        
        $html = "";
        if($params->get("tumblrButton")) {
            
			if ($params->get("tumblrType") == '1') { $tumblr_style= 'width:81px; background:url(\'http://platform.tumblr.com/v1/share_1.png\')'; }
			else if ($params->get("tumblrType") == '2') { $tumblr_style= 'width:81px; background:url(\'http://platform.tumblr.com/v1/share_1T.png\')'; }
			else if ($params->get("tumblrType") == '3') { $tumblr_style= 'width:61px; background:url(\'http://platform.tumblr.com/v1/share_2.png\')'; }
			else if ($params->get("tumblrType") == '4') { $tumblr_style= 'width:61px; background:url(\'http://platform.tumblr.com/v1/share_2T.png\')'; }
			else if ($params->get("tumblrType") == '5') { $tumblr_style= 'width:129px; background:url(\'http://platform.tumblr.com/v1/share_3.png\')'; }
			else if ($params->get("tumblrType") == '6') { $tumblr_style= 'width:129px; background:url(\'http://platform.tumblr.com/v1/share_3T.png\')'; }
			else if ($params->get("tumblrType") == '7') { $tumblr_style= 'width:20px; background:url(\'http://platform.tumblr.com/v1/share_4.png\')'; }
			else if ($params->get("tumblrType") == '8') { $tumblr_style= 'width:20px; background:url(\'http://platform.tumblr.com/v1/share_4T.png\')'; }
			
            $html = '
            <div class="social-share-buttons-share-tum">
			<a href="http://www.tumblr.com/share" title="Share on Tumblr" style="display:inline-block; text-indent:-9999px; overflow:hidden; height:20px; ' . $tumblr_style . ' top left no-repeat transparent;">Share on Tumblr</a>
            </div>
            ';
        }
        
        return $html;
    }
    
    private function getReddit($params, $url, $title){
        
        $html = "";
        if($params->get("redditButton")) {
            
			if ($params->get("redditType") == '1') { $reddit_style= '<a href="http://www.reddit.com/submit" onclick="window.location = \'http://www.reddit.com/submit?url=\' + encodeURIComponent(window.location); return false"> <img src="http://www.reddit.com/static/spreddit1.gif" alt="submit to reddit" border="0" /> </a>'; }
			else if ($params->get("redditType") == '2') { $reddit_style= '<a href="http://www.reddit.com/submit" onclick="window.location = \'http://www.reddit.com/submit?url=\' + encodeURIComponent(window.location); return false"> <img src="http://www.reddit.com/static/spreddit7.gif" alt="submit to reddit" border="0" /> </a>'; }
			else if ($params->get("redditType") == '3') { $reddit_style= '<script type="text/javascript" src="http://www.reddit.com/static/button/button1.js"></script>'; }
			else if ($params->get("redditType") == '4') { $reddit_style= '<script type="text/javascript" src="http://www.reddit.com/static/button/button2.js"></script>'; }
			
            $html = '
            <div class="social-share-buttons-share-red">' . $reddit_style . '</div>
            ';
        }
        
        return $html;
    }

    private function getPinterest($params, $url, $title){
        
        $html = "";
		$article_title = JTable::getInstance("content");
		$article_title->load(JRequest::getInt("id"));
		
		$image_pin = "";
		$pinterestDesc = "";
		
		if ($params->get("pinterestStaticImage")) {
			$image_pin = $params->get("pinterestImage");
		}
		else {
			$first_image = "";
			$text = $article_title->get("introtext");
			$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $text, $matches);
			if ($output) {
				$image_pin = JURI::base().$matches[1][0];				
			}
			else {
				$image_pin = $params->get("pinterestImage");
			}
		}
		
		if ($params->get("pinterestStaticDesc")) {
			$pinterestDesc = $params->get("pinterestDesc");
		}
		else {
			$pinterestDesc = $article_title->get("metadesc");
			if ($pinterestDesc == "") {
				$pinterestDesc = $params->get("pinterestDesc");
			}
		}

        if($params->get("pinterestButton")) {
            
            $html = '
            <div class="social-share-buttons-share-pin"><a href="http://pinterest.com/pin/create/button/?url=' . $url . '&media=' . $image_pin. '&description='. $pinterestDesc .'" class="pin-it-button" count-layout="' . $params->get("pinterestType","horizontal"). '"><img border="0" src="http://assets.pinterest.com/images/PinExt.png" title="Pin It" /></a><script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script></div>
            ';
        }
        
        return $html;
    }

    private function getBufferApp($params, $url, $title){
        
        $html = "";
        if($params->get("bufferappButton")) {
            
            $html = '
            <div class="social-share-buttons-share-buf"><a href="http://bufferapp.com/add" class="buffer-add-button" data-count="' . $params->get("bufferappType","vertical"). '">Buffer</a><script type="text/javascript" src="http://static.bufferapp.com/js/button.js"></script></div>
            ';
        }
        
        return $html;
    }

    private function getLinkedIn($params, $url, $title){
        
        $html = "";
        if($params->get("linkedInButton")) {
            
            $html = '
            <div class="social-share-buttons-share-lin">
            <script type="text/javascript" src="http://platform.linkedin.com/in.js"></script><script type="in/share" data-url="' . $url . '" data-counter="' . $params->get("linkedInType",'right'). '"></script>
            </div>
            ';
        }
        
        return $html;
    }
    
    private function getBuzz($params, $url, $title){
        
        $html = "";
        if($params->get("buzzButton")) {
            
            $html = '
            <div class="social-share-buttons-share-buzz">
            <a title="Post to Google Buzz" class="google-buzz-button" 
            href="http://www.google.com/buzz/post" 
            data-button-style="' . $params->get("buzzType","small-count"). '" 
            data-url="' . $url . '"
            data-locale="' . $this->params->get("buzzLocale", "en") . '"></a>
<script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script>
            </div>
            ';
        }
        
        return $html;
    }
    
    private function getReTweetMeMe($params, $url, $title){
        
        $html = "";
        if($params->get("retweetmeButton")) {
            
            $html = '
            <div class="social-share-buttons-share-retweetme">
            <script type="text/javascript">
tweetmeme_url = "' . $url . '";
tweetmeme_style = "' . $params->get("retweetmeType") . '";
tweetmeme_source = "' . $params->get("twitterName") . '";
</script>
<script type="text/javascript" src="http://tweetmeme.com/i/scripts/button.js"></script>
            </div>';
        }
        
        return $html;
    }
        
    private function getFacebookShareMe($params, $url, $title){
            
            $html = "";
            if($params->get("facebookShareMeButton")) {
                
                $html = '
                <div class="social-share-buttons-share-fbsh">
                <script>var fbShare = {
    url: "' . $url . '",
    title: "' . $title . '",
    size: "' . $params->get("facebookShareMeType","large"). '",
    badge_text: "' . $params->get("facebookShareMeBadgeText","C0C0C0"). '",
    badge_color: "' . $params->get("facebookShareMeBadge","CC00FF"). '",
    google_analytics: "false"
    }</script>
    <script src="http://widgets.fbshare.me/files/fbshare.js"></script>
                </div>
                ';
            }
            
            return $html;
        }
        
}