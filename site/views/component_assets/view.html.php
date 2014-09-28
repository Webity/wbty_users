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
 * View class for a list of Wbty_users.
 */
class Wbty_usersViewcomponent_assets extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
    protected $params;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
        $app                = JFactory::getApplication();
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
        $this->params       = $app->getParams('com_wbty_users');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

        $this->_prepareDocument();
        $this->addToolbar();
		
		parent::display($tpl);
	}


	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('com_wbty_users_DEFAULT_PAGE_TITLE'));
		}
		$title = $this->params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}    

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/wbty_users.php';

		$state	= $this->get('State');
		$canDo	= Wbty_usersHelper::getActions($state->get('filter.category_id'));

		//load the JToolBar library and create a toolbar
		jimport('joomla.html.toolbar');
		$bar = new JToolBar( 'toolbar' );

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/component_asset';
	
		if (file_exists($formPath)) {
            if ($canDo->get('core.create')) {
				$bar->appendButton( 'Standard', 'new', 'New', 'component_asset.add', false );
		    }
		    if ($canDo->get('core.edit') && isset($this->items[0])) {
				$bar->appendButton( 'Standard', 'edit', 'Edit', 'component_asset.edit', false );
				
				if (isset($this->items[0]->checked_out)) {
					$bar->appendButton( 'Standard', 'checkin', 'Check In', 'component_assets.checkin', false );
				}
		    }
			
			if ($canDo->get('core.edit.state') && isset($this->items[0]->state)) {
				if ($this->state->get('filter.state') != -2) { 
					$bar->appendButton( 'Standard', 'trash', 'Trash', 'component_assets.trash', false );
				} else {
					$bar->appendButton( 'Standard', 'publish', 'Publish', 'component_assets.publish', false );
				}
			}
			
        }
	
		if ($canDo->get('core.admin')) {
			//JToolBarHelper::preferences('com_wbty_users');
		}
		
		return $bar->render();
	}
    	
}
