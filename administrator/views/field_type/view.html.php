<?php
/**
 * @version     1
 * @package     com_wbty_users
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('legacy.view.legacy');

// check for Joomla 2.5
if (!class_exists('JViewLegacy')) {
	jimport('joomla.application.component.view');
	class JViewLegacy extends JView {}
}

/**
 * View to edit
 */
class Wbty_usersViewfield_type extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		
		if (isset($this->item['field_type']->checked_out) && $this->item['field_type']->checked_out != 0 && $this->item['field_type']->checked_out != JFactory::getUser()->id) {
			$app->enqueueMessage('Item is currently checkout to '.JFactory::getUser($this->item['field_type']->checked_out)->name.' and can not be edited at this time.');
			$app->redirect('index.php?option=com_wbty_users&view=field_types');
			exit();
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if (JFactory::getApplication()->input->get('layout')=='edit') {
			$this->addToolbar();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		JToolBarHelper::title(JText::_('COM_WBTY_USERS_TITLE_FIELD_TYPE'), 'field_type.png');

		JToolBarHelper::cancel('field_type.cancel', 'Back to field_types List');

	}
}
