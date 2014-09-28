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
class plgUserWbty_users extends JPlugin {
	
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

		parent::__construct($subject, $config);
		$this->loadLanguage();
		JFormHelper::addFieldPath(dirname(__FILE__) . '/fields');
	}

	/**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 * @param	object
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->wbty_users) and $userId > 0)
			{
				// Load the profile data from the database.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('*')
					->from('#__wbty_users_users')
					->where('id = '.(int)$userId);

				$data->wbty_users = $db->setQuery($query)->loadObject();

				$data->first_name = $data->wbty_users->first_name;
				$data->last_name = $data->wbty_users->last_name;

				// Check for a database error.
				if ($db->getErrorNum())
				{
					$this->_subject->setError($db->getErrorMsg());
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		if ($this->form) {
			return $this->form;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration', 'com_wbty_users.user.jform', 'com_wbty_users.user.jform.0')))
		{
			return true;
		}
		
		$params = JComponentHelper::getParams('com_wbty_users');

		if ($params->get('split_name', 0)) {
			if (JFactory::getApplication()->isAdmin()) {
				$old_fields = $form->getFieldset('core');
				foreach ($old_fields as $field) {
					$form->removeField($field->__get('fieldname'));
				}

				$newxml = file_get_contents(dirname(__FILE__) . '/forms/admin.xml');
				$form->load($newxml);
			} elseif ($name == 'com_users.profile') {
				$old_fields = $form->getFieldset('core');
				foreach ($old_fields as $field) {
					$form->removeField($field->__get('fieldname'));
				}

				$newxml = file_get_contents(dirname(__FILE__) . '/forms/core.xml');
				$form->load($newxml);
			} else {
				$old_fields = $form->getFieldset('default');
				foreach ($old_fields as $field) {
					$form->removeField($field->__get('fieldname'));
				}

				$newxml = file_get_contents(dirname(__FILE__) . '/forms/default.xml');
				$form->load($newxml);
			}
		}

		if ($params->get('email_as_username', 0)) {
			// remove except on save
			if (JFactory::getApplication()->input->get('task', '') != 'register') {
				$form->removeField('username');
			}
		}

		// remove captcha and confirm fields on admin side
		if (JFactory::getApplication()->isAdmin()) {
			$form->removeField('captcha');
			$form->removeField('email2');
		}
		
		$this->buildAssetTree($form, $data);

		$this->buildUserForm($form, $data);

		$this->form = $form;

		return true;
	}

	function buildAssetTree($form, $data) {

		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$query->select('*')
			->from('#__wbty_users_component_assets')
			->where('state=1');
		
		$assets = $db->setQuery($query)->loadObjectList();
		
		if (!$assets) {
			return true;
		}
		
		jimport('joomla.database.table.usergroup');
		$table = JTable::getInstance('Usergroup', 'JTable', array());
		
		foreach ($assets as $key => $asset) {
			if (!$asset->base_user_group) {
				unset($assets[$key]);
			}
			
			$query->clear()
				->select('lft, rgt')
				->from('#__usergroups')
				->where('id='.(int)$asset->base_user_group);
			
			$base_group = $db->setQuery($query, 0, 1)->loadObject();
			
			$query->clear()
				->select('*')
				->from('#__usergroups')
				->where('lft>='.(int)$base_group->lft)
				->where('rgt<='.(int)$base_group->rgt)
				->order('lft ASC');
			
			$assets[$key]->groups = $db->setQuery($query)->loadObjectList();
			
			if (!$assets[$key]->groups) {
				unset($assets[$key]);
			}
		}
		
		if (!$assets) {
			return true;
		}

		$user_groups = JUserHelper::getUserGroups(JRequest::getVar('id'));
		$bind_array = array();
		foreach ($user_groups as $ugroup) {
			if (in_array($ugroup, array(2,6,7))) {
				$bind_array['assets']['groups0'][$ugroup] = $ugroup;
			}
		}

		
		$xml = '<?xml version="1.0" encoding="utf-8"?>
<form>
	<fields name="assets">
		<fieldset name="wbty_users">';
		foreach ($assets as $asset) {
			if (! $name = $asset->name) {
				$name = $db->setQuery('SELECT title FROM #__assets WHERE id='.(int)$asset->asset_id)->loadResult();
			}
			$xml .= '<field name="'.strtolower(str_replace(' ', '_', $name)).'" type="spacer" label="'.$name.'" />';
			
			$xml .= '<field type="checkboxes" name="groups'.$asset->id.'" label="Add permission for this asset">';
			foreach ($asset->groups as $group) {
				$xml .= '<option value="'.$group->id.'">'.htmlentities($group->title).'</option>';
				foreach ($user_groups as $ugroup) {
					if ($ugroup == $group->id) {
						$bind_array['assets']['groups'.$asset->id][$group->id] = $group->id;
						break;
					}
				}
			}
			$xml .= '</field>';
		}

			// add base classes for registered and admin
			$xml .= '<field name="base_permissions" type="spacer" label="Base Permissions" />';
			$xml .= '<field type="checkboxes" name="groups0" label="Add permissions">';
			$xml .= '<option value="2">Registered</option>';
			$xml .= '<option value="6">Manager</option>';
			$xml .= '<option value="7">Administrator</option>';
			$xml .= '</field>';


		$xml .= '	</fieldset>
	</fields>
</form>';

		$form->load($xml);

		$form->bind($bind_array);
		
		return true;
	}

	function buildUserForm($form, $data) {

		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$query->select('*')
			->from('#__wbty_users_fields')
			->where('state=1')
			->where('base_id = 0');

		$name = $form->getName();
		if ($name == 'com_users.registration') {
			$query->where('registration_view = 1');
		}
		
		$fields = $db->setQuery($query)->loadObjectList();

		foreach ($fields as $key=>$field) {
			$query->clear()
				->select('name, value')
				->from('#__wbty_users_field_options')
				->where('field_id = '.(int)$field->id);

			$fields[$key]->options = $db->setQuery($query)->loadAssocList('name','value');
		}

		$xml = '<?xml version="1.0" encoding="utf-8"?>
<form>
	<fields name="wbty_users">
		<fieldset name="wbty_user_fields" addfieldpath="/libraries/wbty_components/model/fields">';
		foreach ($fields as $field) {
			$xml .= $this->buildField($field);
		}
		$xml .= '	</fieldset>
	</fields>
</form>';

		$form->load($xml);
	}

	function buildField($field) {
		if (!$field) {return '';}
		
		$return = '<field type="'.strtolower(str_replace('J_','', $field->field_type)).'" ';
		foreach ($field->options as $key=>$value) {
			if ($key=='values') {
				$values = $value;
				continue;
			}
			$return .= $key . '="' . $value . '" ';
		}
		if ($field->default_col) {
			$return .= 'class="default_col" ';
		}
		if ($values) {
			$return .= '>
			';
			$items = explode('|', $values);
			foreach ($items as $i) {
				$return .= '
				<option value="'.$i.'" class="'.strtolower(str_replace(' ', '-', $i)).'">'.$i.'</option>';
			}
			$return .= '
			</field>';
		} elseif ($include_blank) {
			$return .= '>
				<option value=""></option>
			</field>
			';
		}else {
			$return .= '/>';
		}
		return $return;
	}

	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && ((isset($data['wbty_users']) && (count($data['wbty_users']))) || isset($data['first_name'])))
		{
			try
			{
				$data = $this->processForm($data);

				$db = JFactory::getDbo();

				require_once(JPATH_BASE . '/components/com_wbty_users/models/user.php');
				$model = JModelLegacy::getInstance('User', 'Wbty_usersModel');

				$query = $db->getQuery(true);

				$query->select('id')->from('#__wbty_users_users')->where('id='.(int)$userId);

				$row = $db->setQuery($query)->loadResult();

				if (!$row) {
					$d = new stdClass();
					$d->id = $userId;
					$d->created_by = $userId;
					$d->created_time = JFactory::getDate()->toSql();
					$db->insertObject('#__wbty_users_users', $d);
				}

				$data['wbty_users']['id'] = $userId;

				if (isset($data['first_name']) && (isset($data['last_name']))) {
					$data['wbty_users']['first_name'] = $data['first_name'];
					$data['wbty_users']['last_name'] = $data['last_name'];
				}

				$saved = $model->save($data['wbty_users']);

				if (!$saved)
				{
					return false;
				}

			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		// if ($isNew) {
		// 	if (JFactory::getApplication()->isSite() && !JFactory::getUser()->id) {
		// 		$this->processLogin($data);
		// 		JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_wbty_users&view=user&layout=step2'));
		// 	}
		// }

		return true;
	}

	public function processLogin ($data) {
		$app =& JFactory::getApplication();
		
		$credentials = array(
					'username'=>$data['username'],
					'password'=>$data['password1']
					);
		$options = array('remember'=>false);
		return $app->login($credentials, $options);
		
	}

	function processForm($data) {
		$files = JFactory::getApplication()->input->files->get('jform', array(), 'ARRAY');

		if (!$this->form) {
			return false;
		}
		
		$fields = $this->form->getFieldset('wbty_user_fields');

		foreach ($fields as $field) {
			$type = $field->__get('type');
			switch (strtolower($type)) {
				case 'file':
					$name = $field->__get('fieldname');
					$file = $files['wbty_users'][$name];
					if ($file && $file['tmp_name']) {
						$filename = JFile::makeSafe($file['name']);
						$src = $file['tmp_name'];
						if (!file_exists(JPATH_BASE . '/images/wbty_users/' . $data['id'] . '/')) {
							JFolder::create(JPATH_BASE . '/images/wbty_users/' . $data['id'] . '/');
						}
						$dest = JPATH_BASE . '/images/wbty_users/' . $data['id'] . '/' . $filename;

						if ( JFile::upload($src, $dest) ) {
					       $data['wbty_users'][$name] = str_replace(JPATH_BASE . '/', '', $dest);
					    }
					}
					break;
				case 'wbtycheckboxes':
				case 'checkboxes':
					$name = $field->__get('fieldname');
					$data['wbty_users'][$name] = implode(',', $data['wbty_users'][$name]);
					break;
				default:
					// should always be no processing here
					break;
			}
		}
		
		return $data;
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__wbty_users_users WHERE id = '.$userId
				);

				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
}
