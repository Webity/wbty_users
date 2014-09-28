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

jimport('wbty_components.models.wbtymodeladmin');

/**
 * Wbty_users model.
 */
class Wbty_usersModelorganization extends WbtyModelAdmin
{
	protected $text_prefix = 'com_wbty_users';
	protected $com_name = 'wbty_users';
	protected $list_name = 'organizations';
	
	public function getTable($type = 'organizations', $prefix = 'Wbty_usersTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true, $control='jform', $key=0)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
		// Get the form.
		$form = $this->loadForm('com_wbty_users.organization.'.$control.'.'.$key, 'organization', array('control' => $control, 'load_data' => $loadData, 'key'=>$key));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	public function publish($ids, $state) {
		foreach ($ids as $id) {
			if ($state != 1) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('id')
					->from('#__wbty_users_users')
					->where('group_id = '.(int)$id);

				$users = $db->setQuery($query)->loadColumn();

				$db->setQuery('UPDATE `#__users` SET block=1 WHERE id IN ('.implode(',', $users).')')->execute();
			} else {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('id')
					->from('#__wbty_users_users')
					->where('group_id = '.(int)$id)
					->where('state = 1');

				$users = $db->setQuery($query)->loadColumn();

				$db->setQuery('UPDATE `#__users` SET block=0 WHERE id IN ('.implode(',', $users).')')->execute();
			}
		}

		return parent::publish($ids, $state);
	}
	
	public function getItems($parent_id, $parent_key) {
		$query = $this->_db->getQuery(true);
		
		$query->select('id, state');
		$query->from($this->getTable()->getTableName());
		$query->where($parent_key . '=' . (int)$parent_id);
		$query->where('base_id = 0');
		$query->order('state DESC');
		
		$data = $this->_db->setQuery($query)->loadObjectList();

		if (count($data)) {
			$this->getState();
			foreach ($data as $key=>$d) {
				$this->data = null;
				$this->setState($this->getName() . '.id', $d->id);

				$return[$d->id] = $this->getForm(array(), true, 'jform', $d->id);
			}
		}
		
		return $return;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		if (isset($this->data) && $this->data) {
			return $this->data;
		}
		
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_wbty_users.edit.organization.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	public function getItem($pk = null)
	{
		if ($item['organization'] = parent::getItem($pk)) {

			//Do any procesing on fields here if needed
			
			
		}

		return $item;
	}
	
	protected function prepareTable(&$table)
	{
		$user =& JFactory::getUser();

		// each organization should have a base usergroup
		if (!$table->group_id) {
			$usergroups = JTable::getInstance('usergroup');

			$data = array();
			$data['title'] = $table->name;
			$data['parent_id'] = 2;

			$usergroups->bind($data);
			if ($usergroups->store()) {
				$db = $usergroups->getDbo();
				$query = $db->getQuery(true);

				$query->select('id')
					->from('#__usergroups')
					->where('title = \''.$table->name.'\'');

				$table->group_id = $db->setQuery($query)->loadResult();

				$this->new_org = true;
			}
		} else {
			$usergroups = JTable::getInstance('usergroup');
			$usergroups->load($table->group_id);

			if ($table->name != $usergroups->title) {
				$usergroups->title = $table->name;
				$usergroups->store();
			}
		}
		

		parent::prepareTable($table);
	}
	
	public function save($data) {
		$app = JFactory::getApplication();
		if (!$data['admin_org']) {
			$data['admin_org'] = 0;
		}

		if (!$this->checkUnique($data)) {
			return false;
		}

		$oldtable = $this->getTable();
		$oldtable->load($data['id']);

		if (!parent::save($data)) {
			return false;
		}

		JPluginHelper::importPlugin( 'wbty_users' );
		$dispatcher = JEventDispatcher::getInstance();
		$table = $this->getTable();
		$table->load($this->table_id);
		if ($table->id) {
			if ($this->new_org) {
				$results = $dispatcher->trigger( 'onNewWbtyUserOrg', array( &$table ) );
			}
			$results = $dispatcher->trigger( 'onChangeWbtyUserOrg', array( &$table, &$oldtable ) );
		}

		$db = JFactory::getDbo();

		if ($table->state != 1) {
			$query = $db->getQuery(true);

			$query->select('id')
				->from('#__wbty_users_users')
				->where('group_id = '.(int)$table->id);

			$users = $db->setQuery($query)->loadColumn();

			if ($users) {
				$db->setQuery('UPDATE `#__users` SET block=1 WHERE id IN ('.implode(',', $users).')')->execute();
			}
		} else {
			$query = $db->getQuery(true);

			$query->select('id')
				->from('#__wbty_users_users')
				->where('group_id = '.(int)$table->id)
				->where('state = 1');

			$users = $db->setQuery($query)->loadColumn();

			if ($users) {
				$db->setQuery('UPDATE `#__users` SET block=0 WHERE id IN ('.implode(',', $users).')')->execute();
			}
		}

		if ($table->admin_org && $group_id = $app->getParams('com_wbty_users')->get('org_usergroup', 0)) {
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__wbty_users_users')
				->where('group_id = '.(int)$table->id)
				->where('state = 1');

			$users = $db->setQuery($query)->loadColumn();

			foreach($users as $user) {
				JUserHelper::addUserToGroup($user, $group_id);
			}
		}

		// manage link
		
		//$component_asset = JRequest::getVar('component_asset', array(), 'post', 'ARRAY');
		//$this->save_sub('component_asset', $component_asset, 'component_id');
		
		return $this->table_id;
	}

	// function to check that the new name that we are saving is unique
	protected function checkUnique($data) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__wbty_users_organizations')
			->where('name='.$db->quote($data['name']))
			->where('base_id=0')
			->where('id != '.(int)$data['id']);

		$id = $db->setQuery($query)->loadResult();

		return $id ? false : true;
	}

}