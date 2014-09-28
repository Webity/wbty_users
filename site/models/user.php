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
class Wbty_usersModeluser extends WbtyModelAdmin
{
	protected $text_prefix = 'com_wbty_users';
	protected $com_name = 'wbty_users';
	protected $list_name = 'users';
	
	public function getTable($type = 'users', $prefix = 'Wbty_usersTable', $config = array())
	{
		// hack since Joomla has a users table...
		require_once(JPATH_BASE . '/administrator/components/com_wbty_users/tables/users.php');
		return JTable::getInstance($type, $prefix, $config);
	}

	public function publish($ids, $state) {
		$ids = (array)$ids;
		foreach ($ids as $id) {
			$user = JUser::getInstance((int)$id);
			if ($state == 1) {
				$user->block = 0;
			} else {
				$user->block = 1;
			}
			if ($user->save()) {
				JFactory::getApplication()->enqueueMessage('User successfully deleted');
			}
		}
		return true;
	}

	public function getForm($data = array(), $loadData = true, $control='jform', $key=0)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
		// Get the form.
		$form = $this->loadForm('com_wbty_users.user.'.$control.'.'.$key, 'user', array('control' => $control, 'load_data' => $loadData, 'key'=>$key));

		if (empty($form)) {
			return false;
		}

		//Do any procesing on fields here if needed
		// JPluginHelper::importPlugin( 'user' );
		// $dispatcher = JEventDispatcher::getInstance();
		// $results = $dispatcher->trigger( 'onContentPrepareForm', array( &$form, array() ) );

		return $form;
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
		//$data = JFactory::getApplication()->getUserState('com_wbty_users.edit.user.data', array());

		if (empty($data)) {
			$data = $this->getItem(null, true);
		}

