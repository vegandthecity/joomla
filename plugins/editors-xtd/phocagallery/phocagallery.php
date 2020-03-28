<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgButtonPhocaGallery extends JPlugin
{
	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	function onDisplay($name, $asset, $author) {
		
		$app = JFactory::getApplication();

		$document = & JFactory::getDocument();
		$template = $app->getTemplate();
		
		$enableFrontend = $this->params->get('enable_frontend', 0);
		
		if ($template != 'beez_20') {
			JHTML::stylesheet( 'plugins/editors-xtd/phocagallery/assets/css/phocagallery.css' );
		}
		
		$link = 'index.php?option=com_phocagallery&amp;view=phocagallerylinks&amp;tmpl=component&amp;e_name='.$name;

		JHTML::_('behavior.modal');

		$button = new JObject();
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('PLG_EDITORS-XTD_PHOCAGALLERY_IMAGE'));
		$button->set('name', 'phocagallery');
		$button->set('options', "{handler: 'iframe', size: {x: 600, y: 400}}");
		
		if ($enableFrontend == 0) {
			if (!$app->isAdmin()) {
				$button = null;
			}
		}
	
		return $button;
	}
}