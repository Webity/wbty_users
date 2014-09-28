<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.utilities.date');

/**
 * An example custom profile plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	User.profile
 * @version		1.6
 */
class plgSystemWbty_users extends JPlugin {
	
	var $params = null;

	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		$this->params = JComponentHelper::getParams('com_wbty_users');

		// option to hijack all user views and replace them with wbty_users views, on the site only at this point
		if (JFactory::getApplication()->isSite() && $this->params->get('hijack_user_views')) {
			$input = JFactory::getApplication()->input;

			if ($input->get('option') == 'com_users') {
				$view = $input->get('view');
				$layout = $input->get('layout');
				$id = $input->get('id');

				JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_wbty_users&view='.$view.'&layout='.$layout.'&id='.$id));
			}
		}

		if ($this->params->get('split_name', 0)) {
			$this->merge_name();
		}

		if ($this->params->get('email_as_username', 0)) {
			$this->set_username();
		}

		parent::__construct($subject, $config);
		$this->loadLanguage();
		JFormHelper::addFieldPath(dirname(__FILE__) . '/fields');
	}

	function merge_name() {
		$data = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
		if (isset($data['first_name']) && (isset($data['last_name'])))
		{
			$data['name'] = $data['first_name'] . ' ' . $data['last_name'];
			$data['wbty_users']['first_name'] = $data['first_name'];
			$data['wbty_users']['last_name'] = $data['last_name'];
			JFactory::getApplication()->input->post->set('jform', $data);
			JFactory::getApplication()->input->set('jform', $data);
		}
		return true;
	}

	function set_username() {
		$data = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
		if (isset($data['email1']))
		{
			$data['username'] = $data['email1'];
			JFactory::getApplication()->input->post->set('jform', $data);
			JFactory::getApplication()->input->set('jform', $data);
		}
		return true;
	}

}
