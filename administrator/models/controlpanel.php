<?php
/**
 * @version     1
 * @package     com_wbty_users
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com
 */

// No direct access.
defined('_JEXEC') or die;

jimport('legacy.model.legacy');

// check for Joomla 2.5
if (!class_exists('JViewLegacy')) {
	jimport('joomla.application.component.model');
	class JModelLegacy extends JModel {}
}

/**
 * Wbty_users model.
 */
class Wbty_usersModelControlpanel extends JModelLegacy
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'com_wbty_users';
	

}