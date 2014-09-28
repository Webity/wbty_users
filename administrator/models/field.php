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
class Wbty_usersModelfield extends WbtyModelAdmin
{
	protected $text_prefix = 'com_wbty_users';
	protected $com_name = 'wbty_users';
	protected $list_name = 'fields';

	public function getTable($type = 'fields', $prefix = 'Wbty_usersTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true, $control='jform', $key=0)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		
		// Get the form.
		$form = $this->loadForm('com_wbty_users.field.'.$control.'.'.$key, 'field', array('control' => $control, 'load_data' => $loadData, 'key'=>$key));
		if (empty($form)) {
			return false;
		}

		return $form;
	}
	
	public function getItems($parent_id, $parent_key) {
		$query = $this->_db->getQuery(true);
		
		$query->select('id, state');
		$query->from($this->getTable()->getTableName());
		$query->where($parent_key . '=' . (int)$parent_id);
		$query->where($parent_key . '!= 0');
		$query->where('base_id = 0');
		$query->order('state DESC, ordering ASC');
		
		$data = $this->_db->setQuery($query)->loadObjectList();
		if (count($data)) {
			$this->getState();
			$key=0;
			foreach ($data as $key=>$d) {
				$this->data = null;
				$this->setState($this->getName() . '.id', $d->id);
				$return[$d->id] = $this->getForm(array(), true, 'jform', $d->id);
			}
		}
		
		return $return;
	}
	
	public function getFieldForm($form_name = false, $data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		
		if (!$form_name) {
			return false;
		}

		// Get the form.
		$form = $this->loadForm('com_wbty_users.field', strtolower($form_name), array('control' => 'jfields', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		if ($this->data) {
			return $this->data;
		}
		
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_wbty_users.edit.field.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item['field'] = parent::getItem($pk)) {

			//Do any procesing on fields here if needed
			
				$db =& JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->from('#__wbty_users_fields as a');
				
				$query->select('field_types.value as field_types_value');
				$query->join('LEFT', '#__wbty_users_field_types as field_types ON a.field_type=field_types.id');
				$query->where('a.id='.(int)$item->id);
				$items = $db->setQuery($query)->loadObject();
				if($items) {
					foreach($items as $key=>$value) {
						if ($value && $key) {
							$item->$key = $value;
						}
					}
				}
			
		}

		return $item;
	}

	protected function prepareTable(&$table)
	{
		$user =& JFactory::getUser();
		
		
		$jform = JRequest::getVar('jform'); // load all submitted data
		if (!isset($jform['field']['list_view'])) { // likewise for other checkboxes
			$table->list_view = 0;
		}
		if (!isset($jform['field']['registration_view'])) { // likewise for other checkboxes
			$table->registration_view = 0;
		}

		parent::prepareTable($table);
	}
	
	function save($data) {
		if (!parent::save($data)) {
			return false;
		}

		$fields = JRequest::getVar('jfields');
		$db = JFactory::getDbo();
		$table = $this->getTable('users');
		$name = $fields['name'];

		// if new field 
		if (!$data['id']) {
			if (!isset($table->$name)) {
				$query = 'ALTER TABLE `'.$table->getTableName().'` ADD `'.$name.'` VARCHAR(255) NOT NULL';
			}
		} else {
			$query = $db->getQuery(true);
			$query->select('value')
				->from('#__wbty_users_field_options')
				->where('field_id='.(int)$this->table_id)
				->where('name=\'name\'');

			$old_name = $db->setQuery($query)->loadResult();
			if ($old_name) {
				$query = 'ALTER TABLE `'.$table->getTableName().'` CHANGE `'.$old_name.'` `'.$name.'` VARCHAR(255) NOT NULL';
			} else {
				$query = 'ALTER TABLE `'.$table->getTableName().'` ADD `'.$name.'` VARCHAR(255) NOT NULL';
			}
		}
		
		//execute alter query
		$db->setQuery($query)->query();
		
		// manage link
		$query = "DELETE FROM #__wbty_users_field_options WHERE field_id=".$this->_db->quote($this->table_id);
		$this->_db->setQuery($query);
		$this->_db->query();
		
		foreach($fields as $name=>$value) {
			$query = "INSERT INTO #__wbty_users_field_options SET name=".$this->_db->quote($name).", value=".$this->_db->quote($value).", field_id=".$this->_db->quote($this->table_id);
			$this->_db->setQuery($query);
			$this->_db->query();
		}
		
		return $this->table_id;
	}
}