<?php
/**
 * ReDJ Community plugin for Joomla
 *
 * @author selfget.com (info@selfget.com)
 * @package ReDJ
 * @copyright Copyright 2009 - 2016
 * @license GNU Public License
 * @link http://www.selfget.com
 * @version 1.7.10
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldPage404 extends JFormField
{
  protected $plugin = 'redj';
  protected $type = 'Page404';
  protected $currentVersion = '1.7.10';

  protected function getInput()
  {
    $return = '<select id="' . $this->id . '" name="' . $this->name . '">';
    $return .= '<option value="" >' . JText::_('PLG_SYS_REDJ_NO_ITEM_SELECTED') . '</option>';
    $db = JFactory::getDbo();
    $db->setQuery("SELECT `id`, `title`, `language` FROM #__redj_pages404");
    $pages = $db->loadAssocList();
    if (is_array($pages)) {
      foreach ($pages as $page)
      {
        $isselected = ($this->value == $page['id']) ? 'selected="selected"' : '';
        $return .= '<option value="' . $page['id'] . '" ' . $isselected . '>' . $page['title'] . ' (' . $page['language'] . ')</option>';
      }
    }
   $return .= '</select>';
    return $return;
  }
}