		return $data;
	}

	public function getItem($pk = null, $form = false)
	{
		if ($pk && is_int($pk)) {
			$item = JFactory::getUser($pk);
		} elseif ($pk = (int)$this->getState('user.id')) {
			$item = JFactory::getUser($pk);
		} else {
			$item = JFactory::getUser();
		}

		//cache buster!
		unset($item->wbty_users);

		if ($item->id) {
			//Do any procesing on fields here if needed
			JPluginHelper::importPlugin( 'user' );
			JPluginHelper::importPlugin( 'wbty_users' );
			$dispatcher = JEventDispatcher::getInstance();
			$results = $dispatcher->trigger( 'onContentPrepareData', array( 'com_users.profile', &$item ) );
			$results2 = $dispatcher->trigger( 'onLoadUser', array( 'com_wbty_users.user.default', &$item ) );
		}

		$item->email1 = $item->email2 = $item->email;
		if ($item->wbty_users) {
			foreach ($item->wbty_users as $key => $value) {
				$item->$key = $value;
			}
		}


		if ($form) {
			return array('user'=>$item);
		} else {
			return $item;
		}
	}
	
	protected function prepareTable(&$table)
	{
		$user =& JFactory::getUser();

		

		parent::prepareTable($table);
	}
	
	public function save($data) {
		if (!$data['id']) {
			if (!$this->createJomUser($data)) {
				return false;
			}
		}
		
		if (!parent::save($data)) {
			return false;
		}
		
		// manage link

		$old_user_groups = JUserHelper::getUserGroups($this->table_id);
		foreach ($old_user_groups as $group) {
			JUserHelper::removeUserFromGroup($this->table_id, $group);
		}

		JUserHelper::addUserToGroup($this->table_id, 2);
		
		$jform = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
		if ($jform['assets']) {
			foreach ($jform['assets'] as $set) {
				foreach ($set as $group) {
					JUserHelper::addUserToGroup($this->table_id, $group);
					echo $group . '<br>';
				}
			}
		}

		$table = $this->getTable();
		$table->load($this->table_id);

		$db = JFactory::getDbo();

		$org = $db->setQuery('SELECT group_id, admin_org FROM `#__wbty_users_organizations` WHERE id='.(int)$table->group_id)->loadObject();
		if ($group_id = $org->group_id) {
			JUserHelper::addUserToGroup($this->table_id, $group_id);
		}
		if ($org->admin_org) {
			if ($param_group = $app->getParams('com_wbty_users')->get('org_usergroup', 0)) {
				JUserHelper::addUserToGroup($this->table_id, $param_group);
			}
		}

		if ($table->state != 1) {
			$db = JFactory::getDbo();
			$db->setQuery('UPDATE `#__users` SET block=1 WHERE id = '.$this->table_id)->execute();
		} else {
			$db = JFactory::getDbo();
			$db->setQuery('UPDATE `#__users` SET block=0 WHERE id = '.$this->table_id)->execute();
		}
		
		return $this->table_id;
	}

	protected function createJomUser(&$data) {
		$user = JFactory::getUser($data['email']);
		if (!$user) {
			// create user and insert base record into advantage users page
			jimport( 'joomla.user.helper' );
		
			$app =& JFactory::getApplication();
			// because we have a class named user as well, we need to grab the user class on our own
			require_once (JPATH_ROOT . '/libraries/joomla/table/user.php');
				
			$code = JUserHelper::genRandomPassword();
			
			$user_data = array(
				'username' => $data['email'],
				'email' => $data['email'],
				'password' => $code,
				'password2' => $code, 
				'name' => $data['first_name'] . ' ' . $data['last_name']
				);
			
			$user  = new JUser;
			$user->bind($user_data);

			// turns out joomla sucks at saving passwords...
			$salt = JUserHelper::genRandomPassword(32);
			$crypted = JUserHelper::getCryptedPassword($code, $salt, 'md5-hex');
			$password = $crypted . ':' . $salt;

			$user->password = $password;
			$user->activation = '';
			$user->password_clear = $code;

			$jom_users_id = $user->save();
			
			if($jom_users_id) {
				$jom_users_id = $user->id;

				$db = JFactory::getDbo();

				$result = $db->setQuery('SELECT id FROM `#__wbty_users_users` WHERE id='.(int)$jom_users_id)->loadResult();
				if (!$result) {
					$base_user = new stdClass();
					$base_user->id = $jom_users_id;
					$db->insertObject('#__wbty_users_users', $base_user);
				}
				
				$data['id'] = $jom_users_id;
				
				$mailer = JFactory::getMailer();
				// Set a sender
				$config = JFactory::getConfig();
				$sender = array( 
					$config->get( 'mailfrom' ),
					$config->get( 'fromname' ) );
				 
				$mailer->setSender($sender);
				
				// Recipient
				$mailer->addRecipient($user->email);
				
				$mailer->setSubject('Your '.$config->get( 'sitename' ).' User Details!');
				
				// Set email
				$body	= '<h3>Hello '.$user->name.',</h3>';
				$body	.= '<p>You have been added as a user for '.$config->get( 'sitename' ).'.</p>';
				$body	.= '<p>This email contains your username and password to log into <a href="'.JUri::root().'">'.JUri::root().'</a></p>';
				$body	.= '<p>Username: '.$user->email.'<br />';
				$body	.= 'Password: '.$code.'</p>';
				$body	.= '<p>Please do not respond to this message as it is automatically generated and is for information purposes only.</p>';
				$mailer->isHTML(true);
				$mailer->Encoding = 'base64';
				$mailer->setBody($body);
				// Optionally add embedded image
				//$mailer->AddEmbeddedImage( JPATH_COMPONENT.'/'.'assets/logo128.jpg', 'logo_id', 'logo.jpg', 'base64', 'image/jpeg' );
				
				try {
					$send = $mailer->Send();
				} catch(RuntimeException $e) {
				}

echo $body;
exit();
				if ( $send !== true ) {
					$app->enqueueMessage( 'Error sending email' );
					return false;
				} else {
					$app->enqueueMessage( 'Mail sent' );
					return true;
				}
			} else {
				return false;
			}
		} else {
			$db = JFactory::getDbo();

			$result = $db->setQuery('SELECT id FROM `#__wbty_users_users` WHERE id='.(int)$user->id)->loadResult();
			if (!$result) {
				$base_user = new stdClass();
				$base_user->id = $user->id;
				$db->insertObject('#__wbty_users_users', $base_user);
			}
			$data['id'] = $user->id;
		}
		return true;
	}

}