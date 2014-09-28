<?php
/**
 * @version		$Id: users.php 22338 2011-11-04 17:24:53Z github_bot $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users Html Helper
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */

abstract class JHtmlComUsers
{
	public static function loadLanguage() {
		$lang =& JFactory::getLanguage();
		$extension = 'com_users';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
	}
	
	// function to get form elements from com_users for registration
	// $elements will add elements to the end of the form, should be an xml string
	public static function buildUserForm($elements='') {
		$registration = new JForm('registration');
		$registration->loadFile(JPATH_BASE . DS . "components" . DS . "com_users" . DS . "models" . DS . "forms" . DS . "registration.xml");
		if ($elements) {
			$registration->setFields($elements);
		}
		
		self::loadLanguage();
		
		return $registration;
	}
	
	public static function buildUserLogin($elements='') {
		$login = new JForm('login');
		$login->loadFile(JPATH_BASE . DS . "components" . DS . "com_users" . DS . "models" . DS . "forms" . DS . "login.xml");
		if ($elements) {
			$login->setFields($elements);
		}
		
		self::loadLanguage();
		
		return $login;
	}
	
	public static function defaultTemplate($registration, $option='com_users', $task='registration.register', $action='') {
		
		$output = '';
		
		if ($action=='') {
			//$action = 'index.php?option='.$option.'&task='.$task;
		}
		
		// call necessary javascript items
		JHtml::_('behavior.keepalive');
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');
		
		$output = '<form id="member-registration" action="' .  JRoute::_($action) . '" method="post" class="form-validate">';

		foreach ($registration->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.
			$fields = $registration->getFieldset($fieldset->name);
			if (count($fields)):
				$output .= '<fieldset>';
				if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.
					$output .= '<legend>' .  JText::_($fieldset->label) . '</legend>';
				endif;
					$output .= '<dl>';
				foreach($fields as $field):// Iterate through the fields in the set and display them.
					if ($field->hidden):// If the field is hidden, just display the input.
						$output .= $field->input;
					else:
						$output .= '<dt>' .  $field->label; 
						if (!$field->required && $field->type != 'Spacer'): 
							//$output .= '<span class="optional">' .  JText::_('COM_USERS_OPTIONAL') . '</span>';
						endif; 
						$output .= '</dt>';
						$output .= '<dd>' .  $field->input .'</dd>';
					endif;
				endforeach;
					$output .= '</dl><dl><dt></dt><dd><input type="checkbox" name="tos" value="1" class="required" /> By checking you agree to the site\'s Terms of Use</dd></dl>';
				$output .= '</fieldset>';
			endif;
		endforeach;
				$output .= '<div>';
					$output .= '<input type="submit" class="validate" value="' .  JText::_('JREGISTER') . '" />';
					$output .= '<input type="hidden" name="option" value="'.$option.'" />';
					$output .= JHtml::_('form.token');
				$output .= '</div>';
			$output .= '</form>';
			
		return $output;
	}
	
	public static function saveUserForm() {
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// get model to be loaded
		JLoader::import( 'registration', JPATH_BASE . DS . 'components' . DS . 'com_users' . DS . 'models' );
		
		// Initialise variables.
		$app	= JFactory::getApplication();
		
		$jversion = new JVersion();
		$above3 = version_compare($jversion->getShortVersion(), '3.0', 'ge');
		
		if ($above3) {
			require_once(JPATH_BASE . DS . 'libraries' . DS . 'joomla' . DS . 'table' . DS . 'user.php');
			$model	= JModelLegacy::getInstance('Registration', 'UsersModel');
		} else {
			require_once(JPATH_BASE . DS . 'libraries' . DS . 'joomla' . DS . 'database' . DS . 'table' . DS . 'user.php');
			$model	= JModel::getInstance('Registration', 'UsersModel');
		}
		
		// load language for messages
		self::loadLanguage();

		// If registration is disabled - Redirect to login page.
		if(JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0) {
			$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
			return false;
		}

		// Get the user data.
		$input = $app->input;
		$requestData = $_POST;

		JForm::addFormPath(JPATH_BASE . '/components/com_users/models/forms');

		// Validate the posted data.
		$form	= $model->getForm();

		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data	= $model->validate($form, $requestData);

		// Check for validation errors.
		if ($data === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_users.registration.data', $requestData);

			// Redirect back to the registration screen.
			return false;
		}

		// Attempt to save the data.
		$return	= $model->register($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_users.registration.data', $data);

			// Redirect back to the edit screen.
			$app->enqueueMessage(JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $model->getError()), 'warning');
			return false;
		}

		// Flush the data from the session.
		$app->setUserState('com_users.registration.data', null);
		
		$input->set('password', $input->get('password1'));
		self::processLogin();
		
		return true;
	}
	
	public static function defaultLogin($login, $option='com_users', $task='login.login', $action='') {
		$output = '';
		
		if ($action=='') {
			//$action = 'index.php?option='.$option.'&task='.$task;
		}
		self::loadLanguage();
		// call necessary javascript items
		JHtml::_('behavior.keepalive');
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');
		
		$output = '<form id="member-login" action="' .  JRoute::_($action) . '" method="post" class="form-validate">';

		foreach ($login->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.
			$fields = $login->getFieldset($fieldset->name);
			if (count($fields)):
				$output .= '<fieldset>';
				if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.
					$output .= '<legend>' .  JText::_($fieldset->label) . '</legend>';
				endif;
					$output .= '<dl>';
				foreach($fields as $field):// Iterate through the fields in the set and display them.
					if ($field->hidden):// If the field is hidden, just display the input.
						$output .= $field->input;
					else:
						$output .= '<dt>' .  $field->label; 
						if (!$field->required && $field->type != 'Spacer'): 
							//$output .= '<span class="optional">' .  JText::_('COM_USERS_OPTIONAL') . '</span>';
						endif; 
						$output .= '</dt>';
						$output .= '<dd>' .  $field->input .'</dd>';
					endif;
				endforeach;
					
				$output .= '</fieldset>';
			endif;
		endforeach;
				$output .= '<div>';
					$output .= '<input type="submit" class="validate" value="' .  JText::_('JLOGIN') . '" />';
					$output .= '<input type="hidden" name="option" value="'.$option.'" />';
					$output .= JHtml::_('form.token');
				$output .= '</div>';
			$output .= '</form>';
			
		return $output;
	}
	
	public static function processLogin () {
		$app =& JFactory::getApplication();
		
		$credentials = array(
					'username'=>$app->input->get('username'),
					'password'=>$app->input->get('password')
					);
		$options = array('remember'=>false);
		return $app->login($credentials, $options);
	}
	
}
